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
 * Trigger subplugin that excludes the sitecourse.
 *
 * @package lifecycletrigger_sitecourse
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use tool_lifecycle\local\response\trigger_response;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a life cycle trigger subplugin
 * @package lifecycletrigger_sitecourse
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sitecourse extends base_automatic {

    /**
     * Returns triggertype of trigger: trigger, triggertime or exclude.
     * @param object $course DEPRECATED
     * @param int $triggerid DEPRECATED
     * @return trigger_response
     */
    public function check_course($course = null, $triggerid = null) {
        return trigger_response::trigger();
    }

    /**
     * Sql that queries only the sitecourse.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;
        list($insql, $inparam) = $DB->get_in_or_equal(SITEID, SQL_PARAMS_NAMED, 'param', false);
        return ["{course}.id {$insql}", $inparam];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'sitecourse';
    }

    /**
     * Has only one instance and results in a preset workflow.
     * @return bool
     */
    public function has_multiple_instances() {
        return false;
    }

}
