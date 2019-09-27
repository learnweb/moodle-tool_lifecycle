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
 * Trigger subplugin to exclude delayed courses.
 *
 * @package lifecycletrigger_delayedcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\local\manager\delayed_courses_manager;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a life cycle trigger subplugin
 * @package lifecycletrigger_delayedcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delayedcourses extends base_automatic {


    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param object $course Course to be processed.
     * @param int $triggerid Id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        return trigger_response::exclude();
    }

    /**
     * Return sql which excludes delayed courses.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     */
    public function get_course_recordset_where($triggerid) {
        return delayed_courses_manager::get_course_delayed_wheresql();
    }


    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'delayedcourses';
    }

    /**
     * Has only one instance and results in a preset workflow.
     * @return bool
     */
    public function has_multiple_instances() {
        return false;
    }

}
