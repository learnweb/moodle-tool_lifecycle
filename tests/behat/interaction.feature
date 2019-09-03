@tool @tool_lifecycle
Feature: Add a workflow with an email step and test the interaction as a teacher

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | startdate       |
      | Course 1 | C1        | 0        | ##2 days ago## |
      | Course 2 | C2        | 0        | ##4 days ago##  |
      | Course 3 | C3        | 0        | ##4 days ago##  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C2     | editingteacher |
      | teacher1 | C3     | editingteacher |
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow Settings" in site administration
    And I press "Add Workflow"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    And I press "Save changes"
    And I select "Start date delay trigger" from the "triggername" singleselect
    And I set the following fields to these values:
      | Instance Name    | My Trigger                 |
      | delay[number]    | 3                          |
      | delay[timeunit]  | days                       |
    And I press "Save changes"
    And I select "Email Step" from the "stepname" singleselect
    And I set the following fields to these values:
      | Instance Name              | Email Step                  |
      | responsetimeout[number]    | 8                           |
      | responsetimeout[timeunit]  | seconds                     |
      | Subject Template           | Subject                     |
      | Content plain text template           | Content                     |
      | Content HTML Template      | Content HTML                |
    And I press "Save changes"
    And I select "Delete Course Step" from the "stepname" singleselect
    And I set the field "Instance Name" to "Delete Course 2"
    And I press "Save changes"
    And I press "Back"
    And I press "Activate"
    And I log out

  Scenario: Test interaction of email step
    Given I log in as "teacher1"
    When I am on lifecycle view
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see "Course 2" in the "tool_lifecycle_remaining" "table"
    And I should see "Course 3" in the "tool_lifecycle_remaining" "table"
    When I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I am on lifecycle view
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see "Course 2" in the "tool_lifecycle_interaction" "table"
    And I should see "Course 3" in the "tool_lifecycle_interaction" "table"
    And I should see the tool "Keep Course" in the "Course 2" row of the "tool_lifecycle_interaction" table
    And I should see the tool "Keep Course" in the "Course 3" row of the "tool_lifecycle_interaction" table
    When I click on the tool "Keep Course" in the "Course 2" row of the "tool_lifecycle_interaction" table
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see "Course 2" in the "tool_lifecycle_remaining" "table"
    And I should see "Course 3" in the "tool_lifecycle_interaction" "table"
    When I wait "10" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I am on lifecycle view
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see "Course 2" in the "tool_lifecycle_remaining" "table"
    And I should not see "Course 3"