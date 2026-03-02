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

use stdClass;
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
     * @param stdClass $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        if ($course->id == SITEID) {
            return step_response::rollback();
        }

        // Get setting maximum deletions per cron. "0" means no limit.
        $maximumdeletionspercron = settings_manager::get_settings(
            $instanceid, settings_type::STEP)['maximumdeletionspercron'] ?? 0;
        $maximumdeletionspercron = $maximumdeletionspercron == 0 ? PHP_INT_MAX : $maximumdeletionspercron;
        if (self::$numberofdeletions >= $maximumdeletionspercron) {
            // Wait with further deletions at least until the next task run.
            return step_response::waiting();
        }

        delete_course($course);

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
     * @param stdClass $course to be processed.
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
            new instance_setting('status', PARAM_INT, true),
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
        // Status (active or deactivated)?
        $elementname = 'status';
        $options = [
            STEPACTIVE => get_string('active', 'tool_lifecycle'),
            STEPSTOPPED => get_string('stopped', 'tool_lifecycle'),
        ];
        $mform->addElement('select', $elementname, get_string('status', 'tool_lifecycle'), $options);
        $mform->addHelpButton($elementname, 'stopped', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_INT);
        $mform->setDefault($elementname, STEPACTIVE);
        // Maximum courses processed by a single task run.
        $elementname = 'maximumdeletionspercron';
        $mform->addElement('text', $elementname,
            get_string('deletecourse_maximumdeletionspercron', 'lifecyclestep_deletecourse'),
            ['size' => 3]);
        $mform->setType($elementname, PARAM_INT);
        $mform->setDefault($elementname, 10);
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'e/delete';
    }

    /**
     * Returns if this step type is stoppable.
     * @return bool
     */
    public function is_stoppable() {
        return true;
    }
}
