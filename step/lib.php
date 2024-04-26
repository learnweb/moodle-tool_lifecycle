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
 * Interface for the subplugintype step.
 *
 * It has to be implemented by all subplugins.
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\local\entity\process;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\response\step_response;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for the subplugintype step.
 *
 * It has to be implemented by all subplugins.
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class libbase {

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
    abstract public function process_course($processid, $instanceid, $course);

    /**
     * Processes the course in status waiting and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        throw new \coding_exception("Processing of waiting courses is not supported for this workflow step.");
    }

    /**
     * Can be overridden to define actions to take place before
     * process_course() is called for every relevant course.
     */
    public function pre_processing_bulk_operation() {
    }

    /**
     * Can be overridden to define actions to take place after
     * process_course() is called for every relevant course.
     */
    public function post_processing_bulk_operation() {
    }

    /**
     * Rolls back all changes made to the course
     *
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be rolled back.
     */
    public function rollback_course($processid, $instanceid, $course) {

    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    abstract public function get_subpluginname();

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [];
    }

    /**
     * Is called when a setting is changed after a workflow is activated.
     * @param string $settingname name of the setting
     * @param mixed $newvalue the new value
     * @param mixed $oldvalue the old value
     */
    public function on_setting_changed($settingname, $newvalue, $oldvalue) {

    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     */
    public function extend_add_instance_form_definition($mform) {
    }

    /**
     * This method can be overriden, to set default values to the form_step_instance.
     * It is called in definition_after_data().
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
    }

    /**
     * This method can be overridden. It is called when a course and the
     * corresponding process get deleted.
     * @param process $process the process that was aborted.
     */
    public function abort_course($process) {
    }


    /**
     * Ensure validity of settings upon backup restoration.
     * @param array $settings
     * @return array List of errors with settings. If empty, the given settings are valid.
     */
    public function ensure_validity(array $settings): array {
        return [];
    }

}

/**
 * Class representing a local settings object for a subplugin instance.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance_setting {

    /** @var string name of the setting*/
    public $name;

    /** @var string param type of the setting, e.g. PARAM_INT */
    public $paramtype;

    /** @var bool if editable after activation */
    public $editable;

    /**
     * Create a local settings object.
     * @param string $name name of the setting
     * @param string $paramtype param type. Used for cleansing and parsing, e.g. PARAM_INT.
     * @param bool $editable if setting is editable after activation
     */
    public function __construct(string $name, string $paramtype, bool $editable = false) {
        $this->name = $name;
        $this->paramtype = $paramtype;
        $this->editable = $editable;
    }

}
