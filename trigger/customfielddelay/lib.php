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

namespace tool_lifecycle\trigger;

use moodle_url;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Trigger which triggers after a defined period of time after a date value in a course date custom field.
 * @package lifecycletrigger_customfielddelay
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2020 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customfielddelay extends base_automatic {

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
     * Returns whether the lib function check_course contains particular selection code per course or not.
     * @return bool
     */
    public function check_course_code() {
        return false;
    }

    /**
     * Add sql comparing the current date to the start date of a course in combination with the specified delay.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;
        $delay = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['delay'];
        $fieldname = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['customfield'];
        if (!($field = $DB->get_record('customfield_field', ['shortname' => $fieldname, 'type' => 'date']))) {
            throw new \moodle_exception('missingfield',
                'lifecycletrigger_customfielddelay', '', $fieldname);
        }
        $where = "c.id in (select cxt.instanceid from {context} cxt join {customfield_data} d " .
                    "ON d.contextid = cxt.id AND cxt.contextlevel=" . CONTEXT_COURSE . " " .
                    "WHERE d.fieldid = :customfielddateid AND d.intvalue > 0 AND d.intvalue < :customfielddelay)";
        $params = ["customfielddelay" => time() - $delay, "customfielddateid" => $field->id];
        return [$where, $params];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'customfielddelay';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('delay', PARAM_INT),
            new instance_setting('customfield', PARAM_TEXT),
        ];
    }

    /**
     * At the delay since the start date of a course.
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function extend_add_instance_form_definition($mform) {
        global $DB;
        $mform->addElement('duration', 'delay',
            get_string('delay', 'lifecycletrigger_customfielddelay'));
        $mform->addHelpButton('delay', 'delay', 'lifecycletrigger_customfielddelay');
        $fields = $DB->get_records('customfield_field', ['type' => 'date']);
        $choices = [];
        foreach ($fields as $field) {
            $choices[$field->shortname] = $field->name;
        }
        if ($choices) {
            $mform->addElement('select', 'customfield',
                get_string('customfield', 'lifecycletrigger_customfielddelay'), $choices);
            $mform->addHelpButton('customfield', 'customfield',
                'lifecycletrigger_customfielddelay');
        } else {
            $mform->addElement('static', 'nocustomfields',
                get_string('nocustomfields_warning', 'lifecycletrigger_customfielddelay'),
                    \html_writer::link(new moodle_url('/course/customfield.php'),
                        get_string('nocustomfields_link', 'lifecycletrigger_customfielddelay')));
        }
    }

    /**
     * Reset the delay at the add instance form initialization.
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings) && array_key_exists('delay', $settings)) {
            $default = $settings['delay'];
        } else {
            $default = 16416000;
        }
        $mform->setDefault('delay', $default);
        if (is_array($settings) && array_key_exists('customfield', $settings)) {
            $default = $settings['customfield'];
            $mform->setDefault('customfield', $default);
        }
    }
}
