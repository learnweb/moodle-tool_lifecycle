@tool @tool_lifecycle @lifecyclestep @lifecyclestep_adminapprove
Feature: Add an admin approve step without 'button' customisation

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
      | Course 2 | C2 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | teacher1 | C2 | editingteacher |
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
    And I set the field "Label of the proceed selected button" to "Selected create backup"
    And I set the field "Label of the rollback selected button" to "Selected call off workflow"
    And I press "Save changes"

    And I select "Create backup step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Create backup step"
    And I press "Save changes"

    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    Then I should see the tool "Backup course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Backup course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    Then I should see "Workflow started successfully."
    And I log out

    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

  @javascript
  Scenario: Check button texts on approval page (default)

    When I am on approvals page
    And I click on "admin approve step" "link"
    Then "Selected create backup" "button" should exist
    And "Selected call off workflow" "button" should exist
    And "Create backup" "button" should exist
    And "Call off workflow" "button" should exist

  @javascript
  Scenario: Proceed on approval page (default)

    When I am on approvals page
    And I click on "admin approve step" "link"
    Then I should see "Course 1"
    And I click on "checkall" "checkbox"
    And I click on "Create backup" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "25" seconds

    And I am on coursebackups page
    Then I should see "Course 1"

  @javascript
  Scenario: Proceed all on approval page (default)

    When I log in as "teacher1"
    And I am on lifecycle view
    Then I should see the tool "Backup course" in the "Course 2" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Backup course" in the "Course 2" row of the "tool_lifecycle_remaining" table
    Then I should see "Workflow started successfully."
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds
    And I log out

    And I log in as "admin"
    When I am on approvals page
    And I click on "admin approve step" "link"
    And I click on "checkall" "checkbox"
    And I click on "Selected create backup" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    And I should see "Course 2"

  @javascript
  Scenario: Rollback on approval page (default)

    When I am on approvals page
    And I click on "admin approve step" "link"
    Then I should see "Course 1"
    And I click on "checkall" "checkbox"
    And I click on "Call off workflow" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "25" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"

  @javascript
  Scenario: Rollback all on approval page (default)

    When I log in as "teacher1"
    And I am on lifecycle view
    Then I should see the tool "Backup course" in the "Course 2" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Backup course" in the "Course 2" row of the "tool_lifecycle_remaining" table
    Then I should see "Workflow started successfully."
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds
    And I log out

    When I log in as "admin"
    And I am on approvals page
    And I click on "admin approve step" "link"
    And I click on "checkall" "checkbox"
    And I click on "Selected call off workflow" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    And I should see "Course 2"

  @javascript
  Scenario: Proceed on active workflows page (default)

    When I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_manual_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_manual_workflows" table
    Then I should see "Courses: 1"
    When I click on "Courses: 1" "link"
    Then I should see "Call off workflow"
    And I should see "Create backup"
    When I click on "Create backup" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on coursebackups page
    Then I should see "Course 1"

  @javascript
  Scenario: Rollback on active workflows page (default)

    When I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_manual_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_manual_workflows" table
    Then I should see "Courses: 1"
    When I click on "Courses: 1" "link"
    Then I should see "Call off workflow"
    And I should see "Create backup"
    When I click on "Call off workflow" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "25" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    And I should not see "Course 2"
