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
 * @package tool_lifecycle_trigger
 * @subpackage startdatedelay
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\response\trigger_response;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package tool_lifecycle_trigger
 */
class startdatedelay extends base_automatic {


    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param $course object to be processed.
     * @param $triggerid int id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Everything is already in the sql statement.
        return trigger_response::trigger();
    }

    public function get_course_recordset_where($triggerid) {
        $delay = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER)['delay'];
        $startdate = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER)['startdate'];
        $where = "{course}.".$startdate." < :startdatedelay";
        $params = array(
            "startdatedelay" => time() - $delay,
        );
        return array($where, $params);
    }

    public function get_subpluginname() {
        return 'startdatedelay';
    }

    public function instance_settings() {
        return array(
            new instance_setting('delay', PARAM_INT),
            new instance_setting('startdate', PARAM_TEXT)
        );
    }

    public function extend_add_instance_form_definition($mform) {
        $startoptions = array(
            'startdate' => get_string('start_option', 'lifecycletrigger_startdatedelay'),
            'enddate' => get_string('end_option', 'lifecycletrigger_startdatedelay'),
            'timecreated' => get_string('created_option', 'lifecycletrigger_startdatedelay'),
            'timemodified' => get_string('modified_option', 'lifecycletrigger_startdatedelay'),
        );
        $mform->addElement('select', 'startdate', get_string('startdate', 'lifecycletrigger_startdatedelay'), $startoptions);
        $mform->addElement('duration', 'delay', get_string('delay', 'lifecycletrigger_startdatedelay'));
        $mform->addHelpButton('delay', 'delay', 'lifecycletrigger_startdatedelay');
    }

    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings) && array_key_exists('delay', $settings)) {
            $defaultdelay = $settings['delay'];
        } else {
            $defaultdelay = 16416000;
        }
        $mform->setDefault('delay', $defaultdelay);

        if (is_array($settings) && array_key_exists('startdate', $settings)) {
            $defaultstartdate = $settings['startdate'];
        } else {
            $defaultstartdate = 'startdate';
        }
        $mform->setDefault('startdate', $defaultstartdate);
    }
}
