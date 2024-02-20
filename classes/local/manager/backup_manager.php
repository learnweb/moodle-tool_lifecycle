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
     * @return bool tells if the backup was completed successfully.
     */
    public static function create_course_backup($courseid) {
        global $CFG, $DB;
        $course = get_course($courseid);
        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->fullname = $course->fullname;
        $record->shortname = $course->shortname;
        $recordid = $DB->insert_record('tool_lifecycle_backups', $record, true);
        $record->id = $recordid;

        // Build filename.
        $archivefile = date("Y-m-d") . "-ID-{$recordid}-COURSE-{$courseid}.mbz";

        // Path of backup folder.
        $path = get_config('tool_lifecycle', 'backup_path');
        // If the path doesn't exist, make it so!
        if (!is_dir($path)) {
            umask(0000);
            // Create the directory for Backups.
            if (!mkdir($path, $CFG->directorypermissions, true)) {
                throw new \moodle_exception(get_string('errorbackuppath', 'tool_lifecycle'));
            }
        }
        // Perform Backup.
        $bc = new \backup_controller(\backup::TYPE_1COURSE, $courseid, \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO, \backup::MODE_AUTOMATED, get_admin()->id);
        $bc->execute_plan();  // Execute backup.
        $results = $bc->get_results(); // Get the file information needed.
        /* @var $file \stored_file instance of the backup file*/
        $file = $results['backup_destination'];
        if (!empty($file)) {
            $file->copy_content_to($path . DIRECTORY_SEPARATOR . $archivefile);
            $file->delete();
        }
        $bc->destroy();
        unset($bc);

        // First check if the file was created.
        if (!file_exists($path . DIRECTORY_SEPARATOR . $archivefile)) {
            throw new \moodle_exception(get_string('errornobackup', 'tool_lifecycle'));
        }

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
}
