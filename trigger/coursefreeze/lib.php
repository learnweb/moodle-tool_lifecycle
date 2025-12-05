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
 * @package lifecycletrigger_coursefreeze
 * @copyright  2025 Gifty Wanzola (ccaewan)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\trigger;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;
use tool_lifecycle\trigger\instance_setting;


defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the trigger for freezing courses.
 *
 * @package lifecycletrigger_coursefreeze
 */
class coursefreeze extends base_automatic {
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
 * Instance settings for this trigger.
 *
 * @return instance_setting[]
 */
public function instance_settings() {
    return [
        // Last access must be older than this (12 months)
        new instance_setting('lastaccessdelay', PARAM_INT),

        // Course creation must be older than this (24 months)
        new instance_setting('creationdelay', PARAM_INT),
    ];
}

    /**
     * Returns the where statement for all courses that should be triggered,
     * meaning timestamp of the course freeze / interaction with this course is older than delay
     * (only counting interactions of users who are enrolled in the course)
     *
     * @param int $triggerid  id of the trigger instance.
     * @return string[]
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;

        // Get trigger settings.
        $settings = settings_manager::get_settings($triggerid, settings_type::TRIGGER);

        // Fallback defaults if not set.
        $lastaccessdelay = isset($settings['lastaccessdelay']) ? $settings['lastaccessdelay'] : DAYSECS * 365;        // 12 months.
        $creationdelay   = isset($settings['creationdelay'])   ? $settings['creationdelay']   : DAYSECS * 365 * 2;    // 24 months.

        $now = time();
        $lastaccessthreshold = $now - $lastaccessdelay;
        $creationthreshold   = $now - $creationdelay;

        // Only courses that:
        //  - have last access older than lastaccessthreshold
        //  - AND were created before creationthreshold.
        $where = 'c.timecreated < :creationthreshold
                  AND c.id IN (
                        SELECT la.courseid
                          FROM {user_enrolments} ue
                          JOIN {enrol} e ON ue.enrolid = e.id
                          JOIN {user_lastaccess} la ON ue.userid = la.userid
                         WHERE e.courseid = la.courseid
                         GROUP BY la.courseid
                         HAVING MAX(la.timeaccess) < :lastaccessthreshold
                  )';

        $params = [
            'creationthreshold'   => $creationthreshold,
            'lastaccessthreshold' => $lastaccessthreshold,
        ];

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

        // Check last access .
        $elementname = 'lastaccessdelay';
        $mform->addElement('duration', $elementname,
            get_string($elementname, 'lifecycletrigger_coursefreeze'));
        $mform->addHelpButton($elementname, $elementname, 'lifecycletrigger_coursefreeze');
        $mform->setDefault($elementname, DAYSECS * 365); // default = 12 months


        // Chech course creation age.
        $elementname = 'creationdelay';
        $mform->addElement('duration', $elementname,
            get_string($elementname, 'lifecycletrigger_coursefreeze'));
        $mform->addHelpButton($elementname, $elementname, 'lifecycletrigger_coursefreeze');
        $mform->setDefault($elementname, DAYSECS * 365 * 2); // default = 24 months
    }


    /**
     * Extend add instance form
     *
     * @param \MoodleQuickForm $mform
     * @param array $settings
     * @return void
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings)) {
            if (array_key_exists('lastaccessdelay', $settings)) {
                $mform->setDefault('lastaccessdelay', $settings['lastaccessdelay']);
            }
            if (array_key_exists('creationdelay', $settings)) {
                $mform->setDefault('creationdelay', $settings['creationdelay']);
            }
        }
    }

    /**
     * Return subplugin name
     *
     * @return string
     */
    public function get_subpluginname() {
        return 'coursefreeze';
    }

}
