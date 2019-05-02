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
 * @subpackage deletecourse
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class deletecourse extends libbase {

    private static $numberofdeletions = 0;

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
        if (self::$numberofdeletions >= settings_manager::get_settings(
            $instanceid, SETTINGS_TYPE_STEP)['maximumdeletionspercron']) {
            return step_response::waiting(); // Wait with further deletions til the next cron run.
        }
        delete_course($course->id, true);
        self::$numberofdeletions++;
        return step_response::proceed();
    }

    public function get_subpluginname() {
        return 'deletecourse';
    }

    public function instance_settings() {
        return array(
            new instance_setting('maximumdeletionspercron', PARAM_INT),
        );
    }

    public function extend_add_instance_form_definition($mform) {
        $elementname = 'maximumdeletionspercron';
        $mform->addElement('text', $elementname, get_string('deletecourse_maximumdeletionspercron', 'lifecyclestep_deletecourse'));
        $mform->setType($elementname, PARAM_INT);
        $mform->setDefault($elementname, 10);
    }
}
