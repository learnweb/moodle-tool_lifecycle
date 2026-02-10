@tool @tool_lifecycle @lifecyclestep @lifecyclestep_adminapprove
Feature: Handle large amount of approve steps

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And "300" "courses" exist with the following data:
      | shortname  | C[count]             |
      | fullname   | Course [count]       |
      | category   | 0                    |
      | startdate  | ##-32 days##         |
      | enddate    | ##-10 days##         |
    And "300" "course enrolments" exist with the following data:
      | user   | teacher1                 |
      | course | C[count]                 |
      | role   | editingteacher           |

    Given I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                                | My Workflow                               |
      | Displayed workflow title             | Teachers view on workflow                 |
    And I press "Save changes"
    And I select "End date trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name              | My Trigger                                |
    # default is one day after
    And I press "Save changes"

    And I select "Admin approve step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "admin approve step"
    And I set the field "Label of the proceed button" to "Create backup"
    And I set the field "Label of the rollback button" to "Call off workflow"
    And I press "Save changes"

    And I select "Create backup step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Create backup step"
    And I press "Save changes"

    And I am on workflowdrafts page
    And I press "Activate"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "8" seconds

    And I am on approvals page
    And I click on "admin approve step" "link"

  @javascript
  Scenario: Show all on approval page
    And "Show all 300" "link" should exist
    And "1" "link" should exist
    And "2" "link" should exist
    And "3" "link" should exist
    And I should see "Course 100"
    And I should not see "Course 101"

  @javascript
  Scenario: Show page 2 on approval page
    And I click on "2" "link"
    Then I should see "Course 101"
    And I should not see "Course 100"

  @javascript
  Scenario: Select all on page 1 of approval page
    And I click on "select-all-ids" "checkbox"
    And I set the field "With selected courses..." to "Create backup"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    # Here we only execute the task once in order to avoid creating backups!
    # Note that each backup increments a static counter that affects ALL testcases
    # accross ALL test scripts!
    # It is only reset in cron which is normally not executed in test setup.
    Then "Show all 200" "link" should exist

  @javascript
  Scenario: Show all and select all on approval page
    And I click on "Show all 300" "link"
    And I click on "select-all-ids" "checkbox"
    And I set the field "With selected courses..." to "Create backup"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    # No backup is intentionally created here!

    Then I should see "There are currently no courses waiting for approval"
