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
  * Step subplugin to freeze a course context using UCL block_lifecycle manager.
  *
  * @package    lifecyclestep_uclcontextfreeze
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

namespace tool_lifecycle\step;

use stdClass;
use tool_lifecycle\local\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Step subplugin to freeze a course context using UCL block_lifecycle.
 *
 * @package    lifecyclestep_uclcontextfreeze
 */
class uclcontextfreeze extends libbase {

    /**
     * Processes the course and returns a response.
     *
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param stdClass $course to be processed.
     * @return step_response
     */
    public function process_course($processid, $instanceid, $course) {

        if (!class_exists('\block_lifecycle\manager')) {
            return step_response::rollback();
        }

        try {
            \block_lifecycle\manager::freeze_course((int)$course->id);
        } catch (\Exception $e) {
            return step_response::rollback();
        }

        return step_response::proceed();
    }

    /**
     * Processes the course in status waiting and returns a response.
     *
     * @param int $processid
     * @param int $instanceid
     * @param stdClass $course
     * @return step_response
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        return $this->process_course($processid, $instanceid, $course);
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     *
     * @return string
     */
    public function get_subpluginname() {
        return 'uclcontextfreeze';
    }
}
