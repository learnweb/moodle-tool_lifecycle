@tool @tool_lifecycle @manual_trigger
Feature: Add a manual trigger and test view and actions as a teacher

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | teacher2 | Teacher | 2 | teacher2@example.com |
    And the following "categories" exist:
      | name    | category | idnumber |
      | cata    | 0        | cata     |
      | catba   | cata     | catba    |
      | catc    | 0        | catc     |
      | archive | 0        | archive  |
    And the following "courses" exist:
      | fullname    | shortname | category |
      | Course A    | CA        | cata     |
      | Course BA   | CBA       | catba    |
      | Course C    | CC        | catc     |
      | Course Arch | CArch     | archive  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | CA     | editingteacher |
      | teacher1 | CBA    | editingteacher |
      | teacher1 | CC     | editingteacher |
      | teacher1 | CArch  | editingteacher |
      | teacher2 | CA     | editingteacher |
      | teacher2 | CBA    | teacher        |
      | teacher2 | CC     | teacher        |
      | teacher2 | CArch  | editingteacher |

  @javascript
  Scenario: Combine manual trigger with automatic categories trigger (backup and course deletion)
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
      | Action name                | Delete course                             |
      | Capability                 | moodle/course:manageactivities            |
    And I press "Save changes"

    And I select "Categories trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name              | Categories                                |
    And I set the following fields to these values:
      | Categories, for which the workflow should be triggered              | cata, catc |
    And I press "Save changes"

    And I select "Create backup step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Create backup step"
    And I press "Save changes"
    And I select "Delete course step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course 2"
    And I press "Save changes"

    And I am on workflowdrafts page
    And I press "Activate"
    And I log out
    And I log in as "teacher1"

    And I am on lifecycle view
    Then I should see the tool "Delete course" in the "Course A" row of the "tool_lifecycle_remaining" table
    And I should see the tool "Delete course" in the "Course BA" row of the "tool_lifecycle_remaining" table
    And I should see the tool "Delete course" in the "Course C" row of the "tool_lifecycle_remaining" table
    And I should not see "Action" in the "Course Arch" "table_row"

    When I click on the tool "Delete course" in the "Course C" row of the "tool_lifecycle_remaining" table
    And I should not see the tool "Delete course" in the "Course C" row of the "tool_lifecycle_remaining" table
    And I should see the tool "Delete course" in the "Course BA" row of the "tool_lifecycle_remaining" table
    And I should see the tool "Delete course" in the "Course A" row of the "tool_lifecycle_remaining" table
    When I log out
    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds
    And I log out
    And I log in as "teacher1"
    And I am on lifecycle view
    Then I should not see "Course C"
    And I should see "Course BA"
    And I should see "Course A"
    And I should see "Course Arch"
    When I log out
    And I log in as "admin"
    And I am on coursebackups page
    Then I should see "Course C"
    And I should not see "Course BA"
    And I should not see "Course A"
    And I should not see "Course Arch"

