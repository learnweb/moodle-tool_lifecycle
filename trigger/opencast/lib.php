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
 * Trigger subplugin to include or exclude courses with certain opencast videos.
 *
 * @package     lifecycletrigger_opencast
 * @copyright   2025 Thomas Niedermaier University Münster
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use coursecat;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');
require_once(__DIR__ . '/../../../../../mod/lti/locallib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package     lifecycletrigger_opencast
 * @copyright   2025 Thomas Niedermaier University Münster
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opencast extends base_automatic {

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
     * Return sql snippet for including (or excluding) the courses with defined opencast videos.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;

        $sql = "";
        $inparams = [];

        $exclude = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['exclude'];
        $activity = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['activity'];
        $lti = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['lti'];

        $not = $exclude ? 'NOT' : '';
        if ($activity) {
            $sql = " c.id $not IN (SELECT DISTINCT(course) FROM {opencast}) ";
        }
        if ($lti) {
            $ltitools = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['ltitools'];
            $ltitoolsarr = explode(",", $ltitools);
            [$insql, $inparams] = $DB->get_in_or_equal($ltitoolsarr, SQL_PARAMS_NAMED);
            if ($sql) {
                if ($exclude) {
                    $sql = "($sql AND c.id $not IN (SELECT DISTINCT(l.course) FROM {lti} l where
                    l.typeid $insql))";
                } else {
                    $sql = "($sql OR c.id IN (SELECT DISTINCT(l.course) FROM {lti} l where
                    l.typeid $insql))";
                }
            } else {
                $sql = "c.id $not IN (SELECT DISTINCT(l.course) FROM {lti} l where
                    l.typeid IN $insql)";
            }
        }

        $where = $sql;

        return [$where, $inparams];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'opencast';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('activity', PARAM_BOOL),
            new instance_setting('lti', PARAM_BOOL),
            new instance_setting('ltitools', PARAM_SEQUENCE),
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

        $mform->addElement('advcheckbox', 'activity',
            get_string('activity', 'lifecycletrigger_opencast'));
        $mform->setType('activity', PARAM_BOOL);
        $mform->addHelpButton('activity', 'activity', 'lifecycletrigger_opencast');

        $types = lti_filter_get_types(get_site()->id);
        $tools = lti_filter_tool_types($types, LTI_TOOL_STATE_ANY);
        $ltiinstances = $DB->get_fieldset_sql('SELECT DISTINCT(typeid) FROM {lti}');
        $ltitools = [];
        foreach ($tools as $key => $tool) {
            if (!array_key_exists($tool->typeid, $ltiinstances)) {
                continue;
            }
            $ltitools[$key] = $tool->name." (".$tool->baseurl.")";
        }
        if ($ltitools) {
            $mform->addElement('advcheckbox', 'lti',
                get_string('lti', 'lifecycletrigger_opencast'));
            $mform->setType('lti', PARAM_BOOL);
            $mform->addHelpButton('lti', 'lti', 'lifecycletrigger_opencast');
            $options = [
                'multiple' => true,
                'noselectionstring' => get_string('lti_noselection', 'lifecycletrigger_opencast'),
            ];
            $mform->addElement('autocomplete', 'ltitools', "", $ltitools, $options);
            $mform->setType('ltitools', PARAM_SEQUENCE);

            // Disable lti tools unless lti checkbox is checked.
            $mform->hideIf('ltitools', 'lti', 'notchecked');
        }

        $mform->addElement('advcheckbox', 'exclude', get_string('exclude', 'lifecycletrigger_opencast'));
        $mform->addHelpButton('exclude', 'exclude', 'lifecycletrigger_opencast');
    }

    /**
     * Since the rendering of frozen autocomplete elements is awful, we override it here.
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        $type = $mform->getElementType('instancename');
        if (($type ?? "") != "text") {
            if (is_array($settings) && array_key_exists('ltitools', $settings)) {
                $triggerltitools = explode(",", $settings['ltitools']);
            } else {
                $triggerltitools = [];
            }
            $types = lti_filter_get_types(get_site()->id);
            $configuredtools = lti_filter_tool_types($types, LTI_TOOL_STATE_CONFIGURED);
            $ltitoolshtml = "";
            foreach ($configuredtools as $key => $tool) {
                if (in_array($key, $triggerltitools)) {
                    $ltitoolshtml .= \html_writer::div($tool->name." (".$tool->baseurl.")", "badge badge-secondary mr-1");
                }
            }
            $mform->insertElementBefore($mform->createElement(
                'static',
                'ltitoolsstatic',
                get_string('ltitools', 'lifecycletrigger_opencast'),
                $ltitoolshtml), 'buttonar');
            $mform->insertElementBefore($mform->createElement(
                'advcheckbox',
                'exclude',
                get_string('exclude', 'lifecycletrigger_opencast')),
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

}
