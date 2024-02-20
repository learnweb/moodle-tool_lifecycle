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
 * Step Subplugin for creating a course backup.
 *
 * @package lifecyclestep_createbackup
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\local\manager\backup_manager;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

global $CFG;

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/controller/backup_controller.class.php');

/**
 * Step Subplugin for creating a course backup.
 *
 * @package lifecyclestep_createbackup
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class createbackup extends libbase {

    /** @var int $numberofbackups Backups created so far in this php call. */
    private static $numberofbackups = 0;

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
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        if (self::$numberofbackups >= settings_manager::get_settings(
                $instanceid, settings_type::STEP)['maximumbackupspercron']) {
            return step_response::waiting(); // Wait with further backups til the next cron run.
        }
        if (backup_manager::create_course_backup($course->id)) {
            self::$numberofbackups++;
            return step_response::proceed();
        }
        return step_response::waiting();
    }

    /**
     * Simply call the process_course since it handles everything necessary for this plugin.
     * @param int $processid
     * @param int $instanceid
     * @param mixed $course
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
        return 'createbackup';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('maximumbackupspercron', PARAM_INT, true),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'maximumbackupspercron';
        $mform->addElement('text', $elementname,
            get_string('maximumbackupspercron', 'lifecyclestep_createbackup'));
        $mform->setType($elementname, PARAM_INT);
        $mform->setDefault($elementname, 10);
    }
}
