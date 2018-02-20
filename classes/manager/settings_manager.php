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
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

defined('MOODLE_INTERNAL') || die();
require_once (__DIR__.'/../../lib.php');

class settings_manager {

    /**
     * Saves the local settings for a subplugin step instance.
     * @param int $instanceid id of the subplugininstance.
     * @param 'step'|'trigger' $type type of the subplugin.
     * @param string $subpluginname name of the subplugin.
     * @param mixed $data submitted data of the form.
     * @throws \moodle_exception
     */
    public static function save_settings($instanceid, $type, $subpluginname, $data) {
        global $DB;
        self::validate_type($type);

        if (!$data) {
            return;
        }

        // Cast data to array.
        if (is_object($data)) {
            $data = (array) $data;
        }

        if ($type == SETTINGS_TYPE_TRIGGER) {
            $lib = lib_manager::get_trigger_lib($subpluginname);
        } else {
            $lib = lib_manager::get_step_lib($subpluginname);
        }

        $settingsfields = $lib->instance_settings();
        if (!$instanceid) {
            throw new \moodle_exception('id of the step instance has to be set!');
        }
        foreach ($settingsfields as $setting) {
            if (array_key_exists($setting->name, $data)) {
                $value = $data[$setting->name];
                // Needed for editor support.
                if (is_array($value) && array_key_exists('text', $value)) {
                    $value = $value['text'];
                }
                $cleanedvalue = clean_param($value, $setting->paramtype);
                $record = $DB->get_record('tool_cleanupcourses_settings',
                    array(
                        'instanceid' => $instanceid,
                        'type' => $type,
                        'name' => $setting->name)
                );
                if ($record) {
                    $record->value = $cleanedvalue;
                    $DB->update_record('tool_cleanupcourses_settings', $record);
                } else {
                    $newrecord = new \stdClass();
                    $newrecord->instanceid = $instanceid;
                    $newrecord->name = $setting->name;
                    $newrecord->value = $cleanedvalue;
                    $newrecord->type = $type;
                    $DB->insert_record('tool_cleanupcourses_settings', $newrecord);
                }
            }
        }
    }

    /**
     * Returns an array of local subplugin settings for a given instance id
     * @param int $instanceid id of the step instance
     * @param 'step'|'trigger' $type type of the subplugin.
     * @return array|null settings key-value pairs
     */
    public static function get_settings($instanceid, $type) {
        global $DB;

        self::validate_type($type);

        if ($type == SETTINGS_TYPE_TRIGGER) {
            $instance = trigger_manager::get_instance($instanceid);
        } else {
            $instance = step_manager::get_step_instance($instanceid);
        }

        if (!$instance) {
            throw new \coding_exception('The subplugin instance does not exist.');
        }

        if ($type == SETTINGS_TYPE_TRIGGER) {
            $lib = lib_manager::get_trigger_lib($instance->subpluginname);
        } else {
            $lib = lib_manager::get_step_lib($instance->subpluginname);
        }

        $settingsvalues = array();
        foreach ($lib->instance_settings() as $setting) {
            $record = $DB->get_record('tool_cleanupcourses_settings',
                array('instanceid' => $instanceid,
                    'type' => $type,
                    'name' => $setting->name));
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
     * @param 'step'|'trigger' $type type of the subplugin.
     */
    public static function remove_settings($instanceid, $type) {
        global $DB;
        self::validate_type($type);

        $DB->delete_records('tool_cleanupcourses_settings',
                array('instanceid' => $instanceid,
                    'type' => $type));
    }

    /**
     * Validates the type param for the two possibilities 'step' and 'trigger'.
     * @param $type string type subplugin the settings should be saved, queried or removed.
     * @throws \coding_exception
     */
    private static function validate_type($type) {
        if ($type !== SETTINGS_TYPE_TRIGGER && $type !== SETTINGS_TYPE_STEP) {
            throw new \coding_exception('Invalid type value. "step" or "trigger" expected.');
        }
    }

}
