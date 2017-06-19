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
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

use WebDriver\Exception;

defined('MOODLE_INTERNAL') || die();

class backup_manager {

    /**
     * Creates a course backup in a specific cleanup courses backup folder
     * @param int $courseid id of the course the backup should be created for.
     * @return bool tells if the backup was completed successfully.
     */
    public static function create_course_backup($courseid) {
        global $CFG;
        try {
            // Build filename.
            $archivefile = date("Y-m-d") . "-ID-{$courseid}.mbz";

            // Path of backup folder.
            $path = $CFG->dataroot . '/cleanupcourses_backups';
            // If the path doesn't exist, make it so!
            if (!is_dir($path)) {
                umask(0000);
                // Create the directory for Backups.
                if (!mkdir($path, $CFG->directorypermissions, true)) {
                    throw new \moodle_exception(get_string('errorbackuppath', 'tool_cleanupcourses'));
                }
            }
            // Perform Backup.
            $bc = new \backup_controller(\backup::TYPE_1COURSE, $courseid, \backup::FORMAT_MOODLE,
                \backup::INTERACTIVE_NO, \backup::MODE_AUTOMATED, get_admin()->id);
            $bc->execute_plan();  // Execute backup.
            $results = $bc->get_results(); // Get the file information needed.
            $file = $results['backup_destination'];
            if (!empty($file)) {
                $file->copy_content_to($path . '/' . $archivefile);
            }
            $bc->destroy();
            unset($bc);
            return true;
        } catch (\moodle_exception $e) {
            debugging('There was a problem during backup!');
            debugging($e->getMessage());
            return false;
        }
    }

}
