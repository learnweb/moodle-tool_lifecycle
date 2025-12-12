@tool @tool_lifecycle @workflow
Feature: Test the max number of courses to be processed-limitations of a workflow.

  Background:
    Given the following "category" exist:
      | name    | category | idnumber |
      | cat     | 0        | cat      |
    And the following "courses" exist:
      | fullname    | shortname | category |
      | Course A    | C_A       | cat      |
      | Course B    | C_B       | cat      |
      | Course C    | C_C       | cat      |

  @javascript
  Scenario: Create a workflow with a category trigger and an admin approve step
  Test the workflow maximum courses options when running the lifecycle task.
    Given I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                                | My Workflow                               |
      | Displayed workflow title             | Teachers view on workflow                 |
      | Maximum courses triggered per cron   | 1                                         |
      | Maximum courses triggered per day    | 2                                         |
    And I press "Save changes"

    And I select "Categories trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name                                          | Categories |
      | Categories, for which the workflow should be triggered | cat        |
    And I press "Save changes"

    And I select "Admin approve step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Admin approve Step"
    And I press "Save changes"

    And I am on workflowdrafts page
    And I press "Activate"

    And I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    Then I should see "Admin approve Step"
    And I should see "Categories"
    And I should see "Courses: 0"

    When I follow "Run"
    And I wait "5" seconds

    And I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    Then I should see "Courses: 1"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    Then I should see "Courses: 2"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "5" seconds

    And I am on activeworkflows page
    Then I should see the row "My Workflow" in the "tool_lifecycle_active_automatic_workflows" table
    When I click on the tool "View workflow steps" in the "My Workflow" row of the "tool_lifecycle_active_automatic_workflows" table
    Then I should see "Courses: 2"
