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
 * Admin tool "Course Life Cycle" - Subplugin "Opencast step" - Language pack
 *
 * @package    lifecyclestep_opencast
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachedef_ocworkflows'] = 'Cached Opencast Workflow Definitions for Lifecycle Opencast Step';
$string['cachedef_processedvideos'] = 'Cached Opencast Videos that are done processing for Lifecycle Opencast Step';
$string['cachedef_seriesvideos'] = 'Cached Opencast Series Videos for Lifecycle Opencast Step';
$string['coursefullnameunknown'] = 'Unkown coursename';
$string['error_removeseriesmapping'] = 'Unable to remove series mapping record.';
$string['error_removeseriestacl'] = 'Unable to remove course ACLs from the series and its events properly.';
$string['errorexception_body'] = 'There was a fatal error during the opencast step process for {$a->coursefullname} (ID: {$a->courseid}) with workflow ($a->ocworkflow) of opencast instance (ID: {$a->ocinstanceid}).';
$string['errorexception_subj'] = 'Life Cycle Opencast step Fatal error';
$string['errorfailedworkflow_body'] = 'The workflow ({$a->ocworkflow}) of opencast instance (ID: {$a->ocinstanceid}) failed to start on event "{$a->videotitle}" (ID: {$a->videoidentifier}) in {$a->coursefullname} (ID: {$a->courseid})';
$string['errorfailedworkflow_subj'] = 'Life Cycle Opencast step workflow failed';
$string['errorworkflownotexists_body'] = 'The workflow ({$a->ocworkflow}) was not found in opencast instance (ID: {$a->ocinstanceid}) in course in {$a->coursefullname} (ID: {$a->courseid}).';
$string['errorworkflownotexists_subj'] = 'Life Cycle Opencast step workflow was not found';
$string['mform_generalsettingsheading'] = 'General settings';
$string['mform_ocinstanceheading'] = 'Opencast instance: {$a->name}';
$string['mform_ocisdelete'] = 'Enable deletion process';
$string['mform_ocisdelete_help'] = 'When enabled, all related procedures for series and videos deletion will be processed and applied.';
$string['mform_ocnotifyadmin'] = 'Enable notify admin';
$string['mform_ocnotifyadmin_help'] = 'When enabled, admins will be notified in case something does not work as expected i.e failures and errors.';
$string['mform_ocremoveseriesmapping'] = 'Remove series mapping when deleting';
$string['mform_ocremoveseriesmapping_help'] = 'When enabled and the step is for deleting videos in the course, the course-series mapping will also be removed in case all series videos are deleted or the series is unlinked.';
$string['mform_octrace'] = 'Enable trace';
$string['mform_octrace_help'] = 'When enabled, more detailed logs will be generated.';
$string['mform_ocworkflow'] = 'Opencast workflow';
$string['mform_ocworkflow_help'] = 'The opencast workflow to perform on the event of a series eligibale for the step.';
$string['mtrace_error_cannot_remove_acl'] = 'ERROR: Unable to remove course ACLs from the series and its events properly.';
$string['mtrace_error_get_series_videos'] = 'ERROR: There was an error retrieving the series videos, the series will be skipped.';
$string['mtrace_error_remove_series_mapping'] = 'ERROR: Unable to remove series mapping.';
$string['mtrace_error_workflow_cannot_start'] = "ERROR: The workflow couldn't be started properly for this video.";
$string['mtrace_error_workflow_notexist'] = 'ERROR: The workflow ({$a->ocworkflow}) does not exist.';
$string['mtrace_finish_process_course'] = 'Finished processing the videos in course (ID: {$a->courseid})';
$string['mtrace_finish_process_deletion'] = 'Finished deletion process for series and videos.';
$string['mtrace_finish_process_ocinstance'] = 'Finished processing the videos in Opencast instance (ID: {$a->instanceid})';
$string['mtrace_finish_process_regular'] = 'Finished regular process for series and videos.';
$string['mtrace_finish_process_series'] = 'Finished processing the videos in Opencast series (ID: {$a->series})';
$string['mtrace_finish_process_unlinking_series_course'] = 'Finished unlinking the Opencast series (ID: {$a->series}) from the course.';
$string['mtrace_notice_no_remove_mapping'] = 'NOTICE: Since there were unprocessed videos in the series (ID: {$a->series}), the series mapping was not removed!';
$string['mtrace_notice_rate_limiter'] = 'NOTICE: As the Opencast rate limiter is enabled in the step settings, processing the videos in this course will be stopped now and will continue in the next run of this scheduled task.';
$string['mtrace_notice_video_is_processing'] = 'NOTICE: The video is already being processed currently, the video will be skipped.';
$string['mtrace_start_process_course'] = 'Start processing the videos in course "{$a->coursefullname}" (ID: {$a->courseid})';
$string['mtrace_start_process_deletion'] = 'Start deletion process for series and videos.';
$string['mtrace_start_process_ocinstance'] = 'Start processing the videos in Opencast instance (ID: {$a->instanceid})';
$string['mtrace_start_process_regular'] = 'Start regular process for series and videos.';
$string['mtrace_start_process_series'] = 'Start processing the videos in Opencast series (ID: {$a->series})';
$string['mtrace_start_process_video'] = 'Start processing the Opencast video (ID: {$a->identifier})';
$string['mtrace_success_delete_workflow_started'] = 'SUCCESS: The workflow was started for this video. Deletion process is registered in Opencast delete jobs cron.';
$string['mtrace_success_series_course_unlinked'] = 'SUCCESS: Series has been unlinked from course.';
$string['mtrace_success_workflow_started'] = 'SUCCESS: The workflow was started for this video.';
$string['notifycourseprocessed_body'] = 'The course "{$a->coursefullname}" (ID: {$a->courseid}) was successfully processed with workflow ({$a->ocworkflow}).';
$string['notifycourseprocessed_subj'] = 'Life Cycle Opencast step course processed successfully';
$string['plugindescription'] = 'Manages what to do with Opencast videos when the step conditions are met!';
$string['pluginname'] = 'Opencast step';
$string['privacy:metadata'] = 'The "Opencast step" subplugin of the admin tool "Course Life Cycle" does not store any personal data.';
$string['setting_ratelimiter'] = 'Opencast rate limiter';
$string['setting_ratelimiter_desc'] = 'This option makes the step to only be performed once for an opencast event. Disabling this option processes all events of a series in one go.';
$string['setting_workflowtags'] = 'Opencast workflow tags';
$string['setting_workflowtags_desc'] = 'A comma separated list of workflow tags, to get the related workflows from Opencast, which then could be selected for each step to be run agains existing events.<br> NOTE: if empty \'delete\' tag will be used.';
