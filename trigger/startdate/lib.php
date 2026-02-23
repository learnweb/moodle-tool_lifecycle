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
 * Subplugin for the start date.
 *
 * @package lifecycletrigger_startdate
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use DateTime;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for trigger courses subplugin
 *
 * @package    lifecycletrigger_startdate
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class startdate extends base_automatic {

    /**
     * If check_course_code() returns true, code to check the given course is placed here
     * @param object $course
     * @param int $triggerid
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        return trigger_response::trigger();
    }

    /**
     * Add SQL checking if the course start date is in the given time window.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        $datefrom = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['datefrom'];
        $dateto = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['dateto'];
        $where = "c.startdate > 0 AND c.startdate < :dateto AND c.startdate > :datefrom";
        $params = [
            "datefrom" => $datefrom,
            "dateto" => $dateto,
        ];
        return [$where, $params];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'startdate';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('datefrom', PARAM_INT, true),
            new instance_setting('dateto', PARAM_INT, true),
        ];
    }

    /**
     * Defines the timeframe (date from/to) in which the start date of the course should be.
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {

        $mform->addElement('date_time_selector', 'datefrom', get_string('datefrom', 'lifecycletrigger_startdate'));
        $mform->addHelpButton('datefrom', 'datefrom', 'lifecycletrigger_startdate');
        $mform->addRule('datefrom', null, 'required');

        $mform->addElement('date_time_selector', 'dateto', get_string('dateto', 'lifecycletrigger_startdate'));
        $mform->addHelpButton('dateto', 'dateto', 'lifecycletrigger_startdate');
        $mform->addRule('dateto', null, 'required');

    }

    /**
     * set the defaults at the add instance form initialization.
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings) && array_key_exists('datefrom', $settings)) {
            $dt = new DateTime();
            $dt->setTimestamp($settings['datefrom']);
            $mform->setDefault('datefrom', $dt->getTimestamp());
        } else {
            $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
            $date->modify('-1 day');
            $mform->setDefault('datefrom', $date->getTimestamp());
        }
        if (is_array($settings) && array_key_exists('dateto', $settings)) {
            $dt = new DateTime();
            $dt->setTimestamp($settings['dateto']);
            $mform->setDefault('dateto', $dt->getTimestamp());
        } else {
            $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
            $mform->setDefault('dateto', $date->getTimestamp());
        }
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'e/left_to_right';
    }
}
