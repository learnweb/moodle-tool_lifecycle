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
 * Step subplugin to delete a course using Catalyst Maintenance batch deletion logic.
 *
 * @package    lifecyclestep_uclcontextdelete
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\step;

use tool_lifecycle\local\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Lifecycle step: delete course via tool_catmaintenance deleter.
 */
class uclcontextdelete extends base_step {

    /**
     * Process a single course.
     *
     * @param \stdClass $course
     * @param int $stepid
     * @return step_response
     */
    public function process_course($course, $stepid) {
        global $CFG;

        // Load Catalyst Maintenance deleter.
        require_once($CFG->dirroot . '/admin/tool/catmaintenance/classes/local/deleter.php');

        // Delegate deletion to Catalyst's logic.
        \tool_catmaintenance\local\deleter::delete_course($course->id);

        return step_response::success();
    }

    /**
     * Human-readable step name.
     *
     * @return string
     */
    public function get_subpluginname() {
        return 'uclcontextdelete';
    }
}
