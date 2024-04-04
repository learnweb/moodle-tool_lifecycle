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
 * Interface for the subplugintype trigger.
 *
 * It has to be implemented by all subplugins.
 * @package tool_lifecycle
 * @subpackage trigger
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use tool_lifecycle\local\response\trigger_response;

defined('MOODLE_INTERNAL') || die();

/**
 * This class bundles different functions necessary for every trigger of a workflow.
 *
 * This class should not be extended directly. Please use base_manual or base_automatic.
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {

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
     * This method can be overriden, to add additional data validation to the instance form.
     * @param array $error Array containing all errors.
     * @param array $data Data passed from the moodle form to be validated
     */
    public function extend_add_instance_form_validation(&$error, $data) {
    }


    /**
     * If true, the trigger can be used to manually define workflows, based on an instance of this trigger.
     * This has to be combined with installing the workflow in db/install.php of the trigger plugin.
     * If false, at installation the trigger will result in a preset workflow, which can not be changed.
     * This is for instance relevant for the sitecourse trigger or the delayedcourses trigger.
     * @return bool
     */
    public function has_multiple_instances() {
        return true;
    }

    /**
     * Specifies if the trigger is a manual or an automatic trigger.
     * @return boolean
     */
    abstract public function is_manual_trigger();

    /**
     * Returns the status message for the trigger.
     * @return string status message
     * @throws \coding_exception
     */
    public function get_status_message() {
        return get_string("workflow_started", "tool_lifecycle");
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
 * This class represents an automatic trigger.
 *
 * It is used when workflow should be started based on a specific logic.
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_automatic extends base {

    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param object $course Course to be processed.
     * @param int $triggerid Id of the trigger instance.
     * @return trigger_response
     */
    abstract public function check_course($course, $triggerid);

    /**
     * Defines if the trigger subplugin is started manually or automatically.
     * @return bool
     */
    public function is_manual_trigger() {
        return false;
    }

    /**
     * Allows to return a where clause, which reduces the recordset of relevant courses.
     * The return value has to consist of an array with two values. The first one includes the where sql statement,
     * which will be concatenated using an 'AND' to the recordset query (e.g. '{course}.id = $courseid').
     * The second one is the set of parameters for the sql query, which will be merged with other param sets.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     */
    public function get_course_recordset_where($triggerid) {
        return ['', []];
    }
}

/**
 * This class represents a manual trigger.
 *
 * It is used to enable user to manually start processes for workflows.
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_manual extends base {

    /**
     * Defines if the trigger subplugin is started manually or automatically.
     * @return bool
     */
    public function is_manual_trigger() {
        return true;
    }
}

/**
 * Class representing a local settings object for a subplugin instance.
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
