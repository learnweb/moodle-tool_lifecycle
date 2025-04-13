@tool @tool_lifecycle @lifecyclestep @lifecyclestep_adminapprove @javascript
Feature: Add a workflow with an adminapprove step and test the status in the teachers view.

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | teacher | Terry1    | Teacher1 | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher | C1     | editingteacher |
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle" in site administration
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                    | Admin Approve Step WF #1 |
      | Displayed workflow title | Admin Approve Step WF #1 |
    And I press "Save changes"
    And I select "Start date delay trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name   | My Trigger |
      | delay[number]   | 0          |
      | delay[timeunit] | seconds    |
    And I press "Save changes"
    And I select "Admin approve step" from the "tool_lifecycle-choose-step" singleselect
    And I set the following fields to these values:
      | Instance name | Admin Approve Step #1 |
      | Status message | My status |
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I log out

  Scenario: Test interaction of admin approve step
    When I log in as "teacher"
    And I am on lifecycle view
    Then I should see "Course 1" in the "tool_lifecycle_remaining" "table"
    And I should see "My status" in the "tool_lifecycle_remaining" "table"
