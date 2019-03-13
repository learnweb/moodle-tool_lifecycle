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
 * @subpackage category
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\trigger;

use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\response\trigger_response;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 *
 * @package tool_lifecycle_trigger
 */
class category extends base_automatic {

    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     *
     * @param $course object to be processed.
     * @param $triggerid int id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        $settings = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER);
        if ($course->category == $settings['category_select']) {
            return trigger_response::trigger();
        }
        return trigger_response::next();
    }

    public function get_subpluginname() {
        return 'category';
    }

    public function instance_settings() {
        return array(
            new instance_setting('category_select', PARAM_INT)
        );
    }

    public function extend_add_instance_form_definition($mform) {
        global $DB;
        $elementname = 'category_select';
        $categories = $DB->get_records('course_categories');
        $categoriestoshow = array();
        foreach ($categories as $category) {
            $categoriestoshow[$category->id] = $category->name;
        }

        $mform->addElement('select', $elementname, get_string($elementname, 'lifecycletrigger_category'), $categoriestoshow);
        $mform->addHelpButton($elementname, $elementname, 'lifecycletrigger_category');
    }

}
