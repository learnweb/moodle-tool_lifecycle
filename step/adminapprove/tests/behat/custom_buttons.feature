@tool @tool_lifecycle @manual_trigger
Feature: Add an admin approve step with 'button' customisation
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
      | teacher1 | C3 | teacher |
    Given I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
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
    And I set the field "Proceed button text" to "Create backup"
    And I set the field "Rollback button text" to "Cancel workflow"
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
    Then I should see "Course 1"
    When I log out

    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

  @javascript
  Scenario: Check button texts on approval page

    And I am on approvals page
    And I click on "admin approve step" "link"

    Then I should not see "Rollback"
    And I should not see "Proceed"

    And I should see "Cancel workflow"
    And I should see "Create backup"
    And I should see "All cancel workflow"
    And I should see "All create backup"
    And I should see "Selected cancel workflow"
    And I should see "Selected create backup"

  @javascript
  Scenario: Proceed on approval page (customisation)

    And I am on approvals page
    And I click on "admin approve step" "link"
    When I click on "Create backup" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    And I should not see "Course 2"
  @javascript
  Scenario: Proceed all on approval page (customisation)

    And I am on approvals page
    And I click on "admin approve step" "link"
    When I click on "All create backup" "button"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    And I should not see "Course 2"

  @javascript
  Scenario: Rollback on approval page (customisation)

    And I am on approvals page
    And I click on "admin approve step" "link"
    When I click on "Cancel workflow" "button"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    And I should not see "Course 2"

  @javascript
  Scenario: Rollback all on approval page (customisation)

    And I am on approvals page
    And I click on "admin approve step" "link"

    When I click on "All cancel workflow" "button"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    And I should not see "Course 2"
  @javascript
  Scenario: Proceed on active workflows page (customisation)

    When I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_manual_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_manual_workflows" table
    Then I should see "Courses: 1"
    And I should not see "Rollback"
    And I should not see "Proceed"
    When I click on "Courses: 1" "link"
    Then I should see "Cancel workflow"
    And I should see "Create backup"
    When I click on "Create backup" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on coursebackups page
    Then I should see "Course 1"
    And I should not see "Course 2"

  @javascript
  Scenario: Rollback on active workflows page (customisation)

    When I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_manual_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_manual_workflows" table
    Then I should see "Courses: 1"
    When I click on "Courses: 1" "link"
    Then I should see "Cancel workflow"
    And I should see "Create backup"
    When I click on "Cancel workflow" "button"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on delayedworkflows page
    Then I should see "Course 1"
    And I should not see "Course 2"
