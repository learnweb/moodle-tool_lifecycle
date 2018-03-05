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
    When I select "Delete Course Step" from the "subpluginname" singleselect
    And I set the field "Instance Name" to "Delete Course"
    And I press "Save changes"
    Then I should see "Workflow Steps"
    And I should see "Delete Course"

  @wip
  Scenario: Add a new workflow definition and alter trigger
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
    When I click on the tool "Edit" in the "Trigger" row of the "tool_cleanupcourses_workflows" table
    Then the following fields match these values:
      | Instance Name              | My Trigger                                |
      | Subplugin Name             | Start date delay trigger                  |
      | delay[number]              | 2                                         |
      | delay[timeunit]            | days                                      |
    When I set the following fields to these values:
      | Subplugin Name             | Manual trigger                            |
    And I press "reload"
    And I set the following fields to these values:
      | Instance Name              | My updated Trigger                        |
      | Icon                       | t/delete                                  |
      | Action name                | Delete course                             |
      | Capability                 | moodle/course:manageactivities            |
    And I press "Save changes"
    Then I should see "Manual trigger"
    When I click on the tool "Edit" in the "Trigger" row of the "tool_cleanupcourses_workflows" table
    Then the following fields match these values:
      | Instance Name              | My updated Trigger                        |
      | Subplugin Name             | Manual trigger                            |
      | Icon                       | t/delete                                  |
      | Action name                | Delete course                             |
      | Capability                 | moodle/course:manageactivities            |



