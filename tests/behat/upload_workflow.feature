@tool @tool_lifecycle @_file_upload @upload_workflow
Feature: Upload a workflow definition

  @javascript
  Scenario: Upload a new workflow
    Given I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Upload workflow" "link"
    And I upload "admin/tool/lifecycle/tests/fixtures/simpleworkflow.xml" file to "Upload workflow" filemanager
    And I press "Upload"
    Then I should see "A Workflow"
