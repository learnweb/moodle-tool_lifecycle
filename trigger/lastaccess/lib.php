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
 * Interface for the subplugintype trigger
 * It has to be implemented by all subplugins.
 *
 * @package lifecycletrigger_lastaccess
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\trigger;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 *
 * @package lifecycletrigger_lastaccess
 */
class lastaccess extends base_automatic {
    /**
     * If check_course_code() returns true, code to check the given course is placed here
     * @param \stdClass $course
     * @param int $triggerid
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Every decision is already in the where statement.
        return trigger_response::trigger();
    }

    /**
     * Instance setting delay.
     *
     * @return instance_setting[]
     */
    public function instance_settings() {
        return [
            new instance_setting('delay', PARAM_INT),
        ];
    }

    /**
     * Returns the where statement for all courses that should be triggered,
     * meaning timestamp of the last access / interaction with this course is older than delay
     * (only counting interactions of users who are enrolled in the course)
     *
     * @param int $triggerid  id of the trigger instance.
     * @return string[]
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {

        $where = 'c.id IN
            (SELECT la.courseid
                FROM {user_enrolments} ue
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {user_lastaccess} la ON ue.userid = la.userid
                WHERE e.courseid = la.courseid
                GROUP BY la.courseid
                HAVING MAX(la.timeaccess) < :lastaccessthreshold
            )';

        $delay = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['delay'];
        $now = time();
        $params = ["lastaccessthreshold" => $now - $delay];

        return [$where, $params];
    }

    /**
     * Add elements to add instance form
     *
     * @param \MoodleQuickForm $mform
     * @return void
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'delay';
        $mform->addElement('duration', $elementname, get_string($elementname, 'lifecycletrigger_lastaccess'));
        $mform->addHelpButton($elementname, $elementname, 'lifecycletrigger_lastaccess');
    }

    /**
     * Extend add instance form
     *
     * @param \MoodleQuickForm $mform
     * @param array $settings
     * @return void
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings) && array_key_exists('delay', $settings)) {
            $default = $settings['delay'];
        } else {
            $default = 16416000;
        }
        $mform->setDefault('delay', $default);
    }

    /**
     * Return subplugin name
     *
     * @return string
     */
    public function get_subpluginname() {
        return 'lastaccess';
    }

}
