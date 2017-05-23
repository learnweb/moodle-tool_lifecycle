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
 * @package tool_cleanupcourses_step
 * @subpackage email
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\step;

use tool_cleanupcourses\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class email extends base {


    /**
     * Processes the course and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     */
    public function process_course($instanceid, $course) {
        return step_response::waiting();
    }

    public function instance_settings() {
        return array(
            new instance_setting('subject', PARAM_TEXT),
            new instance_setting('content', PARAM_TEXT),
        );
    }

    public function get_subpluginname() {
        return 'email';
    }

    public function extend_add_instance_form_definition($mform) {
        $elementname = 'subject';
        $mform->addElement('text', $elementname, get_string('email_subject', 'cleanupcoursesstep_email'));
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'content';
        $mform->addElement('textarea', $elementname, get_string('email_content', 'cleanupcoursesstep_email'));
        $mform->setType($elementname, PARAM_TEXT);
    }
}
