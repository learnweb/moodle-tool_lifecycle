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
 * Manager to create & restore backups for courses
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

defined('MOODLE_INTERNAL') || die();

// Get the necessary files to perform backup and restore.
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Manager to create & restore backups for courses
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_manager {

    /**
     * Creates a course backup in a specific life cycle backup folder
     * @param int $courseid id of the course the backup should be created for.
     * @param int $stepid id of the step instance
     * @return bool tells if the backup was completed successfully.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function create_course_backup($courseid, $stepid) {
        global $CFG, $DB;
        $course = get_course($courseid);
        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->fullname = $course->fullname;
        $record->shortname = $course->shortname;
        $record->step = $stepid;
        $recordid = $DB->insert_record('tool_lifecycle_backups', $record, true);
        $record->id = $recordid;

        // Build filename.
        $archivefile = date("Y-m-d") . "-ID-{$recordid}-COURSE-{$courseid}.mbz";

        mtrace('=== LIFECYCLE COURSE BACKUP START ===');
        mtrace('Course ID: ' . $courseid);
        mtrace('Record ID: ' . $recordid);
        mtrace('Archive filename: ' . $archivefile);

        // Path of backup folder.
        $path = get_config('tool_lifecycle', 'backup_path');
        mtrace('Backup path: ' . $path);

        // Check backup path.
        if (!is_dir($path)) {
            mtrace('Backup path does not exist. Creating directory...');

            umask(0000);

            // Create the directory for Backups.
            if (!mkdir($path, $CFG->directorypermissions, true)) {
                mtrace('ERROR: Could not create backup directory.');
                throw new \moodle_exception(get_string('errorbackuppath', 'tool_lifecycle'));
            }

            mtrace('Backup directory successfully created.');
        } else {
            mtrace('Backup directory already exists.');
        }

        // Check whether directory is writable.
        if (!is_writable($path)) {
            mtrace('ERROR: Backup directory is not writable: ' . $path);
            throw new \moodle_exception(get_string('errorbackuppath', 'tool_lifecycle'));
        }

        mtrace('Backup directory is writable.');

        // Create backup controller.
        mtrace('Creating backup controller...');

        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $courseid,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_AUTOMATED,
            get_admin()->id
        );

        mtrace('Backup controller created.');

        // Get initial controller status.
        mtrace('Initial backup controller status: ' . $bc->get_status());

        // Execute backup plan.
        mtrace('Starting backup plan...');

        try {
            $bc->execute_plan();
            mtrace('Backup plan finished successfully.');
        } catch (\Throwable $e) {
            mtrace('!!! BACKUP FAILED DURING execute_plan() !!!');
            mtrace('Exception class: ' . get_class($e));
            mtrace('Exception message: ' . $e->getMessage());
            mtrace('Exception code: ' . $e->getCode());
            mtrace('Exception file: ' . $e->getFile());
            mtrace('Exception line: ' . $e->getLine());
            mtrace('Stack trace:');
            mtrace($e->getTraceAsString());

            $bc->destroy();
            unset($bc);

            throw $e;
        }

        // Get controller status after execution.
        mtrace('Backup controller status after execute_plan(): ' . $bc->get_status());

        // Get backup results.
        mtrace('Getting backup results...');

        try {
            $results = $bc->get_results();
            mtrace('Backup results successfully retrieved.');
        } catch (\Throwable $e) {
            mtrace('!!! ERROR WHILE GETTING BACKUP RESULTS !!!');
            mtrace('Exception class: ' . get_class($e));
            mtrace('Exception message: ' . $e->getMessage());
            mtrace('Exception code: ' . $e->getCode());
            mtrace('Exception file: ' . $e->getFile());
            mtrace('Exception line: ' . $e->getLine());
            mtrace('Stack trace:');
            mtrace($e->getTraceAsString());

            $bc->destroy();
            unset($bc);

            throw $e;
        }

        // Log result information.
        mtrace('Backup result keys: ' . implode(', ', array_keys($results)));

        if (isset($results['backup_destination'])) {
            mtrace('backup_destination is present.');

            $file = $results['backup_destination'];

            if ($file instanceof \stored_file) {
                mtrace('backup_destination is a stored_file.');
                mtrace('Filename: ' . $file->get_filename());
                mtrace('Filesize: ' . $file->get_filesize());
                mtrace('Content hash: ' . $file->get_contenthash());
                mtrace('Component: ' . $file->get_component());
                mtrace('File area: ' . $file->get_filearea());
                mtrace('Item ID: ' . $file->get_itemid());
            } else {
                mtrace('WARNING: backup_destination is not a stored_file.');
                mtrace('Actual type: ' . get_debug_type($file));
            }
        } else {
            mtrace('!!! backup_destination is NOT present in backup results !!!');
            mtrace('Complete backup results:');
            var_dump($results);

            $bc->destroy();
            unset($bc);

            throw new \moodle_exception(get_string('errornobackup', 'tool_lifecycle'));
        }

        // Copy backup file to target directory.
        $targetfile = $path . DIRECTORY_SEPARATOR . $archivefile;

        mtrace('Target backup file: ' . $targetfile);
        mtrace('Copying backup file...');

        try {
            $file->copy_content_to($targetfile);
            mtrace('Backup file copied successfully.');
        } catch (\Throwable $e) {
            mtrace('!!! ERROR WHILE COPYING BACKUP FILE !!!');
            mtrace('Exception class: ' . get_class($e));
            mtrace('Exception message: ' . $e->getMessage());
            mtrace('Exception file: ' . $e->getFile());
            mtrace('Exception line: ' . $e->getLine());
            mtrace('Stack trace:');
            mtrace($e->getTraceAsString());

            $bc->destroy();
            unset($bc);

            throw $e;
        }

        // Check resulting file.
        mtrace('Checking whether backup file was created...');

        if (file_exists($targetfile)) {
            mtrace('Backup file exists.');
            mtrace('Backup file size: ' . filesize($targetfile) . ' bytes.');
        } else {
            mtrace('!!! ERROR: Backup file does NOT exist after copy_content_to() !!!');

            $bc->destroy();
            unset($bc);

            throw new \moodle_exception(get_string('errornobackup', 'tool_lifecycle'));
        }

        // Delete temporary stored file.
        mtrace('Deleting temporary stored backup file...');

        try {
            $file->delete();
            mtrace('Temporary stored backup file deleted.');
        } catch (\Throwable $e) {
            mtrace('WARNING: Could not delete temporary stored backup file.');
            mtrace('Exception: ' . $e->getMessage());
        }

        // Destroy backup controller.
        mtrace('Destroying backup controller...');

        $bc->destroy();
        unset($bc);

        mtrace('Backup controller destroyed.');

        mtrace('=== LIFECYCLE COURSE BACKUP END ===');

        $record->backupfile = $archivefile;
        $record->backupcreated = time();
        $DB->update_record('tool_lifecycle_backups', $record, true);

        return true;
    }

    /**
     * Restores a course backup via a backupid
     * The function copies the backup file from the lifecycle backup folder to a temporary folder.
     * It then redirects to the backup/restore.php, which leads the user through the interactive restore process.
     * @param int $backupid id of backup entry.
     * @throws \moodle_exception
     * @throws \restore_controller_exception
     */
    public static function restore_course_backup($backupid) {
        global $DB, $CFG;
        $backuprecord = $DB->get_record('tool_lifecycle_backups', ['id' => $backupid]);

        // Check if backup tmp dir exists.
        $backuptmpdir = $CFG->tempdir . DIRECTORY_SEPARATOR . 'backup';
        if (!check_dir_exists($backuptmpdir, true, true)) {
            throw new \restore_controller_exception('cannot_create_backup_temp_dir');
        }

        // Create the file location in the backup temp.
        $targetfilename = \restore_controller::get_tempdir_name($backuprecord->courseid, get_admin()->id);
        $target = $backuptmpdir . DIRECTORY_SEPARATOR . $targetfilename;
        // Create the location of the actual backup file.
        $source = get_config('tool_lifecycle', 'backup_path') . DIRECTORY_SEPARATOR . $backuprecord->backupfile;
        // Check if the backup file exists.
        if (!file_exists($source)) {
            throw new \moodle_exception('errorbackupfiledoesnotexist', 'tool_lifecycle', $source);
        }

        // Copy the file to the backup temp dir.
        copy($source, $target);

        $context = \context_system::instance();
        $restoreurl = new \moodle_url('/backup/restore.php',
            [
                'contextid' => $context->id,
                'filename' => $targetfilename,
            ]
        );
        redirect($restoreurl);

    }

    /**
     * Deletes a lifecycle course backup
     * @param int $backupid id of the course backup should be created to delete.
     * @return bool tells if the deletion was completed successfully.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function delete_course_backup($backupid) {
        global $DB;

        // Get the filename.
        $filename = $DB->get_field('tool_lifecycle_backups', 'backupfile', ['id' => $backupid]);

        // Path of backup folder.
        $path = get_config('tool_lifecycle', 'backup_path');

        $archivefile = $path . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($archivefile)) {
            unlink($archivefile);
        }

        $DB->delete_records('tool_lifecycle_backups', ['id' => $backupid]);

        return true;
    }
}
