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
 * Trigger subplugin to include or exclude courses with certain activity videos.
 *
 * @package     lifecycletrigger_activity
 * @copyright   2025 Thomas Niedermaier University Münster
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use core_plugin_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for a courses trigger subplugin
 * @package     lifecycletrigger_activity
 * @copyright   2025 Thomas Niedermaier University Münster
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity extends base_automatic {

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
     * Return SQL snippet for including (or excluding) the courses with at least one instance of the defined activities.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {

        $sql = "(";

        $exclude = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['exclude'];
        $activities = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['activities'];

        $modules = explode(',', $activities);

        $not = $exclude ? 'NOT' : '';
        $conj = "";
        foreach ($modules as $module) {
            $sql .= $conj." c.id $not IN (SELECT course FROM {course_modules} where module = $module) ";
            $conj = " OR ";
        }
        $sql .= ")";

        $where = $sql;

        return [$where, []];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'activity';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('activities', PARAM_SEQUENCE),
            new instance_setting('exclude', PARAM_BOOL),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_trigger_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function extend_add_instance_form_definition($mform) {
        global $DB;

        $modules = $DB->get_records('modules', null, 'name', 'id,name');
        $activities = [];
        foreach ($modules as $key => $module) {
            $activities[$key] = $module->name;
        }
        if ($activities) {
            $options = [
                'multiple' => true,
                'noselectionstring' => get_string('noselection', 'lifecycletrigger_activity'),
            ];
            $mform->addElement('autocomplete', 'activities', "", $activities, $options);
            $mform->setType('activities', PARAM_SEQUENCE);
            $mform->addRule('activities', get_string('activities_rule', 'lifecycletrigger_activity'), 'required');

            $mform->addElement('advcheckbox', 'exclude', get_string('exclude', 'lifecycletrigger_activity'));

            $mform->addHelpButton('exclude', 'exclude', 'lifecycletrigger_activity');
        }
    }

    /**
     * Since the rendering of frozen autocomplete elements is awful, we override it here.
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        global $DB;

        $type = $mform->getElementType('instancename');
        if (($type ?? "") != "text") {
            if (is_array($settings) && array_key_exists('', $settings)) {
                $triggeractivities = explode(",", $settings['activities']);
            } else {
                $triggeractivities = [];
            }
            $modules = $DB->get_records('modules', null, 'name', 'id,name');
            $activitieshtml = "";
            foreach ($modules as $key => $module) {
                $activities[$key] = $module->name;
                if (in_array($key, $triggeractivities)) {
                    $activitieshtml .= \html_writer::div($module->name, "badge badge-secondary mr-1");
                }
            }
            $mform->insertElementBefore($mform->createElement(
                'static',
                'activitiesstatic',
                get_string('activities', 'lifecycletrigger_activity'),
                $activitieshtml), 'buttonar');
            $mform->insertElementBefore($mform->createElement(
                'advcheckbox',
                'exclude',
                get_string('exclude', 'lifecycletrigger_activity')),
                'buttonar');
            $mform->setType('exclude', PARAM_BOOL);
        }
    }

    /**
     * Specifies if this trigger can be used more than once in a single workflow.
     * @return bool
     */
    public function multiple_use() {
        return false;
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'i/activities';
    }
}
