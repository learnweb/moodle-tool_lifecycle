@tool @tool_cleanupcourses
Feature: Add a workflow definition

  Scenario: Add a new workflow definition without steps
  For displaying the additional trigger settings the "Save changes" button is used.
    Given I log in as "admin"
    And I navigate to "Workflow Settings" node in "Site administration > Plugins > Cleanup Courses"
    And I press "Add Workflow"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Trigger for workflow 'My Workflow'"
    When I set the following fields to these values:
      | Instance Name              | My Trigger                                |
      | Subplugin Name             | Manual trigger                            |
    And I press "Save changes"
    # The manual trigger requires additional settings. For that reason the form reloads with some more fields.
    Then I should see "Required"
    And I should see "Specific settings of the trigger type"
    When I set the following fields to these values:
      | Icon                       | t/delete                                  |
      | Action name                | Delete course                             |
      | Capability                 | moodle/course:manageactivities            |
    And I press "Save changes"
    Then I should see "Workflow Steps"
    And I should see "Manual trigger"
    When I press "Back"
    Then I should see "My Workflow"

  @wip
  Scenario: Add a new workflow definition with steps
  For displaying the additional trigger settings the "reload" button is used.
    Given I log in as "admin"
    And I navigate to "Workflow Settings" node in "Site administration > Plugins > Cleanup Courses"
    And I press "Add Workflow"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Trigger for workflow 'My Workflow'"
    When I set the following fields to these values:
      | Instance Name              | My Trigger                                |
      | Subplugin Name             | Start date delay trigger                  |
    And I press "reload"
    Then I should see "Specific settings of the trigger type"
    When I set the following fields to these values:
      | delay[number]    | 2                          |
      | delay[timeunit]  | days                       |
    And I press "Save changes"
    Then I should see "Workflow Steps"
    And I should see "Start date delay trigger"
    When I press "Back"
    Then I should see "My Workflow"
