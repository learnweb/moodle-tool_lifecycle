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
 * Step subplugin to delete a course.
 *
 * @package    lifecyclestep_deletecourse
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Step subplugin to delete a course.
 *
 * @package    lifecyclestep_deletecourse
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deletecourse extends libbase {

    /** @var int $numberofdeletions Deletions done so far in this php call. */
    private static $numberofdeletions = 0;

    /**
     * Processes the course and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param object $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        global $CFG;

        if ($course->id == 1) {
            return step_response::rollback();
        }

        if (self::$numberofdeletions >= settings_manager::get_settings(
            $instanceid, settings_type::STEP)['maximumdeletionspercron']) {
            return step_response::waiting(); // Wait with further deletions til the next cron run.
        }

        delete_course($course);

        /* Fix 'delete & backup (other) course aftwerwards' error, which is created by moodle core issue
           MDL-65228 (https://tracker.moodle.org/browse/MDL-65228) */
        if (is_object($CFG) && property_exists($CFG, "forced_plugin_settings") && is_array($CFG->forced_plugin_settings)
                && array_key_exists("backup", $CFG->forced_plugin_settings) && !is_array($CFG->forced_plugin_settings["backup"])) {
            $CFG->forced_plugin_settings["backup"] = [];
        }

        self::$numberofdeletions++;
        return step_response::proceed();
    }

    /**
     * Processes the course in status waiting and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param int $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        return $this->process_course($processid, $instanceid, $course);
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'deletecourse';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('maximumdeletionspercron', PARAM_INT, true),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'maximumdeletionspercron';
        $mform->addElement('text', $elementname, get_string('deletecourse_maximumdeletionspercron', 'lifecyclestep_deletecourse'));
        $mform->setType($elementname, PARAM_INT);
        $mform->setDefault($elementname, 10);
    }
}
