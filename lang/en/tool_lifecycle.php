<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Life cycle langauge strings.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



$string['pluginname'] = 'Life Cycle';
$string['plugintitle'] = 'Course Life Cycle';

$string['lifecycle:managecourses'] = 'May manage courses in tool_lifecycle';

$string['general_config_header'] = "General & Subplugins";
$string['config_delay_duration'] = 'Duration of a course delay';
$string['config_delay_duration_desc'] = 'Defines the time frame, which a course is excluded from the life cycle processes, when rolled back via user interaction.';
$string['active_processes_list_header'] = 'Active Processes';
$string['adminsettings_heading'] = 'Workflow Settings';
$string['active_manual_workflows_heading'] = 'Active Manual Workflows';
$string['active_automatic_workflows_heading'] = 'Active Automatic Workflows';
$string['workflow_definition_heading'] = 'Workflow Definitions';
$string['adminsettings_edit_workflow_definition_heading'] = 'Workflow Definition';
$string['adminsettings_workflow_definition_steps_heading'] = 'Workflow Steps';
$string['adminsettings_edit_trigger_instance_heading'] = 'Trigger for workflow \'{$a}\'';
$string['adminsettings_edit_step_instance_heading'] = 'Step Instance for workflow \'{$a}\'';
$string['add_new_step_instance'] = 'Add New Step Instance...';
$string['step_settings_header'] = 'Specific settings of the step type';
$string['trigger_settings_header'] = 'Specific settings of the trigger type';
$string['general_settings_header'] = 'General Settings';
$string['followedby_none'] = 'None';
$string['invalid_workflow'] = 'Invalid workflow configuration';
$string['invalid_workflow_details'] = 'Go to details view, to create a trigger for this workflow';
$string['active_workflow_not_changeable'] = 'The workflow instance is active. It is not possible to change any of its steps.';
$string['active_workflow_not_removeable'] = 'The workflow instance is active. It is not possible to remove it.';
$string['invalid_workflow_cannot_be_activated'] = 'The workflow definition is invalid, thus it cannot be activated.';
$string['trigger_does_not_exist'] = 'The requested trigger could not be found.';
$string['cannot_trigger_workflow_manually'] = 'The requested workflow could not be triggered manually.';
$string['error_wrong_trigger_selected'] = 'You tried to request a non-manual trigger.';

$string['lifecycle_task'] = 'Run the life cycle processes';

$string['trigger_subpluginname'] = 'Subplugin Name';
$string['trigger_instancename'] = 'Instance Name';
$string['trigger_enabled'] = 'Enabled';
$string['trigger_sortindex'] = 'Up/Down';
$string['trigger_workflow'] = 'Workflow';

$string['add_workflow'] = 'Add Workflow';
$string['workflow_title'] = 'Title';
$string['workflow_displaytitle'] = 'Displayed workflow title';
$string['workflow_displaytitle_help'] = 'This title is displayed to users when managing their courses.';
$string['workflow_active'] = 'Active';
$string['workflow_processes'] = 'Active processes';
$string['workflow_timeactive'] = 'Active since';
$string['workflow_sortindex'] = 'Up/Down';
$string['workflow_tools'] = 'Tools';
$string['viewsteps'] = 'View Workflow Steps';
$string['editworkflow'] = 'Edit Title';
$string['duplicateworkflow'] = 'Duplicate Workflow';
$string['deleteworkflow'] = 'Delete Workflow';
$string['activateworkflow'] = 'Activate';
$string['workflow_duplicate_title'] = '{$a} (Copy)';

$string['step_type'] = 'Type';
$string['step_subpluginname'] = 'Subplugin Name';
$string['step_instancename'] = 'Instance Name';
$string['step_sortindex'] = 'Up/Down';
$string['step_edit'] = 'Edit';
$string['step_show'] = 'Show';
$string['step_delete'] = 'Delete';

$string['trigger'] = 'Trigger';
$string['step'] = 'Process step';

$string['workflow_trigger'] = 'Trigger for the workflow';

$string['lifecycletrigger'] = 'Trigger';
$string['lifecyclestep'] = 'Process step';

$string['subplugintype_lifecycletrigger'] = 'Trigger for starting a lifecycle process';
$string['subplugintype_lifecycletrigger_plural'] = 'Triggers for starting a lifecycle process';
$string['subplugintype_lifecyclestep'] = 'Step within a lifecycle process';
$string['subplugintype_lifecyclestep_plural'] = 'Steps within a lifecycle process';

$string['nointeractioninterface'] = 'No Interaction Interface available!';
$string['tools'] = 'Tools';
$string['status'] = 'Status';
$string['date'] = 'Due date';

$string['nostepfound'] = 'A step with the given stepid could not be found!';
$string['noprocessfound'] = 'A process with the given processid could not be found!';

$string['nocoursestodisplay'] = 'There are currently no courses, which require your attention!';

$string['course_backups_list_header'] = 'Course Backups';
$string['backupcreated'] = 'Created at';
$string['restore'] = 'restore';

$string['workflownotfound'] = 'Workflow with id {$a} could not be found';

// View.php.
$string['tablecoursesrequiringattention'] = 'Courses that require your attention';
$string['tablecoursesremaining'] = 'Remaining courses';
$string['viewheading'] = 'Manage courses';
$string['interaction_success'] = 'Action successfully saved.';
$string['manual_trigger_success'] = 'Workflow started successfully.';
$string['manual_trigger_process_existed'] = 'A workflow for this course already exists.';

$string['workflow_started'] = 'Workflow started.';
$string['workflow_is_running'] = 'Workflow is running.';