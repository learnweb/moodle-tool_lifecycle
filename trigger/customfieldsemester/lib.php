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
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Library
 *
 * @package    lifecycletrigger_customfieldsemester
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 *             based on code 2020 by Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\trigger;

use core_customfield\data_controller;
use core_customfield\field_controller;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;
use DateTime;
use DateInterval;
use core_date;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Customfield semester class
 *
 * @package    lifecycletrigger_customfieldsemester
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 *             based on code 2020 by Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customfieldsemester extends base_automatic {

    /**
     * If check_course_code() returns true, code to check the given course is placed here
     * @param \stdClass $course Course to be processed.
     * @param int $triggerid ID of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Everything is already in the sql statement.
        return trigger_response::trigger();
    }

    /**
     * Add sql comparing the current date to the start date of a course in combination with the specified delay.
     *
     * @param int $triggerid Id of the trigger.
     *
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;

        // Get the trigger instance settings from the settings manager.
        $customfield = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['customfield'];
        $delay = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['delay'];

        // If the configured custom field does not exist, throw an exception.
        if (!($field = $DB->get_record('customfield_field', ['shortname' => $customfield, 'type' => 'semester']))) {
            throw new \moodle_exception('error_missingfield', 'lifecycletrigger_customfieldsemester', '',
                    ['missingfield' => $customfield]);
        }

        // Get all existing term values.
        $fielddatasql = 'SELECT DISTINCT intvalue
                         FROM {customfield_data}
                         WHERE fieldid = :fieldid AND intvalue != 1
                         ORDER BY intvalue DESC';
        $fielddataparams = ['fieldid' => $field->id];
        $fielddata = $DB->get_records_sql($fielddatasql, $fielddataparams);

        // Initialize the oldest term which should be left untriggered in this run.
        // As we might have only really old courses in this Moodle instance and all courses will have to be triggered,
        // we initialize this with the current default value of the custom field (which is in the future by design).
        $fieldcontroller = field_controller::create($field->id);
        $datacontroller = data_controller::create(0, null, $fieldcontroller);
        $oldesttermtoleaveuntriggered = $datacontroller->get_default_value();

        // Get the general start months of the terms.
        $summertermstartmonth = $datacontroller->get_summerterm_startmonth();
        $wintertermstartmonth = $datacontroller->get_winterterm_startmonth();;

        // Iterate over the existing term values.
        foreach ($fielddata as $f) {
            // If the field's intvalue is 0 or does not have 5 digits, this isn't a valid term.
            if ($f->intvalue == 0 || floor(log10($f->intvalue) + 1) != 5) {
                continue;
            }

            // Split the year and term.
            $fyear = intdiv($f->intvalue, 10);
            $fterm = $f->intvalue % 10;

            // If the term is neither 0 or 1, this isn't a valid term.
            if ($fterm != 0 && $fterm != 1) {
                continue;
            }

            // Pick the this term's term start month in two-digit notation.
            if ($fterm === 0) {
                $termstartmonth = sprintf('%02d', $summertermstartmonth);
            } else {
                $termstartmonth = sprintf('%02d', $wintertermstartmonth);

            }

            // Calculate this term's term start day.
            // We use the 'Eight digit year, month and day' format which the DateTime parser understands
            // (See https://www.php.net/manual/en/datetime.formats.date.php).
            $termstartday = new DateTime($fyear.$termstartmonth.'01', core_date::get_server_timezone_object());

            // Add the configured amount of delay months.
            $termstartday->add(new DateInterval('P'.$delay.'M'));

            // Get the current time stamp to be used for comparison.
            $now = new DateTime('now', core_date::get_server_timezone_object());

            // If this term's delay has not passed yet.
            if ($termstartday > $now) {
                // We remember this term as the new oldest term which should be left untriggered in this run.
                $oldesttermtoleaveuntriggered = $f->intvalue;

                // Otherwise, we have reached the newest term which should be triggered in this run.
                // No need to further iterate the remaining terms as the terms are ordered in descending order.
            } else {
                break;
            }
        }

        // Compose the WHERE clause by getting the course IDs from all courses which are
        // a) old enough according to their term
        // b) not a term-independent course (fdata.intvalue = 1).
        $where = '{course}.id IN
                      (
                          SELECT ctx.instanceid
                          FROM {context} ctx
                          JOIN {customfield_data} fdata
                              ON fdata.contextid = ctx.id AND ctx.contextlevel = '.CONTEXT_COURSE.'
                          WHERE fdata.fieldid = :customfieldid AND fdata.intvalue < :oldesttermtoleaveuntriggered
                              AND fdata.intvalue != 1
                      )';
        $params = [
            'oldesttermtoleaveuntriggered' => $oldesttermtoleaveuntriggered,
            'customfieldid' => $field->id,
        ];

        return [$where, $params];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'customfieldsemester';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('customfield', PARAM_TEXT),
            new instance_setting('delay', PARAM_INT),
        ];
    }

    /**
     * Add form elements to the form_step_instance.
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        global $DB;

        // Add the 'Customfield' field.
        $customfields = $DB->get_records('customfield_field', ['type' => 'semester']);
        // If we have found at least one field.
        if ($customfields) {
            $customfieldchoices = [];
            foreach ($customfields as $field) {
                $customfieldchoices[$field->shortname] = $field->name;
            }
            $mform->addElement('select', 'customfield', get_string('setting_customfield', 'lifecycletrigger_customfieldsemester'),
                    $customfieldchoices);
            $mform->addHelpButton('customfield', 'setting_customfield', 'lifecycletrigger_customfieldsemester');

            // Otherwise.
        } else {
            $managefieldsurl = new \core\url('/course/customfield.php');
            $mform->addElement('static', 'customfield', get_string('setting_customfield', 'lifecycletrigger_customfieldsemester'),
                    get_string('setting_customfield_nofield', 'lifecycletrigger_customfieldsemester', $managefieldsurl->out()));
        }

        // Add the 'Delay' field.
        $mform->addElement('text', 'delay', get_string('setting_delay', 'lifecycletrigger_customfieldsemester'));
        $mform->addHelpButton('delay', 'setting_delay', 'lifecycletrigger_customfieldsemester');
        $mform->addRule('delay', get_string('error_delaypositive', 'lifecycletrigger_customfieldsemester'),
                'regex', '#^([1-9]|[1-9][0-9]|[1-9][0-9][0-9])$#');
        $mform->setType('delay', PARAM_INT);
    }

    /**
     * Set default values to the form_step_instance.
     *
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        // Add the default for the 'Customfield' field from the setting which was stored previously.
        if (is_array($settings) && array_key_exists('customfield', $settings)) {
            $customfielddefault = $settings['customfield'];
            $mform->setDefault('customfield', $customfielddefault);
        }

        // Add the default for the 'Delay' field or the setting which was stored previously.
        if (is_array($settings) && array_key_exists('delay', $settings)) {
            $delaydefault = $settings['delay'];
        } else {
            $delaydefault = 24;
        }
        $mform->setDefault('delay', $delaydefault);
    }

    /**
     * Add additional data validation to the instance form.
     *
     * @param array $error Array containing all errors.
     * @param array $data Data passed from the moodle form to be validated
     */
    public function extend_add_instance_form_validation(&$error, $data) {
        // Call parent form validation.
        parent::extend_add_instance_form_validation($error, $data);

        // Make all form fields required.
        $requiredsettings = $this->instance_settings();
        foreach ($requiredsettings as $setting) {
            if (!array_key_exists($setting->name, $data) || empty($data[$setting->name])) {
                $error[$setting->name] = get_string('required');
            }
        }
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'i/course';
    }
}
