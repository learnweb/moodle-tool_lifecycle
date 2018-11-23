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
     *  Cache for all categories to calculate it only once.
     */
    private $allcategoriescache;

    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param $course object to be processed.
     * @param $triggerid int id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        $categories = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER)['categories'];
        $exclude = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER)['exclude'] && true;

        $courseincategory = $this->course_is_within_categories($course, explode(',', $categories));

        /*
         * I want to trigger the course if ...
         * - the course is within the categories and I don't want to exclude those courses.
         *      $exclude = false
         *      $courseincategory = true
         * - the course is not within the categories and I want to exclude all courses that do.
         *      $exclude = true
         *      $courseincategory = false
         * that is why the unequal is valid here.
         */
        if ($courseincategory !== $exclude) {
            return trigger_response::trigger();
        }
        return trigger_response::next();
    }

    /**
     * Tells if the course is within the listed categories.
     * @param $course object course to be checked.
     * @param $categories int[] of category ids.
     * @return bool
     */
    private function course_is_within_categories($course, $categories) {
        if (!$this->allcategoriescache) {
            $categoryobjects = coursecat::get_many($categories);
            $this->allcategoriescache = array();
            foreach ($categories as $category) {
                array_push($this->allcategoriescache, $category);
                $children = $categoryobjects[$category]->get_all_children_ids();
                $this->allcategoriescache = array_merge($this->allcategoriescache, $children);
            }
        }

        return array_search($course->category, $this->allcategoriescache) !== false;
    }

    public function get_subpluginname() {
        return 'categories';
    }

    public function instance_settings() {
        return array(
            new instance_setting('categories', PARAM_SEQUENCE),
            new instance_setting('exclude', PARAM_BOOL),
        );
    }

    public function extend_add_instance_form_definition($mform) {
        $mform->addElement('text', 'categories', get_string('categories', 'lifecycletrigger_categories'));
        $mform->setType('categories', PARAM_SEQUENCE);
        $mform->addElement('checkbox', 'exclude', get_string('exclude', 'lifecycletrigger_categories'));
        $mform->setType('exclude', PARAM_BOOL);
    }

}
