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

$string['abortdisableworkflow'] = 'Disable workflow (abort processes, maybe unsafe!)';
$string['abortdisableworkflow_confirm'] = 'The workflow is going to be disabled and all running processes of this workflow will be aborted. Are you sure?';
$string['abortprocesses'] = 'Abort running processes (maybe unsafe!)';
$string['abortprocesses_confirm'] = 'All running processes of this workflow will be aborted. Are you sure?';
$string['activateworkflow'] = 'Activate';
$string['active'] = 'Active';
$string['active_automatic_workflows_heading'] = 'Active automatic workflows';
$string['active_manual_workflows_heading'] = 'Active manual workflows';
$string['active_workflow_not_changeable'] = 'The workflow instance was already activated. Depending on the step type, some of its settings might be still editable. Changes to triggers will not affect already triggered courses.';
$string['active_workflow_not_removeable'] = 'The workflow instance is active. It is not possible to remove it.';
$string['active_workflows_header'] = 'Active workflows';
$string['active_workflows_list'] = 'List active workflows';
$string['add_new_step_instance'] = 'Add new step instance...';
$string['add_new_trigger_instance'] = 'Add new trigger instance...';
$string['add_workflow'] = 'Create new workflow';
$string['adminsettings_edit_step_instance_heading'] = 'Step instance for workflow \'{$a}\'';
$string['adminsettings_edit_trigger_instance_heading'] = 'Trigger for workflow \'{$a}\'';
$string['adminsettings_edit_workflow_definition_heading'] = 'Workflow definition';
$string['adminsettings_heading'] = 'Workflow settings';
$string['adminsettings_workflow_definition_steps_heading'] = 'Workflow steps';
$string['all_delays'] = 'All delays';
$string['anonymous_user'] = 'Anonymous User';
$string['apply'] = 'Apply';
$string['backupcreated'] = 'Created at';
$string['backupworkflow'] = 'Backup workflow';
$string['cachedef_mformdata'] = 'Caches the mform data.';
$string['cannot_trigger_workflow_manually'] = 'The requested workflow could not be triggered manually.';
$string['config_backup_path'] = 'Path of the lifecycle backup folder';
$string['config_backup_path_desc'] = 'This settings defines the storage location of the backups created by the backup step.
The path has to be specified as an absolute path on your server.';
$string['config_delay_duration'] = 'Default duration of a course delay';
$string['config_delay_duration_desc'] = 'This setting defines the default delay duration of a workflow
in case one of its processes is rolled back or finishes.
The delay duration determines how long a course will be excepted from being processed again in either of the cases.';
$string['config_showcoursecounts'] = 'Show amount of courses which will be triggered';
$string['config_showcoursecounts_desc'] = 'The workflow overview page by default shows the amount of courses which will be
triggered by the configured triggers which can be load heavy. Disable this option if you experience issues loading the workflow
overview.';
$string['course_backups_list_header'] = 'Course backups';
$string['courseid'] = 'Course ID';
$string['coursename'] = 'Course name';
$string['courses_will_be_excluded'] = '{$a} Courses will be excluded';
$string['courses_will_be_excluded_total'] = '{$a} courses will be excluded in total';
$string['courses_will_be_triggered'] = '{$a} Courses will be triggered';
$string['courses_will_be_triggered_total'] = '{$a} courses will be triggered in total';
$string['create_copy'] = 'Create copy';
$string['create_step'] = 'Create step';
$string['create_trigger'] = 'Create trigger';
$string['create_workflow_from_existing'] = 'Copy new workflow from existing';
$string['date'] = 'Due date';
$string['deactivated'] = 'Deactivated';
$string['deactivated_workflows_list'] = 'List deactivated workflows';
$string['deactivated_workflows_list_header'] = 'Deactivated workflows';
$string['delayed_courses_header'] = 'Delayed courses';
$string['delayed_for_workflow_until'] = 'Delayed for "{$a->name}" until {$a->date}';
$string['delayed_for_workflows'] = 'Delayed for {$a} workflows';
$string['delayed_globally'] = 'Delayed globally until {$a}';
$string['delayed_globally_and_seperately'] = 'Delayed globally and seperately for {$a} workflows';
$string['delayed_globally_and_seperately_for_one'] = 'Delayed globally and seperately for 1 workflow';
$string['delays'] = 'Delays';
$string['delays_for_workflow'] = 'Delays for "{$a}"';
$string['delete_all_delays'] = 'Delete all delays';
$string['delete_delay'] = 'Delete delay';
$string['deleteworkflow'] = 'Delete workflow';
$string['deleteworkflow_confirm'] = 'The workflow is going to be deleted. This can\'t be undone. Are you sure?';
$string['details:displaytitle'] = 'Displayed to teachers as <b>{$a}</b>.';
$string['details:finishdelay'] = 'When a course has finished the workflow, it will be delayed for <b>{$a}</b>.';
$string['details:globaldelay_no'] = 'These delays apply <b>only to this workflow</b>.';
$string['details:globaldelay_yes'] = 'These delays apply <b>to all workflows</b>.';
$string['details:rollbackdelay'] = 'When a course is rolled back, it will be delayed for <b>{$a}</b>.';
$string['disableworkflow'] = 'Disable workflow (processes keep running)';
$string['disableworkflow_confirm'] = 'The workflow is going to be disabled. Are you sure?';
$string['download'] = 'Download';
$string['draft'] = 'Draft';
$string['duplicateworkflow'] = 'Duplicate workflow';
$string['edit_step'] = 'Edit step';
$string['edit_trigger'] = 'Edit trigger';
$string['editworkflow'] = 'Edit general settings';
$string['error_wrong_trigger_selected'] = 'You tried to request a non-manual trigger.';
$string['errorbackuppath'] = "Error while trying to create the backup directory. You might be missing the permission to do so.
Please check your path at Site administration/Plugins/Admin tools/Life Cycle/General & subplugins/backup_path.";
$string['errornobackup'] = "No backup was created at the specified directory, reasons unknown.";
$string['find_course_list_header'] = 'Find courses';
$string['followedby_none'] = 'None';
$string['force_import'] = 'Try ignoring errors and import the workflow anyway. <b>Use this at your own risk!</b>';
$string['forselected'] = 'For all selected processes';
$string['general_config_header'] = "General & subplugins";
$string['general_settings_header'] = 'General settings';
$string['globally'] = 'Global delays';
$string['globally_until_date'] = 'Globally until {$a}';
$string['interaction_success'] = 'Action successfully saved.';
$string['invalid_workflow'] = 'Invalid workflow configuration';
$string['invalid_workflow_cannot_be_activated'] = 'The workflow definition is invalid, thus it cannot be activated.';
$string['invalid_workflow_details'] = 'Go to details view, to create a trigger for this workflow';
$string['lastaction'] = 'Last action on';
$string['lifecycle:managecourses'] = 'May manage courses in tool_lifecycle';
$string['lifecycle_cleanup_task'] = 'Delete old delay entries for life cycle workflows';
$string['lifecycle_error_notify_task'] = 'Notify the admin upon errors in tool_lifecycle processes';
$string['lifecycle_task'] = 'Run the life cycle processes';
$string['lifecyclestep'] = 'Process step';
$string['lifecycletrigger'] = 'Trigger';
$string['managecourses_link'] = 'Manage courses';
$string['manual_trigger_process_existed'] = 'A workflow for this course already exists.';
$string['manual_trigger_success'] = 'Workflow started successfully.';
$string['move_down'] = 'Move down';
$string['move_up'] = 'Move up';
$string['name_until_date'] = '"{$a->name}" until {$a->date}';
$string['nocoursestodisplay'] = 'There are currently no courses which require your attention!';
$string['nointeractioninterface'] = 'No interaction interface available!';
$string['noprocesserrors'] = 'There are no process errors to handle!';
$string['noprocessfound'] = 'A process with the given processid could not be found!';
$string['noremainingcoursestodisplay'] = 'There are currently no remaining courses!';
$string['nostepfound'] = 'A step with the given stepid could not be found!';
$string['notifyerrorsemailcontent'] = 'There are {$a->amount} new tool_lifecycle process errors waiting to be fixed!' . "\n" . 'Please review them at {$a->url}.';
$string['notifyerrorsemailcontenthtml'] = 'There are {$a->amount} new tool_lifecycle process errors waiting to be fixed!<br>Please review them at the <a href="{$a->url}">error handling overview</a>.';
$string['notifyerrorsemailsubject'] = 'There are {$a->amount} new tool_lifecycle process errors waiting to be fixed!';
$string['overview:add_trigger'] = 'Add trigger';
$string['overview:add_trigger_help'] = 'You can only add one instance of each trigger type.';
$string['overview:trigger'] = 'Trigger';
$string['overview:trigger_help'] = 'A course will only trigger a workflow, if all triggers agree on it (AND operation).<br><br>
Courses which are delayed, or already in another workflow are not included in the displayed counts.<br>
Still, these numbers are only approximates, since it could be that a course is excluded by another workflow, or will trigger another workflow before this one.';
$string['pluginname'] = 'Life Cycle';
$string['plugintitle'] = 'Course Life Cycle';
$string['privacy:metadata:tool_lifecycle_action_log'] = 'A log of actions done by course managers.';
$string['privacy:metadata:tool_lifecycle_action_log:action'] = 'Identifier of the action that was done.';
$string['privacy:metadata:tool_lifecycle_action_log:courseid'] = 'ID of the Course the action was done for';
$string['privacy:metadata:tool_lifecycle_action_log:processid'] = 'ID of the Process the action was done in.';
$string['privacy:metadata:tool_lifecycle_action_log:stepindex'] = 'Index of the Step in the Workflow, the action was done for.';
$string['privacy:metadata:tool_lifecycle_action_log:time'] = 'Time when the action was done.';
$string['privacy:metadata:tool_lifecycle_action_log:userid'] = 'ID of the user that did the action.';
$string['privacy:metadata:tool_lifecycle_action_log:workflowid'] = 'ID of the Workflow the action was done in.';
$string['proceed'] = 'Proceed';
$string['process_errors_header'] = 'Error handling';
$string['process_proceeded_event'] = 'A process has been proceeded';
$string['process_rollback_event'] = 'A process has been rolled back';
$string['process_triggered_event'] = 'A process has been triggered';
$string['restore'] = 'Restore';
$string['restore_error_in_step'] = 'An error occurred when importing step "{$a}": ';
$string['restore_error_in_trigger'] = 'An error occurred when importing trigger "{$a}": ';
$string['restore_step_does_not_exist'] = 'The step {$a} is not installed, but is included in the backup file. Please installed it first and try again.';
$string['restore_subplugins_invalid'] = 'Wrong format of the backup file. The structure of the subplugin elements is not as expected.';
$string['restore_trigger_does_not_exist'] = 'The trigger {$a} is not installed, but is included in the backup file. Please installed it first and try again.';
$string['restore_workflow_not_found'] = 'Wrong format of the backup file. The workflow could not be found.';
$string['rollback'] = 'Rollback';
$string['see_in_workflow'] = 'See in workflow';
$string['show_delays'] = 'Kind of view';
$string['status'] = 'Status';
$string['step'] = 'Process step';
$string['step_delete'] = 'Delete';
$string['step_edit'] = 'Edit';
$string['step_instancename'] = 'Instance name';
$string['step_instancename_help'] = 'Step instance title (visible for admins only).';
$string['step_settings_header'] = 'Specific settings of the step type';
$string['step_show'] = 'Show';
$string['step_sortindex'] = 'Up/Down';
$string['step_subpluginname'] = 'Subplugin name';
$string['step_subpluginname_help'] = 'Step subplugin/trigger title (visible for admins only).';
$string['step_type'] = 'Type';
$string['subplugintype_lifecyclestep'] = 'Step within a lifecycle process';
$string['subplugintype_lifecyclestep_plural'] = 'Steps within a lifecycle process';
$string['subplugintype_lifecycletrigger'] = 'Trigger for starting a lifecycle process';
$string['subplugintype_lifecycletrigger_plural'] = 'Triggers for starting a lifecycle process';
$string['tablecourseslog'] = 'Past actions';
$string['tablecoursesremaining'] = 'Remaining courses';
$string['tablecoursesrequiringattention'] = 'Courses that require your attention';
$string['tools'] = 'Tools';
$string['trigger'] = 'Trigger';
$string['trigger_does_not_exist'] = 'The requested trigger could not be found.';
$string['trigger_enabled'] = 'Enabled';
$string['trigger_instancename'] = 'Instance name';
$string['trigger_instancename_help'] = 'Trigger instance title (visible for admins only).';
$string['trigger_settings_header'] = 'Specific settings of the trigger type';
$string['trigger_sortindex'] = 'Up/Down';
$string['trigger_subpluginname'] = 'Subplugin name';
$string['trigger_subpluginname_help'] = 'Step subplugin/trigger title (visible for admins only).';
$string['trigger_workflow'] = 'Workflow';
$string['upload_workflow'] = 'Upload workflow';
$string['viewheading'] = 'Manage courses';
$string['viewsteps'] = 'View workflow steps';
$string['workflow'] = 'Workflow';
$string['workflow_active'] = 'Active';
$string['workflow_definition_heading'] = 'Workflow definitions';
$string['workflow_delayforallworkflows'] = 'Delay for all workflows?';
$string['workflow_delayforallworkflows_help'] = 'If ticked, the durations on top do not only delay the execution
of this workflow for a course, but for all other workflows as well. Thus, until the time passes no process can be started
for the respective course.';
$string['workflow_displaytitle'] = 'Displayed workflow title';
$string['workflow_displaytitle_help'] = 'This title is displayed to users when managing their courses.';
$string['workflow_drafts_header'] = 'Workflow drafts';
$string['workflow_drafts_list'] = 'List workflow drafts';
$string['workflow_duplicate_title'] = '{$a} (Copy)';
$string['workflow_finishdelay'] = 'Delay in case of finished course';
$string['workflow_finishdelay_help'] = 'If a course has finished a process instance of this workflow,
this value describes the time until a process for this combination of course and workflow can be started again.';
$string['workflow_is_running'] = 'Workflow is running.';
$string['workflow_not_removeable'] = 'It is not possible to remove this workflow instance. Maybe it still has running processes?';
$string['workflow_processes'] = 'Active processes';
$string['workflow_rollbackdelay'] = 'Delay in case of rollback';
$string['workflow_rollbackdelay_help'] = 'If a course was rolled back within a process instance of this workflow,
this value describes the time until a process for this combination of course and workflow can be started again.';
$string['workflow_sortindex'] = 'Up/Down';
$string['workflow_started'] = 'Workflow started.';
$string['workflow_timeactive'] = 'Active since';
$string['workflow_timedeactive'] = 'Deactivated since';
$string['workflow_title'] = 'Title';
$string['workflow_title_help'] = 'Workflow title (visible for admins only).';
$string['workflow_tools'] = 'Actions';
$string['workflow_trigger'] = 'Trigger for the workflow';
$string['workflow_was_not_imported'] = 'The workflow was not imported!';
$string['workflownotfound'] = 'Workflow with id {$a} could not be found';
$string['workflowoverview'] = 'View workflow';
$string['workflowoverview_list_header'] = 'Details of Workflows';
