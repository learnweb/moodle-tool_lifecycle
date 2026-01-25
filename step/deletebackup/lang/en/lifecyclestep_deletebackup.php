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
 * Lang strings for delete backup step
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['backupdeleted'] = 'deletion time';
$string['backupdeletionlogtable'] = 'Backup deletion log of step {$a}';
$string['deletebackupsolderthan'] = 'Delete all backups that are older than x days';
$string['files'] = 'Backupfiles deleted';
$string['log'] = 'Write log entries';
$string['log_help'] = 'If checked a log entry for every file deletion is written. These log entries can be seen in the workflow overview page.';
$string['maximumdeletionspercron'] = 'Maximum number of backups deleted per cron job';
$string['mtracebackupdeleted'] = 'Backup id {$a->recordid}. DB-record succesfully deleted: {$a->recorddeleted}. File {$a->backupfile} successfully deleted: {$a->filedeleted}';
$string['plugindescription'] = 'Delete backup step';
$string['pluginname'] = 'Delete backup step';
$string['privacy:metadata'] = 'This subplugin does not store any personal data.';



