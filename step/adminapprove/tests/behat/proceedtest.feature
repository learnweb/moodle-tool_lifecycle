@tool @tool_lifecycle @lifecyclestep @lifecyclestep_adminapprove @javascript
Feature: Add a workflow with an adminapprove step and test it

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
      | Course 3 | C3        |
      | Course 4 | C4        |
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle" in site administration
    And I click on "Workflow drafts" "link"
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
    And I select "Delete course step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course #1"
    And I press "Save changes"
    And I click on "Workflow drafts" "link"
    And I press "Activate"

  Scenario: Test interaction of admin approve step
    When I navigate to "Plugins > Admin tools > Life Cycle > Manage Admin Approve Steps" in site administration
    Then I should see "There are currently no courses waiting for interaction in any Admin Approve step."
    When I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I reload the page
    And I click on "Admin Approve Step #1" "link"
    Then I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"
    And I should see "Course 4"
    When I click on the tool "Proceed" in the "Course 1" row of the "lifecyclestep_adminapprove-decisiontable" table
    And I wait to be redirected
    Then I should not see "Course 1"
    When I click on the tool "Rollback" in the "Course 2" row of the "lifecyclestep_adminapprove-decisiontable" table
    And I wait to be redirected
    Then I should not see "Course 2"
