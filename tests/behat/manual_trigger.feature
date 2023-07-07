@tool @tool_lifecycle @manual_trigger
Feature: Add a manual trigger and test view and actions as a teacher

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

  @javascript
  Scenario: Test displayed action tools for different capabilities
    Given I log in as "admin"
    # Allow teacher role to view courses in life cycle view
    # to allow for different visibility levels of manual tools.
    And I set the following system permissions of "Non-editing teacher" role:
      | capability | permission |
      | tool/lifecycle:managecourses | Allow |
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow drafts" in site administration
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
    And I click on "Workflow drafts" "link"
    And I press "Activate"
    And I log out
    And I log in as "teacher1"
    And I am on lifecycle view
    Then I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"
    And I should see the tool "Delete course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    And I should see the tool "Delete course" in the "Course 2" row of the "tool_lifecycle_remaining" table
    And I should not see the tool "Delete course" in the "Course 3" row of the "tool_lifecycle_remaining" table

  @javascript
  Scenario: Manually trigger backup and course deletion
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow drafts" in site administration
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
    And I select "Create backup step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Create backup step"
    And I press "Save changes"
    And I select "Delete course step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course 2"
    And I press "Save changes"
    And I click on "Workflow drafts" "link"
    And I press "Activate"
    And I log out
    And I log in as "teacher1"
    And I am on lifecycle view
    Then I should see the tool "Delete course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Delete course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    Then I should see "Course 1"
    And I should see "Course 2"
    And I should not see the tool "Delete course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    And I should see the tool "Delete course" in the "Course 2" row of the "tool_lifecycle_remaining" table
    When I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I am on lifecycle view
    Then I should not see "Course 1"
    And I should see "Course 2"
    When I log out
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Course backups" in site administration
    Then I should see "Course 1"
    And I should not see "Course 2"
