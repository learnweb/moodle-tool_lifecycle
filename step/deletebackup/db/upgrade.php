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
 * Update script for lifecycles subplugin deletebackup
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;

/**
 * Update script for lifecycles subplugin deletebackup
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param int $oldversion Version id of the previously installed version.
 * @throws coding_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_lifecyclestep_deletebackup_upgrade($oldversion) {
    // Check step's version
    if ($oldversion < 2024101600) {
        /* Example: How to add new settings to all old/stored workflows

        // Define new setting name & default value
        $settingsname = 'maximumdeletionspercron';
        $settingsvalue = 10;

        // Get step instances
        $coursedeletesteps = step_manager::get_step_instances_by_subpluginname('deletebackup');
        foreach ($coursedeletesteps as $step) {
            // If settings are empty
            if (empty(settings_manager::get_settings($step->id, 'step'))) {
                // Add setting: maximumdeletionspercron with default value
                settings_manager::save_settings($step->id, 'step', 'deletebackup', [$settingsname => $settingsvalue]);
            }
        }

        // Deletebackup savepoint reached.
        upgrade_plugin_savepoint(true, 2024101600, 'lifecyclestep', 'deletebackup');
        */
    }
}
