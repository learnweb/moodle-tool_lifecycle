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
 * @package lifecyclestep_deletecourse
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;

/**
 * Update script for lifecycles subplugin deletecourse
 *
 * @package lifecyclestep_deletecourse
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param int $oldversion Version id of the previously installed version.
 * @throws coding_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_lifecyclestep_deletecourse_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018122300) {

        $coursedeletesteps = step_manager::get_step_instances_by_subpluginname('deletecourse');

        $settingsname = 'maximumdeletionspercron';
        $settingsvalue = 10;
        foreach ($coursedeletesteps as $step) {
            if (empty(settings_manager::get_settings($step->id, 'step'))) {
                settings_manager::save_settings($step->id, 'step', 'deletecourse',
                    [$settingsname => $settingsvalue]);
            }
        }

        // Deletecourse savepoint reached.
        upgrade_plugin_savepoint(true, 2018122300, 'lifecyclestep', 'deletecourse');
    }

    if ($oldversion < 2026012003) {

        // Define table lifecyclestep_deletecourse to be created.
        $table = new xmldb_table('lifecyclestep_deletecourse');

        // Adding fields to table lifecyclestep_deletecourse.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('stepid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modules', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('participants', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table lifecyclestep_deletecourse.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for lifecyclestep_deletecourse.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Deletecourse savepoint reached.
        upgrade_plugin_savepoint(true, 2026012003, 'lifecyclestep', 'deletecourse');
    }


    return true;
}
