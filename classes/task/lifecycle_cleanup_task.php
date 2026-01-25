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
 * Scheduled task for cleanup past delays
 *
 * @package tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\task;

use stdClass;

/**
 * Scheduled task for cleanup past delays
 *
 * @package tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lifecycle_cleanup_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('lifecycle_cleanup_task', 'tool_lifecycle');
    }

    /**
     * Do the job.
     */
    public function execute() {
        global $DB;
        $twomonthago = time() - 60 * 24 * 60 * 60;
        $oneyearago = time() - 365 * 24 * 60 * 60;
        $DB->delete_records_select('tool_lifecycle_delayed', 'delayeduntil <= :time', ['time' => $twomonthago]);
        $DB->delete_records_select('tool_lifecycle_delayed_workf', 'delayeduntil <= :time', ['time' => $twomonthago]);
        $DB->delete_records_select('lifecyclestep_email_notified', 'timemailsent <= :time', ['time' => $oneyearago]);
        if ($days = get_config('tool_lifecycle', 'deletebackupsafterdays') ?? 0) {
            $timestamp = time() - $days * 24 * 60 * 60;
            $this->delete_course_backups($timestamp);
        }
    }

    /**
     * Delete all lifecycle course backups older than timestamp.
     * @param int $timestamp date in milliseconds
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function delete_course_backups($timestamp) {
        global $DB;

        // Get all course backups older than timestamp from db.
        $sql = "SELECT id, backupfile
                FROM {tool_lifecycle_backups}
                WHERE backupcreated < :timestmp";
        $records = $DB->get_records_sql($sql, ['timestmp' => $timestamp]);

        // Get path of backup folder.
        $path = get_config('tool_lifecycle', 'backup_path');
        // Check if backup path exists.
        if (is_dir($path)) {
            $result = new stdClass;
            foreach ($records as $record) {
                $deletedfile = false;
                $deletedrecord = false;
                // If file exists.
                if (is_file("$path/$record->backupfile")) {
                    // Set permissions to 777 of course backup file.
                    chmod("$path/$record->backupfile", 0777);
                    // Clears file status cache.
                    clearstatcache();
                    // Delete course backup file first.
                    $deletedfile = unlink("$path/$record->backupfile");
                    // If deletion of course backup file was unsuccessful.
                    if (!$deletedfile) {
                        // Delete course backup file with system command.
                        exec("rm -f " . escapeshellarg("$path/$record->backupfile"),
                            $output, $resultcode);
                        if ($resultcode == 0) {
                            $deletedfile = true;
                        }
                    }
                    // If file was deleted, delete record too.
                    if ($deletedfile) {
                        $deletedrecord = $DB->delete_records('tool_lifecycle_backups', ['id' => $record->id]);
                    }
                } else { // If file not exists, delete just record.
                    $deletedrecord = $DB->delete_records('tool_lifecycle_backups', ['id' => $record->id]);
                }
                $result->filedeleted = $deletedfile ? get_string('yes') : get_string('no');
                $result->recorddeleted = $deletedrecord ? get_string('yes') : get_string('no');
                $result->backupfile = $path.'/'.$record->backupfile;
                $result->recordid = $record->id;
                mtrace(get_string('mtracebackupdeleted', 'lifecyclestep_deletebackup', $result));
            }
        }
    }
}
