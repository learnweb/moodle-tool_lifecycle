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
 * Interface for the subplugintype step
 * It has to be implemented by all subplugins.
 *
 * @package tool_lifecycle_step
 * @subpackage movecategory
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class movecategory extends libbase {

    /**
     * Processes the course and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     */
    public function process_course($processid, $instanceid, $course) {
        $categoryid = settings_manager::get_settings(
            $instanceid,
            SETTINGS_TYPE_STEP
        )['categorytomoveto'];

        $success = move_courses(
            array($course->id), $categoryid
        );

        if($success) {
            return step_response::proceed();
        } else {
            return step_response::rollback();
        }

    }

    public function instance_settings() {
        return array(
            new instance_setting('categorytomoveto', PARAM_INT),
        );
    }

    public function extend_add_instance_form_definition($mform) {
        global $DB;

        $elementname = 'categorytomoveto';
        $categories = $DB->get_records('course_categories');
        $categoriesToShow = array();
        foreach($categories as $category) {
            $categoriesToShow[$category->id] = $category->name;
        }
        $mform->addElement('select', $elementname, get_string('categorytomoveto', 'lifecyclestep_movecategory'), $categoriesToShow);
//        $mform->addElement('text', $elementname, get_string('categorytomoveto', 'lifecyclestep_movecategory'));
        $mform->addHelpButton($elementname , 'categorytomoveto', 'lifecyclestep_movecategory');
        $mform->setType($elementname, PARAM_INT);
    }

    public function get_subpluginname() {
        return 'movecategory';
    }
}
