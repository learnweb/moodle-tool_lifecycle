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
 * Life Cycle Admin push backup task step
 *
 * @package    lifecyclestep_pushbackuptask
 * @copyright  2024 Johannes Burk (HTW Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_pushbackuptask\task;

use tool_lifecycle\local\manager\backup_manager;

/**
 * Task for adhoc backups of courses.
 */
class course_backup_task extends \core\task\adhoc_task {

    /**
     * Run the adhoc task and preform the backup.
     */
    public function execute() {
        global $CFG, $DB;

        if (!empty($CFG->custom_no_tool_lifecycle_adhoc_backups)) {
            mtrace('Tool lifecycle adhoc backups are disabled via config.php ($CFG->custom_no_tool_lifecycle_adhoc_backups)');
            return;
        }

        $lockfactory = \core\lock\lock_config::get_lock_factory('course_backup_adhoc');
        $courseid = $this->get_custom_data()->courseid;

        try {
            $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        } catch (\moodle_exception $e) {
            mtrace('Invalid course id: ' . $courseid . ', task aborted.');
            return;
        }

        if (!$lock = $lockfactory->get_lock('lifecyclestep_pushbackuptask_' . $courseid, 10)) {
            mtrace('Backup adhoc task for: ' . $course->fullname . 'is already running.');
            return;
        } else {
            mtrace('Processing backup for course: ' . $course->fullname);
        }

        try {
            backup_manager::create_course_backup($courseid);
        } catch (Exception $e) {
            mtrace('Backup for course: ' . $course->fullname . ' encounters an error.');
            mtrace('Exception: ' . $e->getMessage());
            mtrace('Debug: ' . $e->debuginfo);
        } finally {
            // Everything is finished release lock.
            $lock->release();
            mtrace('Backup for course: ' . $course->fullname . ' completed.');
        }
    }
}
