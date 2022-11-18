@tool @tool_lifecycle
Feature: Add a workflow definition

  @javascript
  Scenario: Add a new workflow definition without steps
  For displaying the additional trigger settings the "Save changes" button is used.
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow drafts" in site administration
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    When I select "Manual trigger" from the "tool_lifecycle-choose-trigger" singleselect
    Then I should see "Create trigger"
    When I set the following fields to these values:
      | Instance name              | My Trigger                                |
    And I press "Save changes"
    # The manual trigger requires additional settings. For that reason the form reloads with some more fields.
    Then I should see "Required"
    And I should see "Specific settings of the trigger type"
    When I set the following fields to these values:
      | Icon                       | t/delete                                  |
      | Action name                | Delete course                             |
      | Capability                 | moodle/course:manageactivities            |
    And I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    And I should see "manual"
    When I click on "Workflow drafts" "link"
    Then I should see "My Workflow"

  Scenario: Add a new workflow definition with steps
  For displaying the additional trigger settings the "reload" button is used.
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow drafts" in site administration
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    When I select "Start date delay trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name              | My Trigger                                |
      | delay[number]    | 2                          |
      | delay[timeunit]  | days                       |
    And I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    And I should see "startdatedelay"
    When I select "Delete course step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course"
    And I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    And I should see "deletecourse"

  Scenario: Add a new workflow definition and alter trigger
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow drafts" in site administration
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    When I select "Start date delay trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name    | My Trigger                 |
      | delay[number]    | 2                          |
      | delay[timeunit]  | days                       |
    And I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    And I should see "startdatedelay"
    When I click on "Edit" in the trigger "Trigger"
    Then the following fields match these values:
      | Instance name              | My Trigger                                |
      | delay[number]              | 2                                         |
      | delay[timeunit]            | days                                      |
    And I set the following fields to these values:
      | Instance name              | Other Trigger                             |
      | delay[number]              | 4                                         |
      | delay[timeunit]            | weeks                                     |
    And I press "Save changes"
    When I click on "Edit" in the trigger "Trigger"
    Then the following fields match these values:
      | Instance name              | Other Trigger                             |
      | delay[number]              | 4                                         |
      | delay[timeunit]            | weeks                                     |

  Scenario: Add a new workflow definition with steps and rearange
    Given I log in as "admin"
    And I navigate to "Plugins > Admin tools > Life Cycle > Workflow drafts" in site administration
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | My Workflow                               |
      | Displayed workflow title   | Teachers view on workflow                 |
    When I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    When I select "Start date delay trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name    | My Trigger                 |
      | delay[number]    | 2                          |
      | delay[timeunit]  | days                       |
    And I press "Save changes"
    Then I should see "Displayed to teachers as Teachers view on workflow"
    And I should see "startdatedelay"
    When I select "Delete course step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course 1"
    And I press "Save changes"
    And I select "deletecourse" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Delete Course 2"
    And I press "Save changes"
    And I select "createbackup" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Create backup step"
    And I press "Save changes"
    Then the step "Delete Course 1" should be at the 1 position
    And the step "Delete Course 2" should be at the 2 position
    And the step "Create backup step" should be at the 3 position
    And I click on "Move down" in the step "Delete Course 1"
    Then the step "Delete Course 1" should be at the 2 position
    And the step "Delete Course 2" should be at the 1 position
    And the step "Create backup step" should be at the 3 position
    And I click on "Move up" in the step "Create backup step"
    Then the step "Delete Course 1" should be at the 3 position
    And the step "Delete Course 2" should be at the 1 position
    And the step "Create backup step" should be at the 2 position
