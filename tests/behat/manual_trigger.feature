@tool @tool_cleanupcourses
Feature: Add a manual trigger and activate it as a teacher

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

  Scenario: Add a new workflow definition with steps and rearange
    Given I log in as "admin"
    And I navigate to "Workflow Settings" node in "Site administration > Plugins > Cleanup Courses"
    And I press "Add Workflow"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    And I press "Save changes"
    And I set the following fields to these values:
      | Instance Name              | My Trigger                                |
      | Subplugin Name             | Manual trigger                            |
    And I press "reload"
    And I should see "Specific settings of the trigger type"
    And I set the following fields to these values:
      | Icon                       | t/delete                                  |
      | Action name                | Delete course                             |
      | Capability                 | moodle/course:manageactivities            |
    And I press "Save changes"
    And I select "Create Backup Step" from the "subpluginname" singleselect
    And I set the field "Instance Name" to "Create Backup Step"
    And I press "Save changes"
    And I select "Delete Course Step" from the "subpluginname" singleselect
    And I set the field "Instance Name" to "Delete Course 2"
    And I press "Save changes"
    And I press "Back"
    And I press "Activate"
    And I log out
    And I log in as "teacher1"
    And I am on cleanupcourses view
    Then I should see the tool "Delete course" in the "Course 1" row of the "tool_cleanupcourses_remaining" table
    When I click on the tool "Delete course" in the "Course 1" row of the "tool_cleanupcourses_remaining" table
    Then I should see "Course 1"
    And I should see "Course 2"
    And I should not see the tool "Delete course" in the "Course 1" row of the "tool_cleanupcourses_remaining" table
    And I should see the tool "Delete course" in the "Course 2" row of the "tool_cleanupcourses_remaining" table
    When I run the scheduled task "tool_cleanupcourses\task\process_cleanup"
    And I am on cleanupcourses view
    Then I should not see "Course 1"
    And I should see "Course 2"