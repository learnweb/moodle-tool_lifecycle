@tool @tool_lifecycle
Feature: Select setcustomfield step and test view and actions

  Background:
    Given the following "custom field categories" exist:
      | name  | component   | area   | itemid |
      | Other | core_course | course | 0      |
    And the following "custom fields" exist:
      | name          | category | type     | shortname | configdata                                          |
      | FieldCB       | Other    | checkbox | checkbox  | {"checkbydefault":1}                                |
      | FieldDate     | Other    | date     | date      | {"includetime":0}                                   |
      | FieldSelect   | Other    | select   | select    | {"options":"a\r\nb\r\nc","defaultvalue":"b"}            |
      | FieldText     | Other    | text     | text      | {"defaultvalue":"Hello"}                            |
      | FieldTextarea | Other    | textarea | textarea  | {"defaultvalue":"Some text","defaultvalueformat":1} |
      | FieldNumber   | Other    | number   | number    | {"defaultvalue":47}                                 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "categories" exist:
      | name    | category | idnumber |
      | CAT A   | 0        | cata     |
    And the following "courses" exist:
      | fullname     | shortname  | category |
      | Course A     | CA         | cata     |
      | Course C     | CC         | cata     |
    And the following "course enrolments" exist:
      | user     | course  | role           |
      | teacher1 | CA      | editingteacher |
      | teacher1 | CC      | editingteacher |
    And I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | Test   |
      | Displayed workflow title   | Test   |
      | rollbackdelay[number]      | 0              |
      | finishdelay[number]        | 0              |
    And I press "Save changes"
    And I select "Manual trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name              | Test                       |
      | Action name                | Test                       |
      | Capability                 | moodle/course:manageactivities     |
    And I press "Save changes"
    And I select "Set course custom field step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "set field step"

  @javascript
  Scenario Outline: Validation set custom field step
    When I set the following fields to these values:
      | Course customfield          | <setfield>     |
      | Set field to                | specific value |
      | Value                       | <setvalue>     |
    And I press "Save changes"
    And I should see "<expected>"
    Examples:
      | setfield      | setvalue   | expected      |
      | FieldNumber   | uu         | Invalid value |
      | FieldSelect   | 5          | Invalid value |
      | FieldSelect   | xxx        | Invalid value |
      | FieldSelect   | 0          | Add new trigger instance |

  @javascript
  Scenario Outline: Value custom field
    When I set the following fields to these values:
      | Course customfield          | <setfield>     |
      | Set field to                | specific value |
      | Value                       | <setvalue>        |
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    And I click on the tool "Test" in the "Course A" row of the "tool_lifecycle_remaining" table

    When I log out
    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds

    And I log in as "teacher1"
    And I am on "Course A" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And the field "<getfield>" matches value "<getvalue>"

    Examples:
      | setfield      | getfield                |  setvalue          | getvalue           |
      | FieldSelect   | FieldSelect             |  1                 | a                  |
      | FieldSelect   | FieldSelect             |  a                 | a                  |
      | FieldSelect   | FieldSelect             |  c                 | c                  |
      | FieldTextarea | FieldTextarea           |  Text in text area |  Text in text area |
      | FieldText     | FieldText               |  this is a text    |  this is a text    |
      | FieldNumber   | FieldNumber             |  54                |  54                |
      | FieldCB       | FieldCB                 |  1                 |  1                 |
      | FieldCB       | FieldCB                 |  0                 |  0                 |

@javascript
Scenario Outline: Empty custom field
    When I set the following fields to these values:
      | Course customfield          | <setfield>     |
      | Set field to                | empty       |
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    And I click on the tool "Test" in the "Course A" row of the "tool_lifecycle_remaining" table

    When I log out
    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds

    And I log in as "teacher1"
    And I am on "Course A" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And the field "<getfield>" matches value "<value>"

  Examples:
    | setfield      | getfield                |  value    |
    | FieldSelect   | FieldSelect             |           |
    | FieldTextarea | FieldTextarea           |           |
    | FieldDate     | customfield_date[enabled]| 0        |
    | FieldText     | FieldText               |           |
    | FieldNumber   | FieldNumber             |  0        |
    | FieldCB       | FieldCB                 |  0        |

  @javascript
  Scenario: Set custom date field
    When I set the following fields to these values:
      | Course customfield          | FieldDate |
      | Set field to                | now       |
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    And I click on the tool "Test" in the "Course A" row of the "tool_lifecycle_remaining" table

    When I log out
    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds

    And I log in as "teacher1"
    And I am on "Course A" course homepage
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And the field "customfield_date[enabled]" matches value "1"
