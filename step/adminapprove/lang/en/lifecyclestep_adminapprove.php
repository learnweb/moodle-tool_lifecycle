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
 * Lang Strings for Admin Approve Step
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adminapprovals'] = 'Admin Approvals';
$string['allstepapprovalsproceed'] = 'All pending processes of step "{$a->step}" of the workflow "{$a->workflow}" have been proceeded.';
$string['allstepapprovalsrollback'] = 'All pending processes of step "{$a->step}" of the workflow "{$a->workflow}" have been rolled back.';
$string['amount_courses'] = 'Remaining waiting courses';
$string['bulkactions'] = 'Bulk actions';
$string['cachedef_mformdata'] = 'Caches the mform data';
$string['courseid'] = 'Course id';
$string['courses_waiting'] = 'These courses are currently waiting for approval in the "{$a->step}" Step in the "{$a->workflow}" Workflow.';
$string['emailcontent'] = 'There are {$a->amount} new courses waiting for confirmation. Please visit {$a->url}.';
$string['emailcontenthtml'] = 'There are {$a->amount} new courses waiting for confirmation. Please visit <a href="{$a->url}">this link</a>.';
$string['emailsubject'] = 'Lifecycle: There are new courses waiting for confirmation.';
$string['manage-adminapprove'] = 'Manage Admin Approve Steps';
$string['no_courses_waiting'] = 'There are currently no courses waiting for approval in the "{$a->step}" Step in the "{$a->workflow}" Workflow.';
$string['nostepstodisplay'] = 'There are currently no courses waiting for interaction in any Admin Approve step.';
$string['nothingtodisplay'] = 'There are no courses waiting for approval matching your current filters.';
$string['only_number'] = 'Only numeric characters allowed!';
$string['plugindescription'] = 'Demands an approval of any sysadmin before coninuing the workflow.';
$string['pluginname'] = 'Admin approve step';
$string['privacy:metadata'] = 'This subplugin does not store any personal data.';
$string['proceed'] = 'Proceed';
$string['proceedbuttonlabel'] = 'Label of the proceed button';
$string['proceedbuttonlabel_help'] = 'Option to customize the label of the button \'Proceed\'. Leave it empty if you are ok with the default value.';
$string['rollback'] = 'Rollback';
$string['rollbackbuttonlabel'] = 'Label of the rollback button';
$string['rollbackbuttonlabel_help'] = 'Option to customize the label of the button \'Rollback\'. Leave it empty if you are ok with the default value.';
$string['selectedstepapprovalsproceed'] = '{$a} selected approvals have been promoted.';
$string['selectedstepapprovalsrollback'] = '{$a} selected approvals have been rolled back.';
$string['statusmessage'] = 'Status message';
$string['statusmessage_help'] = 'Status message, which is displayed to a teacher, if a process of a course is at this admin approve step.';
$string['statusmessagedefault'] = 'In Admin Approve step';
$string['tools'] = 'Tools';
$string['withselectedcourses'] = 'With selected courses... ';
$string['workflow'] = 'Workflow';
