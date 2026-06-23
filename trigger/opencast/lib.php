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
 * Trigger subplugin, which triggers on specific jku needs only.
 *
 * @package    lifecycletrigger_opencast
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

//use core_reportbuilder\local\aggregation\count;
use tool_lifecycle\local\manager\settings_manager;
//use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;
// Import trigger's lib
use lifecycletrigger_opencast\local\courseFilterOpencast;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package lifecycletrigger_opencast
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opencast extends base_automatic {
    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param object $course Course to be processed.
     * @param int $triggerid Id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Everything is already in the sql statement.
        return trigger_response::trigger();
    }

    /**
     * Returns true or false, depending on if the current date is one of the specified days,
     * at which the trigger should run.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \Exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;

        // Create course filter
        $courseFilter = new courseFilterOpencast();

        // Get all courses & reduce them to an array with course ids
        $course_ids = array_map(function($course) { return $course->id; }, get_courses());

        // Get settings
        $settings = settings_manager::get_settings($triggerid, settings_type::TRIGGER);
        //mtrace("  Mike - Trigger opencast: Settings: ".print_r($settings, true));
        //echo("<div>SETTINGS: <pre>".print_r($settings, true)."</pre></div>");
        // throw new \moodle_exception("Error text: " . $var);

        // Filter courses with opencast block
        $course_ids = $this->opencast_filter($course_ids, $settings, $courseFilter);

        // Create "where query"
        //$course_ids = array_merge($course_ids, [2, 5, 9, 12, 25]);
        if(count($course_ids) > 0) {
            $course_ids = array_unique($course_ids);
            list($insql, $inparams) = $DB->get_in_or_equal($course_ids, SQL_PARAMS_NAMED, 'courseid');
            $where = "{course}.id {$insql}";
            mtrace("  Course ids " . count($course_ids)); // . ": " . print_r($course_ids, true) . " -> where: " . $where . " + inparams: " . print_r($inparams, true));

            // Return "where query"
            return [$where, $inparams];
        }

        // Return "true or false"
        //return ['true', []];
        return ['false', []];
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
            // Add instance for courses with opencast block
            new instance_setting('opencast_courses', PARAM_TEXT),
        ];
    }

    /**
     * This method can be overwritten, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        // Add select for courses with opencast block
        $options = [
            'no_opencast' => get_string('no_opencast', 'lifecycletrigger_opencast'),
            'only_opencast' => get_string('only_opencast', 'lifecycletrigger_opencast'),
        ];
        $mform->addElement('select', 'opencast_courses', get_string('opencast_courses', 'lifecycletrigger_opencast'), $options);
        $mform->setDefault('opencast_courses', 'no_opencast');
    }

    /**
     * Validate parsable dates.
     * @param array $error Array containing all errors.
     * @param array $data Data passed from the moodle form to be validated.
     * @throws \coding_exception
     */
    public function extend_add_instance_form_validation(&$error, $data) {

    }

    // Filter courses with opencast block
    public function opencast_filter($course_ids, $settings, $courseFilter) {
        mtrace("  Mike - Trigger opencast: Filter opencast courses: ".$settings["opencast_courses"]);
        if($settings["opencast_courses"] == "no_opencast") { $courses = $courseFilter->get_opencast_courses(true); }
        else { $courses = $courseFilter->get_opencast_courses(false); }
        mtrace("  Mike - Trigger opencast: Opencast courses result: ".count($courses));
        // Intersect course ids
        $course_ids = array_intersect($course_ids, $courses);
        mtrace("  Mike - Trigger opencast: Opencast courses intersected");
        // Sort course ids
        sort($course_ids);

        return $course_ids;
    }
}
