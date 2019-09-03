@tool @tool_lifecycle
Feature: Add a workflow definition activate it
  Further, check that all edit possibilities are disabled.

  Scenario: Add a new workflow definition with steps and rearrange
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow Settings" in site administration
    And I press "Add Workflow"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Workflow Steps"
    When I select "Start date delay trigger" from the "triggername" singleselect
    Then I should see "Trigger for workflow 'My Workflow'"
    And I should see "Specific settings of the trigger type"
    When I set the following fields to these values:
      | instancename     | delay trigger              |
      | delay[number]    | 2                          |
      | delay[timeunit]  | days                       |
    And I press "Save changes"
    Then I should see "Workflow Steps"
    And I should see "Add New Step Instance"
    And I should see "Start date delay trigger"
    When I select "Email Step" from the "stepname" singleselect
    And I set the following fields to these values:
      | Instance Name              | Email Step                  |
      | responsetimeout[number]    | 14                          |
      | responsetimeout[timeunit]  | days                        |
      | Subject Template           | Subject                     |
      | Content plain text template           | Content                     |
      | Content HTML Template      | Content HTML                |
    And I press "Save changes"
    And I select "Create Backup Step" from the "stepname" singleselect
    And I set the field "Instance Name" to "Create Backup Step"
    And I press "Save changes"
    And I select "Delete Course Step" from the "stepname" singleselect
    And I set the field "Instance Name" to "Delete Course 2"
    And I press "Save changes"
    And I press "Back"
    Then I should see the tool "View Workflow Steps" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    And I should see the tool "Duplicate Workflow" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    And I should see the tool "Edit General Settings" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    And I should see the tool "Delete Workflow" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    And I should not see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    When I press "Activate"
    Then I should see the tool "View Workflow Steps" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    And I should not see the table "tool_lifecycle_workflow_definitions"
    # And I should not see the tool "View Workflow Steps" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table # won't work because table doesnt exist when only workflow
    # And I should see the tool "Duplicate Workflow" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    # And I should see the tool "Edit General Settings" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    # And I should not see the tool "Delete Workflow" in the "My Workflow" row of the "tool_lifecycle_workflow_definitions" table
    When I click on the tool "View Workflow Steps" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    Then I should not see the tool "Edit" in any row of the "tool_lifecycle_workflows" table
    And I should not see the tool "Delete" in any row of the "tool_lifecycle_workflows" table
    And I should not see the tool "Up" in any row of the "tool_lifecycle_workflows" table
    And I should not see the tool "Down" in any row of the "tool_lifecycle_workflows" table
    And I should see the tool "View" in all rows of the "tool_lifecycle_workflows" table
    And I should not see "Add New Step Instance"