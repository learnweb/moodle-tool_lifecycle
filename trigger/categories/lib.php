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
 * @subpackage categories
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use coursecat;
use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\response\trigger_response;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package tool_lifecycle_trigger
 */
class categories extends base_automatic {

    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param $course object to be processed.
     * @param $triggerid int id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Every decision is already in the where statement.
        return trigger_response::trigger();
    }

    public function get_course_recordset_where($triggerid) {
        global $DB, $CFG;
        $categories = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER)['categories'];
        $exclude = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER)['exclude'] && true;

        $categories = explode(',', $categories);
        require_once($CFG->libdir . '/coursecatlib.php');
        $categoryobjects = coursecat::get_many($categories);
        $allcategories = array();
        foreach ($categories as $category) {
            array_push($allcategories , $category);
            $children = $categoryobjects[$category]->get_all_children_ids();
            $allcategories  = array_merge($allcategories , $children);
        }

        list($insql, $inparams) = $DB->get_in_or_equal($allcategories, SQL_PARAMS_NAMED);

        $where = "{course}.category {$insql}";
        if ($exclude) {
            $where = "NOT " . $where;
        }

        return array($where, $inparams);
    }

    public function get_subpluginname() {
        return 'categories';
    }

    public function instance_settings() {
        return array(
            new instance_setting('categories', PARAM_INT),
            new instance_setting('exclude', PARAM_BOOL),
        );
    }

    public function extend_add_instance_form_definition($mform) {
        global $DB;
        $categories = $DB->get_records('course_categories');
        $categorynames = array();
        foreach ($categories as $category) {
            $categorynames[$category->id] = $category->name;
        }
        $options = array(
            'multiple' => true,
            'noselectionstring' => 'lol lol lol',
        );
        $mform->addElement('autocomplete', 'categories', get_string('categories', 'lifecycletrigger_categories'), $categorynames, $options);
//        $mform->setType('categories', PARAM_INT);

        $mform->addElement('advcheckbox', 'exclude', get_string('exclude', 'lifecycletrigger_categories'));
        $mform->setType('exclude', PARAM_BOOL);
    }

}
