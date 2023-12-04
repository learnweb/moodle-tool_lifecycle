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
 * Manager to retrive the local settings for each step subplugin.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/../../../lib.php');

/**
 * Manager to retrive the local settings for each step subplugin.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_manager {

    /**
     * Saves the local settings for a subplugin step instance.
     * @param int $instanceid id of the subplugininstance.
     * @param 'step'|'trigger' $type type of the subplugin.
     * @param string $subpluginname name of the subplugin.
     * @param string $name name of the setting.
     * @param mixed $value value of the setting.
     * @throws \moodle_exception
     */
    public static function save_setting($instanceid, $type, $subpluginname, $name, $value) {
        $data = [$name => $value];
        self::save_settings($instanceid, $type, $subpluginname, $data);
    }

    /**
     * Saves the local settings for a subplugin step instance.
     * @param int $instanceid id of the subplugininstance.
     * @param 'step'|'trigger' $type type of the subplugin.
     * @param string $subpluginname name of the subplugin.
     * @param mixed $data submitted data of the form.
     * @param bool $accessvalidation whether to do only change settings that are editable once the workflow has started.
     *          Then also calls the on_setting_changed listener. Defaults to false.
     * @throws \moodle_exception
     */
    public static function save_settings($instanceid, $type, $subpluginname, $data, $accessvalidation = false) {
        global $DB;
        self::validate_type($type);

        if (!$data) {
            return;
        }

        // Cast data to array.
        if (is_object($data)) {
            $data = (array) $data;
        }

        if ($type == settings_type::TRIGGER) {
            $lib = lib_manager::get_trigger_lib($subpluginname);
            $trigger = trigger_manager::get_instance($instanceid);
            $wfeditable = workflow_manager::is_editable($trigger->workflowid);
        } else {
            $lib = lib_manager::get_step_lib($subpluginname);
            $step = step_manager::get_step_instance($instanceid);
            $wfeditable = workflow_manager::is_editable($step->workflowid);
        }

        $settingsfields = $lib->instance_settings();
        if (!$instanceid) {
            throw new \moodle_exception('id of the step instance has to be set!');
        }
        foreach ($settingsfields as $setting) {
            if ($accessvalidation && !$wfeditable && !$setting->editable) {
                continue;
            }
            if (array_key_exists($setting->name, $data)) {
                $value = $data[$setting->name];
                // Needed for editor support.
                if (is_array($value) && array_key_exists('text', $value)) {
                    $value = $value['text'];
                }
                if (is_array($value)) {
                    // Not sure if this is best practice...
                    $cleanedvalue = implode(
                        ',',
                        clean_param_array($value, $setting->paramtype)
                    );
                } else {
                    $cleanedvalue = clean_param($value, $setting->paramtype);
                }
                $record = $DB->get_record('tool_lifecycle_settings',
                    [
                        'instanceid' => $instanceid,
                        'type' => $type,
                        'name' => $setting->name, ]
                );
                if ($record) {
                    if ($record->value != $cleanedvalue) {
                        $oldvalue = $record->value;
                        $record->value = $cleanedvalue;
                        $DB->update_record('tool_lifecycle_settings', $record);
                        if ($accessvalidation && !$wfeditable) {
                            $lib->on_setting_changed($setting->name, $cleanedvalue, $oldvalue);
                        }
                    }
                } else {
                    $newrecord = new \stdClass();
                    $newrecord->instanceid = $instanceid;
                    $newrecord->name = $setting->name;
                    $newrecord->value = $cleanedvalue;
                    $newrecord->type = $type;
                    $DB->insert_record('tool_lifecycle_settings', $newrecord);
                }
            }
        }
    }

    /**
     * Returns an array of local subplugin settings for a given instance id
     * @param int $instanceid id of the step instance
     * @param string $type Type of the setting (see {@see settings_type}).
     * @return array|null settings key-value pairs
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_settings($instanceid, $type) {
        global $DB;

        self::validate_type($type);

        if ($type == settings_type::TRIGGER) {
            $instance = trigger_manager::get_instance($instanceid);
        } else {
            $instance = step_manager::get_step_instance($instanceid);
        }

        if (!$instance) {
            throw new \coding_exception('The subplugin instance does not exist.');
        }

        if ($type == settings_type::TRIGGER) {
            $lib = lib_manager::get_trigger_lib($instance->subpluginname);
        } else {
            $lib = lib_manager::get_step_lib($instance->subpluginname);
        }

        $settingsvalues = [];
        foreach ($lib->instance_settings() as $setting) {
            $record = $DB->get_record('tool_lifecycle_settings', ['instanceid' => $instanceid,
                    'type' => $type,
                    'name' => $setting->name, ]);
            if ($record) {
                $value = clean_param($record->value, $setting->paramtype);
                $settingsvalues[$setting->name] = $value;
            }
        }
        return $settingsvalues;
    }

    /**
     * Removes all local settings for a given instance id
     * @param int $instanceid id of the step instance
     * @param string $type Type of the setting (see {@see settings_type}).
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function remove_settings($instanceid, $type) {
        global $DB;
        self::validate_type($type);

        $DB->delete_records('tool_lifecycle_settings',
                ['instanceid' => $instanceid,
                    'type' => $type, ]);
    }

    /**
     * Validates the type param for the two possibilities 'step' and 'trigger'.
     * @param string $type Type of the setting (see {@see settings_type}).
     * @throws \coding_exception
     */
    private static function validate_type($type) {
        if ($type !== settings_type::TRIGGER && $type !== settings_type::STEP) {
            throw new \coding_exception('Invalid type value. "step" or "trigger" expected.');
        }
    }

}
