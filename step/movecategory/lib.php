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
 * @package lifecyclestep_movecategory
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\step;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class movecategory extends libbase {

    /**
     * Processes the course and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     *
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        $categoryid = settings_manager::get_settings(
            $instanceid,
            settings_type::STEP
        )['categorytomoveto'];

        $success = move_courses(
            array($course->id), $categoryid
        );

        if ($success) {
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


        //Fetch a complete list of courses and let it be shown in a flat hierachical view with all parent branches
        $displaylist = \core_course_category::make_categories_list('moodle/course:changecategory');

        $mform->addElement('autocomplete', $elementname, get_string('categorytomoveto', 'lifecyclestep_movecategory'), $displaylist);
        $mform->addHelpButton($elementname, 'categorytomoveto', 'lifecyclestep_movecategory');
        $mform->setType($elementname, PARAM_INT);
    }

    public function get_subpluginname() {
        return 'movecategory';
    }
}
