@tool @tool_lifecycle
Feature: Add a workflow definition activate it
  Further, check that all edit possibilities are disabled.

  Scenario: Add a new workflow definition with steps and rearrange
    Given I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Delay in case of rollback"
    When I select "Start date delay trigger" from the "tool_lifecycle-choose-trigger" singleselect
    Then I should see "Settings of the trigger"
    And I should see "Specific settings of the trigger type"
    When I set the following fields to these values:
      | instancename     | delay trigger              |
      | delay[number]    | 2                          |
      | delay[timeunit]  | days                       |
    And I press "Save changes"
    Then I should see "Add new step instance"
    And I should see "startdatedelay"
    When I select "Email step" from the "tool_lifecycle-choose-step" singleselect
    And I set the following fields to these values:
      | Instance name               | Email step                  |
      | responsetimeout[number]     | 14                          |
      | responsetimeout[timeunit]   | days                        |
      | Subject template            | Subject                     |
      | Content plain text template | Content                     |
      | Content HTML Template       | Content HTML                |
    And I press "Save changes"
    And I select "Create backup step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Create backup step"
    And I press "Save changes"
    And I select "Delete course step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course 2"
    And I press "Save changes"
    And I am on workflowdrafts page
    Then I should see the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    And I should see the tool "Download workflow definition" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    And I should see the tool "Delete workflow" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    And I press "Activate"
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    Then I should see "Edit"
    And I should not see "Move up"
    And I should not see "Move down"
    And I should not see "Add new step instance"
