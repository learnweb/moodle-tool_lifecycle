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
 * Update script for lifecycles subplugin deletecourse
 *
 * @package tool_lifecycle_step
 * @subpackage createbackup
 * @copyright  2019 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\manager\step_manager;

defined('MOODLE_INTERNAL') || die();

function xmldb_lifecyclestep_createbackup_upgrade($oldversion) {

    if ($oldversion < 2019052900) {

        $coursedeletesteps = step_manager::get_step_instances_by_subpluginname('createbackup');

        $settingsname = 'maximumbackupspercron';
        $settingsvalue = 10;
        foreach ($coursedeletesteps as $step) {
            if (empty(settings_manager::get_settings($step->id, 'step'))) {
                settings_manager::save_settings($step->id, 'step', 'createbackup',
                    array($settingsname => $settingsvalue));
            }
        }

        // Deletecourse savepoint reached.
        upgrade_plugin_savepoint(true, 2019052900, 'lifecyclestep', 'createbackup');
    }

}
