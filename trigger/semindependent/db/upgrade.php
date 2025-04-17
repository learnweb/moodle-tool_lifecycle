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
 * Update script for lifecycletrigger semindependent plugin
 *
 * @package lifecycletrigger_semindependent
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\settings_type;

/**
 * Update script for lifecycletrigger semindependent.
 * @param int $oldversion Version id of the previously installed version.
 * @return bool
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_lifecycletrigger_semindependent_upgrade($oldversion) {

    global $DB;

    if ($oldversion < 2025041600) {

        if ($instances = $DB->get_records('tool_lifecycle_trigger', ['subpluginname' => 'semindependent'])) {
            // For each existing semindependent instance.
            foreach ($instances as $instance) {
                // Is there an include setting.
                if ($setting = $DB->get_record('tool_lifecycle_settings',
                    ['instanceid' => $instance->id, 'name' => 'include'])) {
                    // If include is 0 write an exclude setting of 1.
                    if ($setting->value == 0) {
                        settings_manager::save_setting($instance->id, settings_type::TRIGGER, 'semindependent', 'exclude', 1);
                    } else { // If include setting is 1 write an exclude setting of 0.
                        settings_manager::save_setting($instance->id, settings_type::TRIGGER, 'semindependent', 'exclude', 0);
                    }
                } else { // No include setting for existing semindependent instance - write exclude setting of 1.
                    settings_manager::save_setting($instance->id, settings_type::TRIGGER, 'semindependent', 'exclude', 1);
                }
            }
        }
        upgrade_plugin_savepoint(true, 2025041600, 'lifecycletrigger', 'semindependent');

    }

    return true;
}
