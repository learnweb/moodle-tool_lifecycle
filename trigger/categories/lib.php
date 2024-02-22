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
 * Trigger subplugin to include or exclude courses of certain categories.
 *
 * @package lifecycletrigger_categories
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use coursecat;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;
use core_course_category;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package lifecycletrigger_categories
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categories extends base_automatic {

    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param object $course Course to be processed.
     * @param int $triggerid Id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Every decision is already in the where statement.
        return trigger_response::trigger();
    }

    /**
     * Return sql sniplet for including (or excluding) the courses belonging to specific categories
     * and all their children.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB, $CFG;
        $categories = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['categories'];
        $exclude = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['exclude'];

        $categories = explode(',', $categories);
        // Use core_course_category for moodle 3.6 and higher.
        if ($CFG->version >= 2018120300) {
            $categoryobjects = \core_course_category::get_many($categories);
        } else {
            require_once($CFG->libdir . '/coursecatlib.php');
            $categoryobjects = \coursecat::get_many($categories);
        }
        $allcategories = [];
        foreach ($categories as $category) {
            array_push($allcategories, $category);
            if (!isset($categoryobjects[$category]) || !$categoryobjects[$category]) {
                continue;
            }
            $children = $categoryobjects[$category]->get_all_children_ids();
            $allcategories = array_merge($allcategories, $children);
        }

        list($insql, $inparams) = $DB->get_in_or_equal($allcategories, SQL_PARAMS_NAMED, 'param', !$exclude);

        $where = "{course}.category {$insql}";

        return [$where, $inparams];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'categories';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('categories', PARAM_SEQUENCE, true),
            new instance_setting('exclude', PARAM_BOOL, true),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $displaylist = core_course_category::make_categories_list();
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('categories_noselection', 'lifecycletrigger_categories'),
        ];
        $mform->addElement('autocomplete', 'categories',
            get_string('categories', 'lifecycletrigger_categories'),
            $displaylist, $options);
        $mform->setType('categories', PARAM_SEQUENCE);

        $mform->addElement('advcheckbox', 'exclude', get_string('exclude', 'lifecycletrigger_categories'));
        $mform->setType('exclude', PARAM_BOOL);
    }

    /**
     * Ensure validity of settings upon backup restoration.
     * @param array $settings
     * @return array List of errors with settings. If empty, the given settings are valid.
     */
    public function ensure_validity(array $settings): array {
        $missingcategories = [];
        $categories = explode(',', $settings['categories']);
        $categoryobjects = \core_course_category::get_many($categories);
        foreach ($categories as $category) {
            if (!isset($categoryobjects[$category]) || !$categoryobjects[$category]) {
                $missingcategories[] = $category;
            }
        }
        if ($missingcategories) {
            return [get_string('categories_do_not_exist', 'lifecycletrigger_categories', join(', ', $missingcategories))];
        } else {
            return [];
        }
    }

}
