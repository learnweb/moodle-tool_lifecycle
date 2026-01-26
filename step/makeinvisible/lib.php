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
 * lib for Make Invisible Step
 *
 * @package    lifecyclestep_makeinvisible
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\step;

use stdClass;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\local\response\step_response;
use function update_course;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * lib for Make Invisible Step
 *
 * @package    lifecyclestep_makeinvisible
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class makeinvisible extends libbase {

    /**
     * Stores old visibility and hides course
     *
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param stdClass $course to be processed.
     * @return step_response
     */
    public function process_course($processid, $instanceid, $course) {
        process_data_manager::set_process_data($processid, $instanceid, 'visible', $course->visible);
        process_data_manager::set_process_data($processid, $instanceid, 'visibleold', $course->visibleold);
        course_change_visibility($course->id, false);
        return step_response::proceed();
    }

    /**
     * Roll back the changes.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param stdClass $course to be rolled back.
     * @throws \moodle_exception
     */
    public function rollback_course($processid, $instanceid, $course) {
        global $CFG;
        // If visibility changed, do nothing.
        if ($course->visibleold) {
            return;
        }

        require_once($CFG->dirroot . '/course/lib.php');
        $cat = \core_course_category::get($course->category, MUST_EXIST, true);
        $record = new stdClass();
        $record->id = $course->id;
        $record->visibleold = (bool) process_data_manager::get_process_data($processid, $instanceid, 'visibleold');
        $record->visible = $record->visibleold && (bool)$cat->visible;
        update_course($record);
    }

    /**
     * The technical subplugin name.
     * @return string
     */
    public function get_subpluginname() {
        return 'makeinvisible';
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'i/show';
    }
}
