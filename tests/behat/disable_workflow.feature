@tool @tool_lifecycle
Feature: Disable a workflow
  Further, check that all edit possibilities are disabled.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | startdate      |
      | Course 1 | C1        | 0        | ##4 days ago## |
    And I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow drafts" in site administration
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    And I press "Save changes"
    And I select "Start date delay trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name   | My Trigger |
      | delay[number]   | 3          |
      | delay[timeunit] | days       |
    And I press "Save changes"
    And I select "Email step" from the "tool_lifecycle-choose-step" singleselect
    And I set the following fields to these values:
      | Instance name             | Email step   |
      | responsetimeout[number]   | 42           |
      | responsetimeout[timeunit] | days         |
      | Subject template          | Subject      |
      | Content plain text template | Content    |
      | Content HTML Template     | Content HTML |
    And I press "Save changes"
    And I select "Delete course step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course 1"
    And I press "Save changes"
    And I click on "Workflow drafts" "link"
    And I press "Activate"
    When I wait "10" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I log out

  Scenario: Disable an workflow, keep processes running, then abort all processes and delete workflow
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Active workflows" in site administration
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    And I should see the tool "Disable workflow (processes keep running)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "Disable workflow (processes keep running)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    And I should not see the tool "Delete workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    When I click on the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see the tool "Delete workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    And I should not see the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    When I click on the tool "Delete workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see "Nothing to display"

  Scenario: Disable an workflow and kill processes (abort), then delete workflow
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Active workflows" in site administration
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    And I should see the tool "Disable workflow (abort processes, maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "Disable workflow (abort processes, maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    # Here a confirming dialog would appear, but we can't test it without javascript (it just doesn't show).
    Then I should see the tool "Delete workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    And I should not see the tool "Abort running processes (maybe unsafe!)" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    When I click on the tool "Delete workflow" in the "My Workflow" row of the "tool_lifecycle_deactivated_workflows" table
    Then I should see "Nothing to display"

  Scenario: Disable an workflow then create (duplicate) a new one with the same configuration
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Active workflows" in site administration
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    And I click on the tool "Disable workflow (processes keep running)" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    When I click on "List workflow drafts" "link"
    And I click on "Copy new workflow from existing" "link"
    And I click on the tool "Create copy" in the "My Workflow" row of the "tool_lifecycle-select-workflow" table
    And I press "Save changes"
    And I click on "Workflow drafts" "link"
    Then I should see the row "My Workflow" in the "tool_lifecycle_workflow_definitions" table
    And I click on "List active workflows" "link"
    And I should not see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    And I click on "List workflow drafts" "link"
    When I press "Activate"
    # Since no element is left, the table is not displayed anymore.
    And I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    And I click on "List workflow drafts" "link"
    Then I should not see the table "tool_lifecycle_workflow_definitions"
