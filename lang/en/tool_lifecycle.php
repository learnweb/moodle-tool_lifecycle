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
$string['managecourses_link'] = 'Manage courses';

$string['general_config_header'] = "General & subplugins";
$string['config_delay_duration'] = 'Default duration of a course delay';
$string['config_delay_duration_desc'] = 'This setting defines the default delay duration of a workflow
in case one of its processes is rolled back or finishes.
The delay duration determines how long a course will be excepted from being processed again in either of the cases.';
$string['active_processes_list_header'] = 'Active processes';
$string['adminsettings_heading'] = 'Workflow settings';
$string['active_manual_workflows_heading'] = 'Active manual workflows';
$string['active_automatic_workflows_heading'] = 'Active automatic workflows';
$string['workflow_definition_heading'] = 'Workflow definitions';
$string['adminsettings_edit_workflow_definition_heading'] = 'Workflow definition';
$string['adminsettings_workflow_definition_steps_heading'] = 'Workflow steps';
$string['adminsettings_edit_trigger_instance_heading'] = 'Trigger for workflow \'{$a}\'';
$string['adminsettings_edit_step_instance_heading'] = 'Step instance for workflow \'{$a}\'';
$string['add_new_step_instance'] = 'Add new step instance...';
$string['add_new_trigger_instance'] = 'Add new trigger instance...';
$string['step_settings_header'] = 'Specific settings of the step type';
$string['trigger_settings_header'] = 'Specific settings of the trigger type';
$string['general_settings_header'] = 'General settings';
$string['followedby_none'] = 'None';
$string['invalid_workflow'] = 'Invalid workflow configuration';
$string['invalid_workflow_details'] = 'Go to details view, to create a trigger for this workflow';
$string['active_workflow_not_changeable'] = 'The workflow instance was already activated. It is not possible to change any of its steps anymore.';
$string['active_workflow_not_removeable'] = 'The workflow instance is active. It is not possible to remove it.';
$string['workflow_not_removeable'] = 'It is not possible to remove this workflow instance. Maybe it still has running processes?';
$string['invalid_workflow_cannot_be_activated'] = 'The workflow definition is invalid, thus it cannot be activated.';
$string['trigger_does_not_exist'] = 'The requested trigger could not be found.';
$string['cannot_trigger_workflow_manually'] = 'The requested workflow could not be triggered manually.';
$string['error_wrong_trigger_selected'] = 'You tried to request a non-manual trigger.';

$string['lifecycle_task'] = 'Run the life cycle processes';
$string['lifecycle_cleanup_task'] = 'Delete old delay entries for life cycle workflows';

$string['trigger_subpluginname'] = 'Subplugin name';
$string['trigger_subpluginname_help'] = 'Step subplugin/trigger title (visible for admins only).';
$string['trigger_instancename'] = 'Instance name';
$string['trigger_instancename_help'] = 'Trigger instance title (visible for admins only).';
$string['trigger_enabled'] = 'Enabled';
$string['trigger_sortindex'] = 'Up/Down';
$string['trigger_workflow'] = 'Workflow';

$string['workflow'] = 'Workflow';
$string['add_workflow'] = 'Add workflow';
$string['upload_workflow'] = 'Upload workflow';
$string['workflow_title'] = 'Title';
$string['workflow_title_help'] = 'Workflow title (visible for admins only).';
$string['workflow_displaytitle'] = 'Displayed workflow title';
$string['workflow_displaytitle_help'] = 'This title is displayed to users when managing their courses.';
$string['workflow_rollbackdelay'] = 'Delay in case of rollback';
$string['workflow_rollbackdelay_help'] = 'If a course was rolled back within a process instance of this workflow,
this value describes the time until a process for this combination of course and workflow can be started again.';
$string['workflow_finishdelay'] = 'Delay in case of finished course';
$string['workflow_finishdelay_help'] = 'If a course has finished a process instance of this workflow,
this value describes the time until a process for this combination of course and workflow can be started again.';
$string['workflow_delayforallworkflows'] = 'Delay for all workflows?';
$string['workflow_delayforallworkflows_help'] = 'If ticked, the durations on top do not only delay the execution
of this workflow for a course, but for all other workflows as well. Thus, until the time passes no process can be started
for the respective course.';
$string['workflow_active'] = 'Active';
$string['workflow_processes'] = 'Active processes';
$string['workflow_timeactive'] = 'Active since';
$string['workflow_sortindex'] = 'Up/Down';
$string['workflow_tools'] = 'Actions';
$string['viewsteps'] = 'View workflow steps';
$string['editworkflow'] = 'Edit general settings';
$string['backupworkflow'] = 'Backup workflow';
$string['duplicateworkflow'] = 'Duplicate workflow';
$string['deleteworkflow'] = 'Delete workflow';
$string['deleteworkflow_confirm'] = 'The workflow is going to be deleted. This can\'t be undone. Are you sure?';
$string['activateworkflow'] = 'Activate';
$string['disableworkflow'] = 'Disable workflow (processes keep running)';
$string['disableworkflow_confirm'] = 'The workflow is going to be disabled. Are you sure?';
$string['abortdisableworkflow'] = 'Disable workflow (abort processes, maybe unsafe!)';
$string['abortdisableworkflow_confirm'] = 'The workflow is going to be disabled and all running processes of this workflow will be aborted. Are you sure?';
$string['abortprocesses'] = 'Abort running processes (maybe unsafe!)';
$string['abortprocesses_confirm'] = 'All running processes of this workflow will be aborted. Are you sure?';
$string['workflow_duplicate_title'] = '{$a} (Copy)';

// Deactivated workflows.
$string['deactivated_workflows_list'] = 'List deactivated workflows';
$string['deactivated_workflows_list_header'] = 'Deactivated workflows';
$string['workflow_timedeactive'] = 'Deactivated since';
$string['active_workflows_list'] = 'List active workflows and workflow definitions';

$string['step_type'] = 'Type';
$string['step_subpluginname'] = 'Subplugin name';
$string['step_subpluginname_help'] = 'Step subplugin/trigger title (visible for admins only).';
$string['step_instancename'] = 'Instance name';
$string['step_instancename_help'] = 'Step instance title (visible for admins only).';
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

$string['nointeractioninterface'] = 'No interaction interface available!';
$string['tools'] = 'Tools';
$string['status'] = 'Status';
$string['date'] = 'Due date';

$string['nostepfound'] = 'A step with the given stepid could not be found!';
$string['noprocessfound'] = 'A process with the given processid could not be found!';

$string['nocoursestodisplay'] = 'There are currently no courses which require your attention!';

$string['course_backups_list_header'] = 'Course backups';
$string['backupcreated'] = 'Created at';
$string['restore'] = 'Restore';
$string['download'] = 'Download';

$string['workflownotfound'] = 'Workflow with id {$a} could not be found';

// View.php.
$string['tablecoursesrequiringattention'] = 'Courses that require your attention';
$string['tablecoursesremaining'] = 'Remaining courses';
$string['tablecourseslog'] = 'Past actions';
$string['viewheading'] = 'Manage courses';
$string['interaction_success'] = 'Action successfully saved.';
$string['manual_trigger_success'] = 'Workflow started successfully.';
$string['manual_trigger_process_existed'] = 'A workflow for this course already exists.';

$string['coursename'] = 'Course name';
$string['lastaction'] = 'Last action on';
$string['anonymous_user'] = 'Anonymous User';

$string['workflow_started'] = 'Workflow started.';
$string['workflow_is_running'] = 'Workflow is running.';

// Backup & Restore.
$string['restore_workflow_not_found'] = 'Wrong format of the backup file. The workflow could not be found.';
$string['restore_subplugins_invalid'] = 'Wrong format of the backup file. The structure of the subplugin elements is not as expected.';
$string['restore_step_does_not_exist'] = 'The step {$a} is not installed, but is included in the backup file. Please installed it first and try again.';
$string['restore_trigger_does_not_exist'] = 'The trigger {$a} is not installed, but is included in the backup file. Please installed it first and try again.';

// Events.
$string['process_triggered_event'] = 'A process has been triggered';
$string['process_proceeded_event'] = 'A process has been proceeded';
$string['process_rollback_event'] = 'A process has been rolled back';

// Privacy API
$string['privacy:metadata:tool_lifecycle_action_log'] = 'A log of actions done by course managers.';
$string['privacy:metadata:tool_lifecycle_action_log:processid'] = 'ID of the Process the action was done in.';
$string['privacy:metadata:tool_lifecycle_action_log:workflowid'] = 'ID of the Workflow the action was done in.';
$string['privacy:metadata:tool_lifecycle_action_log:courseid'] = 'ID of the Course the action was done for';
$string['privacy:metadata:tool_lifecycle_action_log:stepindex'] = 'Index of the Step in the Workflow, the action was done for.';
$string['privacy:metadata:tool_lifecycle_action_log:time'] = 'Time when the action was done.';
$string['privacy:metadata:tool_lifecycle_action_log:userid'] = 'ID of the user that did the action.';
$string['privacy:metadata:tool_lifecycle_action_log:action'] = 'Identifier of the action that was done.';

// Delays.
$string['delayed_courses_header'] = 'Delayed courses';
$string['delete_delay'] = 'Delete delay';
$string['globally_until_date'] = 'Globally until {$a}';
$string['name_until_date'] = '"{$a->name}" until {$a->date}';
$string['delayed_globally_and_seperately'] = 'Delayed globally and seperately for {$a} workflows';
$string['delayed_globally_and_seperately_for_one'] = 'Delayed globally and seperately for 1 workflow';
$string['delayed_globally'] = 'Delayed globally until {$a}';
$string['delayed_for_workflow_until'] = 'Delayed for "{$a->name}" until {$a->date}';
$string['delayed_for_workflows'] = 'Delayed for {$a} workflows';
$string['delays'] = 'Delays';
$string['apply'] = 'Apply';
$string['show_delays'] = 'Kind of view';
$string['all_delays'] = 'All delays';
$string['globally'] = 'Global delays';
$string['delays_for_workflow'] = 'Delays for "{$a}"';
$string['delete_all_delays'] = 'Delete all delays';
