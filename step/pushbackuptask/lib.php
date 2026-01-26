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

namespace tool_lifecycle\step;

use lifecyclestep_pushbackuptask\task\course_backup_task;
use stdClass;
use tool_lifecycle\local\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Class for processing courses.
 */
class pushbackuptask extends libbase {
    /**
     * Processes the course and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param stdClass $course to be processed.
     * @return step_response
     */
    public function process_course($processid, $instanceid, $course) {
        $asynctask = new course_backup_task();
        $asynctask->set_custom_data(['courseid' => $course->id]);
        \core\task\manager::queue_adhoc_task($asynctask);
        return step_response::proceed();
    }

    /**
     * Simply call the process_course since it handles everything necessary for this plugin.
     * @param int $processid
     * @param int $instanceid
     * @param stdClass $course
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        return $this->process_course($processid, $instanceid, $course);
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'pushbackuptask';
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'i/emojicategoryobjects';
    }
}
