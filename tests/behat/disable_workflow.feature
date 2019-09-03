@tool @tool_lifecycle
Feature: Disable a workflow
  Further, check that all edit possibilities are disabled.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | startdate      |
      | Course 1 | C1        | 0        | ##4 days ago## |
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow Settings" in site administration
    And I press "Add Workflow"
    And I set the following fields to these values:
      | Title                    | My Workflow               |
      | Displayed workflow title | Teachers view on workflow |
    And I press "Save changes"
    And I select "Start date delay trigger" from the "triggername" singleselect
    And I set the following fields to these values:
      | Instance Name   | My Trigger |
      | delay[number]   | 3          |
      | delay[timeunit] | days       |
    And I press "Save changes"
    And I select "Email Step" from the "stepname" singleselect
    And I set the following fields to these values:
      | Instance Name             | Email Step   |
      | responsetimeout[number]   | 42           |
      | responsetimeout[timeunit] | days         |
      | Subject Template          | Subject      |
      | Content plain text template | Content    |
      | Content HTML Template     | Content HTML |
    And I press "Save changes"
    And I select "Delete Course Step" from the "stepname" singleselect
    And I set the field "Instance Name" to "Delete Course 1"
    And I press "Save changes"
    And I press "Back"
    And I press "Activate"
    When I wait "10" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I log out

  Scenario: Disable an workflow, keep processes running, then abort all processes and delete workflow
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow Settings" in site administration
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    And I should see the tool "Disable Workflow (processes keep running)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "Disable Workflow (processes keep running)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    And I should not see the tool "Delete Workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    When I click on the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see the tool "Delete Workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    And I should not see the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    When I click on the tool "Delete Workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see "Nothing to display"

  Scenario: Disable an workflow and kill processes (abort), then delete workflow
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow Settings" in site administration
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    And I should see the tool "Disable Workflow (abort processes, maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "Disable Workflow (abort processes, maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see the tool "Delete Workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    And I should not see the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    When I click on the tool "Delete Workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    Then I should see "Nothing to display"

  Scenario: Disable an workflow then create (duplicate) a new one with the same configuration
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow Settings" in site administration
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "Disable Workflow (processes keep running)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "Duplicate Workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    Then I should see the row "My Workflow" in the "tool_lifecycle_workflow_definitions" table
    And I should not see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    When I press "Activate"
    # Since no element is left, the table is not displayed anymore.
    Then I should not see the table "tool_lifecycle_workflow_definitions"
    And I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table