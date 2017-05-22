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

class settings_manager {

    /**
     * Saves the local settings for a subplugin step instance.
     * @param string $subpluginname name of the subplugin.
     * @param mixed $data submitted data of the form.
     * @throws \moodle_exception
     */
    public static function save_settings($subpluginname, $data) {
        global $DB;
        $lib = lib_manager::get_step_lib($subpluginname);

        $settingsfields = $lib->instance_settings();
        if (!object_property_exists($data, 'id')) {
            throw new \moodle_exception('id of the step instance has to be set!');
        }
        $id = $data->id;
        foreach ($settingsfields as $setting) {
            if (object_property_exists($data, $setting->name)) {
                $value = $data->{$setting->name};
                $cleanedvalue = clean_param($value, $setting->paramtype);
                $record = $DB->get_record('tool_cleanupcourses_settings',
                    array(
                        'instanceid' => $id,
                        'name' => $setting->name)
                );
                if ($record) {
                    $record->value = $cleanedvalue;
                    $DB->update_record('tool_cleanupcourses_settings', $record);
                } else {
                    $newrecord = new \stdClass();
                    $newrecord->instanceid = $id;
                    $newrecord->name = $setting->name;
                    $newrecord->value = $cleanedvalue;
                    $DB->insert_record('tool_cleanupcourses_settings', $newrecord);
                }
            }
        }
    }

    /**
     * Returns an array of local step settings for a given instance id
     * @param int $instanceid id of the step instance
     * @return array|null settings key-value pairs
     */
    public static function get_settings($instanceid) {
        global $DB;

        $stepinstance = step_manager::get_step_instance($instanceid);

        if (!$stepinstance) {
            return null;
        }

        $lib = lib_manager::get_step_lib($stepinstance->subpluginname);

        if ($stepinstance->subpluginname !== $lib->get_subpluginname()) {
            return null;
        }

        $settingsvalues = array();
        foreach ($lib->instance_settings() as $setting) {
            $record = $DB->get_record('tool_cleanupcourses_settings',
                array('instanceid' => $instanceid,
                    'name' => $setting->name));
            if ($record) {
                $value = clean_param($record->value, $setting->paramtype);
                $settingsvalues[$setting->name] = $value;
            }
        }
        return $settingsvalues;
    }

}
