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
 * Step subplugin for storing workflow information in course custom fields.
 *
 * @package lifecyclestep_setcustomfield
 * @copyright  2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use core_customfield\api;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../email/lib.php');


/**
 * Step subplugin for setting course custom field.
 * @package    lifecyclestep_setcustomfield
 * @copyright  2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setcustomfield extends libbase {

    /**
     * Constant to set custom field to empty value.
     */
    const EMPTY = 'empty';
    /**
     * Constant to set custom field to now.
     */
    const NOW = 'now';
    /**
     * Constant to set custom field to now.
     */
    const VALUE = 'value';

    /**
     * Processes the course and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param object $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        process_data_manager::set_process_data($processid, $instanceid, 'visible', $course->visible);
        process_data_manager::set_process_data($processid, $instanceid, 'visibleold', $course->visibleold);
        $this->set_field($instanceid, $course);
        return step_response::proceed();
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'setcustomfield';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('customfield', PARAM_TEXT, true),
            new instance_setting('set', PARAM_TEXT, true),
            new instance_setting('value', PARAM_TEXT, true),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {

        global $DB;
        $fields = $DB->get_records('customfield_field');
        $choices = [];
        foreach ($fields as $field) {
            $choices[$field->shortname] = $field->name;
        }
        if ($choices) {
            $mform->addElement('select', 'customfield',
                    get_string('customfield', 'lifecyclestep_setcustomfield'), $choices);
            $mform->setType('customfield', PARAM_TEXT);
            $mform->addHelpButton('customfield', 'customfield',
                    'lifecyclestep_setcustomfield');
        } else {
            $mform->addElement('static', 'nocustomfields',
                    get_string('nocustomfields_warning', 'lifecyclestep_setcustomfield'),
                    \html_writer::link(new \moodle_url('/course/customfield.php'),
                            get_string('nocustomfields_link', 'lifecyclestep_setcustomfield')));
        }

        $choices = [];
        $choices[self::EMPTY] = get_string('setempty', 'lifecyclestep_setcustomfield');
        $choices[self::NOW] = get_string('settimestamp', 'lifecyclestep_setcustomfield');
        $choices[self::VALUE] = get_string('setvalue', 'lifecyclestep_setcustomfield');

        $mform->addElement('select', 'set',
                get_string('action', 'lifecyclestep_setcustomfield'), $choices);
        $mform->setType('set', PARAM_TEXT);
        $mform->addHelpButton('set', 'action',
                'lifecyclestep_setcustomfield');

        $mform->addElement('text', 'value',
            get_string('value', 'lifecyclestep_setcustomfield'), $choices);
        $mform->setType('value', PARAM_TEXT);
        $mform->setDefault('value', '');
        $mform->addHelpButton('value', 'value',
            'lifecyclestep_setcustomfield');
        $mform->hideIf('value', 'set', 'neq', self::VALUE);

    }


    /**
     * read custom field for course contact from database
     *
     * @param int $instanceid
     * @param \moodle_database|null $DB
     * @param object $course
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function set_field(int $instanceid, object $course) {
        $fieldname = settings_manager::get_settings($instanceid, settings_type::STEP)['customfield'];
        $set = settings_manager::get_settings($instanceid, settings_type::STEP)['set'];

        $fields = \core_course\customfield\course_handler::create()->get_fields();

        $context = \context_course::instance($course->id);
        $records = api::get_instance_fields_data($fields, $course->id);

        // Determine for actual field object.
        $fieldobj = null;
        foreach ($fields as $field) {
            if ($field->get('shortname') == $fieldname) {
                $fieldobj = $field;
                break;
            }
        }

        foreach ($records as $d) {
            $field = $d->get_field();
            if ($field->get('shortname') !== $fieldname) {
                continue;
            }
            switch($set) {
                case self::EMPTY:
                    switch ($field->get('type')) {
                        case 'text':
                        case 'textarea':
                            $value = '';
                            break;
                        default:
                            $value = 0;
                            break;
                    }
                    break;
                case self::NOW:
                    $value = time();
                    break;
                case self::VALUE:
                    $value = settings_manager::get_settings($instanceid, settings_type::STEP)['value'];
                    if ($field->get('type') == 'select' && isset($fieldobj)) {
                        // Special handling for select:
                        // Possible values are array index and array value.
                        // Prefer index to value.
                        $options = $fieldobj->get_options();
                        if (!array_key_exists($value, $options)) {
                            if (array_search($value, $options) !== false) {
                                $value = array_search($value, $options);
                            }
                        }
                    }
                    break;
                default:
                    throw new \coding_exception('invalid value');
            }
            $d->set($d->datafield(), $value);
            $d->set('value', $value);
            $d->set('valuetrust', 0);
            $d->set('contextid', $context->id);
            $d->save();
            return;
        }

        throw new \coding_exception('could not find custom field ' . $fieldname);
    }


    /**
     * This method can be overriden, to add additional data validation to the instance form.
     * @param array $error Array containing all errors.
     * @param array $data Data passed from the moodle form to be validated
     */
    public function extend_add_instance_form_validation(&$error, $data) {
        if ($data['set'] != self::VALUE) {
            // Special handling for select fields with value
            // => no value to be set => finished.
            return;
        }
        $fieldname = $data['customfield'];
        $fields = \core_course\customfield\course_handler::create()->get_fields();
        foreach ($fields as $field) {
            if ($field->get('shortname') != $fieldname) {
                continue;
            }
            switch ($field->get('type')) {
                case 'select':
                    // Check if value to be set is valid.
                    $value = $data['value'];
                    $options = $field->get_options();
                    if (array_key_exists($value, $options)) {
                        // Value is a key => ok, finished.
                        return;
                    }
                    // Value is not a key => check if value is a array value.
                    if (array_search($value, $options) !== false) {
                        // Value is found => finished.
                        return;
                    }
                    // Values was not found.
                    $error['value'] = get_string('error.invalidvalue', 'lifecyclestep_setcustomfield');
                    break;
                case 'number':
                    if (!is_number($data['value'])) {
                        $error['value'] = get_string('error.invalidvalue', 'lifecyclestep_setcustomfield');
                    }
                    break;
                default:
                    // No validation required.
                    break;
            }
            return;
        }
        throw new \coding_exception('could not find custom field ' . $fieldname);
    }
}
