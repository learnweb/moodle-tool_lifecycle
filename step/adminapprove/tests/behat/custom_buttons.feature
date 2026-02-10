@tool @tool_lifecycle @lifecyclestep @lifecyclestep_adminapprove
Feature: Add an admin approve step WITH button label customisation

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
      | Course 2 | C2 | 0 |
      | Course 3 | C3 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | teacher1 | C2 | editingteacher |
      | teacher1 | C3 | editingteacher |
    Given I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                                | My Workflow                               |
      | Displayed workflow title             | Teachers view on workflow                 |
    And I press "Save changes"
    And I select "Manual trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name              | My Trigger                                |
      | Icon                       | t/delete                                  |
      | Action name                | Backup course                             |
      | Capability                 | moodle/course:manageactivities            |
    And I press "Save changes"

    And I select "Admin approve step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "admin approve step"
    And I set the field "Label of the proceed button" to "Create backup"
    And I set the field "Label of the rollback button" to "Call off workflow"
    And I press "Save changes"

    And I select "Create Adhoc Backup Step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Create backup step"
    And I press "Save changes"

    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    Then I should see the tool "Backup course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Backup course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    And I click on the tool "Backup course" in the "Course 2" row of the "tool_lifecycle_remaining" table
    And I click on the tool "Backup course" in the "Course 3" row of the "tool_lifecycle_remaining" table
    Then I should see "Workflow started successfully."
    And I log out

    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

  @javascript
  Scenario: Check button texts on approval page (custom)
    When I am on approvals page
    And I click on "admin approve step" "link"
    And I should see "Create backup" in the "Course 1" "table_row"
    And I should see "Create backup" in the "Course 2" "table_row"
    And I should see "Create backup" in the "Course 3" "table_row"
    And I should see "Call off workflow" in the "Course 1" "table_row"
    And I should see "Call off workflow" in the "Course 2" "table_row"
    And I should see "Call off workflow" in the "Course 3" "table_row"
    And "Create backup" "link" should exist in the "Course 1" "table_row"
    And "Call off workflow" "link" should exist in the "Course 1" "table_row"

  @javascript
  Scenario: Proceed on approval page (check all, custom)
    When I am on approvals page
    And I click on "admin approve step" "link"
    Then I should see "Course 1"
    Then I should see "Course 2"
    Then I should see "Course 3"
    And I click on "select-all-ids" "checkbox"
    And I set the field "With selected courses..." to "Create backup"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run all adhoc tasks
    And I wait "2" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    Then I should see "Course 2"
    Then I should see "Course 3"

  @javascript
  Scenario: Proceed on approval page (check first one, custom)
    When I am on approvals page
    And I click on "admin approve step" "link"
    And I click on "c[]" "checkbox"
    And I set the field "With selected courses..." to "Create backup"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run all adhoc tasks
    And I wait "2" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"

  @javascript
  Scenario: Proceed on approval page (click proceed button, custom)
    When I am on approvals page
    And I click on "admin approve step" "link"
    And I click on "Create backup" "link" in the "Course 1" "table_row"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run all adhoc tasks
    And I wait "2" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"

  @javascript
  Scenario: Rollback on approval page (check all, custom)
    When I am on approvals page
    And I click on "admin approve step" "link"
    Then I should see "Course 1"
    And I click on "select-all-ids" "checkbox"
    And I set the field "With selected courses..." to "Call off workflow"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    Then I should see "Course 2"
    Then I should see "Course 3"
    And I am on coursebackups page
    Then I should not see "Course 1"
    Then I should not see "Course 2"
    Then I should not see "Course 3"

  @javascript
  Scenario: Rollback on approval page (check first one, custom)
    And I am on approvals page
    And I click on "admin approve step" "link"
    And I click on "c[]" "checkbox" in the "Course 1" "table_row"
    And I set the field "With selected courses..." to "Call off workflow"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I am on coursebackups page
    Then I should not see "Course 1"
    Then I should not see "Course 2"
    Then I should not see "Course 3"

  @javascript
  Scenario: Rollback on approval page (click rollback button, custom)
    When I am on approvals page
    And I click on "admin approve step" "link"
    And I click on "Call off workflow" "link" in the "Course 1" "table_row"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I am on coursebackups page
    Then I should not see "Course 1"
    Then I should not see "Course 2"
    Then I should not see "Course 3"

  @javascript
  Scenario: Proceed on active workflows page (custom)
    When I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_manual_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_manual_workflows" table
    Then I should see "Courses: 3"
    When I click on "Courses: 3" "link"

    Then I should see "Create backup" in the "Course 1" "table_row"
    And I should see "Create backup" in the "Course 2" "table_row"
    And I should see "Create backup" in the "Course 3" "table_row"
    And I should see "Call off workflow" in the "Course 1" "table_row"
    And I should see "Call off workflow" in the "Course 2" "table_row"
    And I should see "Call off workflow" in the "Course 3" "table_row"

    When I click on "Create backup" "button" in the "Course 1" "table_row"

    And I wait "1" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run all adhoc tasks
    And I wait "3" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    Then I should not see "Course 2"
    Then I should not see "Course 3"

  @javascript
  Scenario: Rollback on active workflows page (custom)
    When I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_manual_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_manual_workflows" table
    Then I should see "Courses: 3"
    When I click on "Courses: 3" "link"
    Then I should see "Call off workflow"
    And I should see "Create backup"
    When I click on "Call off workflow" "button" in the "Course 1" "table_row"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    Then I should not see "Course 2"
    Then I should not see "Course 3"
