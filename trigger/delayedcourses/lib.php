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
 * Trigger subplugin to include delayed courses.
 *
 * @package lifecycletrigger_delayedcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a life cycle trigger subplugin
 * @package lifecycletrigger_delayedcourses
 * @copyright  2025 Thomas Niedermaier University of Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delayedcourses extends base_automatic {

    /**
     * If check_course_code() returns true, code to check the given course is placed here
     * @param int $course id of the course
     * @param int $triggerid id of the trigger
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        return trigger_response::trigger();
    }

    /**
     * Returns the default response of this trigger.
     * @return trigger_response
     */
    public function default_response() {
        return trigger_response::trigger();
    }

    /**
     * Return SQL which excludes delayed courses.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;
        if (settings_manager::get_settings($triggerid, settings_type::TRIGGER)['includegenerallydelayed'] ?? false) {
            $workflowid = $DB->get_field('tool_lifecycle_trigger', 'workflowid', ['id' => $triggerid]);
            return delayed_courses_manager::get_course_delayed_all_wheresql($workflowid);
        } else {
            return delayed_courses_manager::get_course_delayed_wheresql();
        }
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'delayedcourses';
    }

    /**
     * Settings for the trigger.
     * @return array|instance_setting[]
     */
    public function instance_settings() {
        return [
            new instance_setting('includegenerallydelayed', PARAM_BOOL),
        ];
    }

    /**
     * Form elements for the instance settings of the trigger.
     * @param \MoodleQuickForm $mform
     * @return void
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {

        $mform->addElement('advcheckbox', 'includegenerallydelayed', get_string('includegenerallydelayed',
            'lifecycletrigger_delayedcourses'), get_string('includegenerallydelayed_help',
            'lifecycletrigger_delayedcourses'));
        $mform->setType('includegenerallydelayed', PARAM_BOOL);
    }
}
