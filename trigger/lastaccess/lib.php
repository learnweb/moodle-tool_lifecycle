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
     * Checks the course and returns a response, which tells if the course should be further processed.
     *
     * @param \stdClass $course DEPRECATED
     * @param int $triggerid DEPRECATED
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Every decision is already in the where statement.
        return trigger_response::trigger();
    }

    /**
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
     * *
     * @param $triggerid int id of the trigger instance.
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;

        $sql = "SELECT la.courseid 
        FROM mdl_user_enrolments AS ue 
        JOIN mdl_enrol AS e ON (ue.enrolid = e.id) 
        JOIN mdl_user_lastaccess AS la ON (ue.userid = la.userid) 
        WHERE e.courseid = la.courseid 
        GROUP BY la.courseid 
        HAVING MAX(la.timeaccess) < :lastaccessthreshold";

        $delay = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['delay'];
        $now = time();

        try {
            $records = $DB->get_records_sql($sql, ["lastaccessthreshold" => $now - $delay]);
        } catch (\dml_exception $e) {
            $records = [];
        }

        $courseids = array_column($records, 'courseid');

        [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $where = "{course}.id {$insql}";
        return [$where, $inparams];
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
     * @return string
     */
    public function get_subpluginname() {
        return 'lastaccess';
    }

}
