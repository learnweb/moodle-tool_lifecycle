@tool @tool_lifecycle @javascript
Feature: Add a workflow with a manual trigger and a duplicate step and test the interaction as a teacher

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow Settings" in site administration
    And I press "Add Workflow"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    And I press "Save changes"
    And I select "Manual trigger" from the "triggername" singleselect
    And I set the following fields to these values:
      | Instance Name              | My Trigger                                |
      | Icon                       | t/delete                                  |
      | Action name                | Duplicate course                          |
      | Capability                 | moodle/course:manageactivities            |
    And I press "Save changes"
    And I select "Duplicate Step" from the "stepname" singleselect
    And I set the following fields to these values:
      | Instance Name              | Duplicate Step                  |
    And I press "Save changes"
    And I press "Back"
    And I press "Activate"
    And I log out

  Scenario: Test interaction of duplicate step including the correct handling of the form for aditional information
    Given I log in as "teacher1"
    When I am on lifecycle view
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see the tool "Duplicate course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Duplicate course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    Then I should see "Duplicate Course"
    When I set the following fields to these values:
      | Course short name          | C2                                 |
      | Course full name           | Course 2                           |
    And I press "Save changes"
    Then I should see "Duplicated course will be available shortly." in the "tool_lifecycle_remaining" "table"
    When I log out
    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I log out
    And I log in as "teacher1"
    And I am on lifecycle view
    Then I should see "Course 2" in the "tool_lifecycle_remaining" "table"
    And I should see "C2" in the "tool_lifecycle_remaining" "table"

  Scenario: Test interaction of duplicate step when interaction is interrupted
    Given I log in as "teacher1"
    When I am on lifecycle view
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see the tool "Duplicate course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Duplicate course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    Then I should see "Duplicate Course"
    When I am on lifecycle view
    Then I should see the tool "Enter data" in the "Course 1" row of the "tool_lifecycle_interaction" table
    When I click on the tool "Enter data" in the "Course 1" row of the "tool_lifecycle_interaction" table
    Then I should see "Duplicate Course"
    When I set the following fields to these values:
      | Course short name          | C2                                 |
      | Course full name           | Course 2                           |
    And I press "Save changes"
    Then I should see "Duplicated course will be available shortly." in the "tool_lifecycle_remaining" "table"
    When I log out
    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I log out
    And I log in as "teacher1"
    And I am on lifecycle view
    Then I should see "Course 2" in the "tool_lifecycle_remaining" "table"
    And I should see "C2" in the "tool_lifecycle_remaining" "table"

  Scenario: Test interaction of duplicate step when interaction is canceled
    Given I log in as "teacher1"
    When I am on lifecycle view
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see the tool "Duplicate course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    When I click on the tool "Duplicate course" in the "Course 1" row of the "tool_lifecycle_remaining" table
    Then I should see "Duplicate Course"
    When I press "Cancel"
    Then I should not see "Duplicated course will be available shortly." in the "tool_lifecycle_remaining" "table"
    And I should see "Course 1" in the "tool_lifecycle_remaining" "table"