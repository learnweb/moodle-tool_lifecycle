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
 * Step subplugin to delete a backup.
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Step subplugin to delete a backup.
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deletebackup extends libbase {

    /** @var int $numberofdeletions Deletions done so far in this php call. */
    private static $numberofdeletions = 0;

    /**
     * Processes the course and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        // Get settings.
        $settings = settings_manager::get_settings($instanceid, settings_type::STEP);

        // Call $this->delete_backups(...) only once.
        if (self::$numberofdeletions < 1) {
            // Delete course backup files older than date.
            $results = $this->delete_backups($settings['deletebackupsolderthan'], $settings['maximumdeletionspercron']);
            foreach ($results as $result) {
                mtrace("Record id: " . $result['recordid'] . " (deleted: " . $result['recorddeleted'] . "), file: "
                    . $result['backupfile'] . " (deleted: " . $result['filedeleted'] . ")");
            }
        }

        // Raise deletion counter.
        self::$numberofdeletions++;

        return step_response::proceed();
    }

    /**
     * Processes the course in status waiting and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        return $this->process_course($processid, $instanceid, $course);
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'deletebackup';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            // Add instance for maximum deletions (per cron execution).
            new instance_setting('maximumdeletionspercron', PARAM_INT, true),
            // Add instance for deleting all backups that are older than.
            new instance_setting('deletebackupsolderthan', PARAM_INT, true),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        // Add input field for maximum deletions (per cron execution).
        $mform->addElement('text', 'maximumdeletionspercron', get_string('maximumdeletionspercron', 'lifecyclestep_deletebackup'));
        $mform->setType('maximumdeletionspercron', PARAM_INT);
        $mform->setDefault('maximumdeletionspercron', 10);

        // Add input field for deleting all backups that are older than X days.
        $mform->addElement('text', 'deletebackupsolderthan', get_string('deletebackupsolderthan', 'lifecyclestep_deletebackup'));
        $mform->setType('deletebackupsolderthan', PARAM_INT);
        $mform->setDefault('deletebackupsolderthan', 365);
    }

    // ... ################################################ Additional Methods ################################################ ...

    /**
     * Test trigger
     */
    public function test() {
        echo("<br><br><br>");
        echo("<br>Test step: <strong>deletebackup</strong> (for <u>tool lifecycle</u>)<br>");
        echo("<hr><br><strong>Show course backup records:</strong>");
        $results = $this->delete_backups((time() - 365 * 24 * 60 * 60), 10);
        echo("<br>Course backup records: ".count($results)."<br>");
        foreach ($results as $result) {
            echo("<br>Record id: ".$result['recordid']." (deleted: ".$result['recorddeleted']."), file: "
                .$result['backupfile']." (deleted: ".$result['filedeleted'].")");
        }
        echo("<hr><br><strong>Show course backup files:</strong>");
        $files = $this->get_backup_files();
        echo("<br>Course backup files: ".count($files)."<br>");
        foreach ($files as $file) {
            echo("<br>".$file['file']."&nbsp;&nbsp;(parsed: ".$file['filedate'].", created: "
                .$file['creationdate'].", modified: ".$file['modificationdate'].")");
        }
    }

    /**
     * Delete course backup files older than date.
     * @param string $timestamp of "older than date".
     * @param int $maxdeletions per run.
     */
    public function delete_backups($timestamp = null, $maxdeletions = 10) {
        global $DB;

        // If date is null, use now - 1 year.
        if (is_null($timestamp) || empty($timestamp) || !is_int($timestamp) || $timestamp < -1) {
            $timestamp = time() - 365 * 24 * 60 * 60;
            // Info: Needed because of the switch from date_selector (= unix timestamp) to text (int value of days).
        } else {
            $timestamp = time() - $timestamp * 24 * 60 * 60;
        }

        // Get all course backups older than timestamp from db.
        $sql = "SELECT id, backupfile FROM {tool_lifecycle_backups} WHERE backupcreated < ?";
        $records = $DB->get_records_sql($sql, [$timestamp]);

        // Get path of backup folder.
        $path = get_config('tool_lifecycle', 'backup_path');
        // Define file extension of course backups.
        $extension = "mbz";
        // Define result array.
        $results = [];
        $counter = 0;

        // Check if backup path exists.
        if (is_dir($path)) {
            foreach ($records as $record) {
                $deletedfile = -1;
                $deletedrecord = -1;

                if ($counter < $maxdeletions) {
                    // If file exists.
                    if (is_file("$path/$record->backupfile")) {
                        // If file extension = mbz.
                        if (pathinfo($record->backupfile, PATHINFO_EXTENSION) === $extension
                            // If parsed time of file name <= timestamp.
                            && preg_match('/(\d{4}-\d{2}-\d{2})/', $record->backupfile, $matches)
                            && \DateTime::createFromFormat('Y-m-d', $matches[1])->getTimestamp() < $timestamp
                        ) {
                            // Set permissions to 777 of course backup file.
                            chmod("$path/$record->backupfile", 0777);
                            // Clears file status cache.
                            clearstatcache();
                            // Delete course backup file first.
                            $deletedfile = unlink("$path/$record->backupfile");
                            // If deletion of course backup file was unsuccessful.
                            if (!$deletedfile) {
                                // Delete course backup file with system command.
                                exec("rm -f " . escapeshellarg("$path/$record->backupfile"), $output, $resultcode);
                                if ($resultcode == 0) {
                                    $deletedfile = 1;
                                }
                            }

                            // If file was deleted, delete record too.
                            if ($deletedfile) {
                                $deletedrecord = $DB->delete_records('tool_lifecycle_backups',
                                        ['backupfile' => $record->backupfile]);
                            }
                            $counter++;
                        }
                        // If file not exists, delete just record.
                    } else {
                        $deletedrecord = $DB->delete_records('tool_lifecycle_backups', ['backupfile' => $record->backupfile]);
                    }

                    $results[] = ['recordid' => $record->id, 'backupfile' => $path.'/'.$record->backupfile,
                            'recorddeleted' => $deletedrecord, 'filedeleted' => $deletedfile];
                }
            }
        } else {
            echo("<br>Backup path: $path does not exist!");
        }

        return $results;
    }

    /**
     * Get course backup files (of backup dir).
     */
    public function get_backup_files() {
        // Get path of backup folder.
        $path = get_config('tool_lifecycle', 'backup_path');
        // Define file extension of course backups.
        $extension = "mbz";
        $results = [];

        // Check if backup path exists.
        if (is_dir($path)) {
            // Get files of backup folder.
            $files = array_diff(scandir($path), ['.', '..']);
            // Filter files by extension.
            $files = array_filter($files, function($file) use ($path, $extension) {
                return is_file("$path/$file") && pathinfo($file, PATHINFO_EXTENSION) === $extension;
            });

            // If files exist.
            if (!empty($files)) {
                // For each file.
                foreach ($files as $file) {
                    // Parse creation date of file name.
                    $filedate = false;
                    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
                        // Create date of first match and reformat it.
                        $filedate = \DateTime::createFromFormat('Y-m-d', $matches[1])->format('d.m.Y');
                    }
                    // Get creation date of file.
                    $creationdate = date("d.m.Y", filectime("$path/$file"));
                    // Get modification date of file.
                    $modificationdate = date("d.m.Y", filemtime("$path/$file"));

                    $results[] = ['file' => $file, 'filedate' => $filedate, 'creationdate' => $creationdate,
                            'modificationdate' => $modificationdate];
                }
            } else {
                echo("<br>No files found in the backup directory");
            }
        } else {
            echo("<br>Backup path: $path does not exist!");
        }

        return $results;
    }
}
