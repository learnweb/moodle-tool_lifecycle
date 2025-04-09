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
 * Update script for lifecyclestep_adminapprove plugin
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2025 Thomas Niedermaier University of Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Update script for lifecyclestep_adminapprove.
 * @param int $oldversion Version id of the previously installed version.
 * @return bool
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_lifecyclestep_adminapprove_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2025040600) {

        // Define table lifecyclestep_adminapprove to be created.
        $table = new xmldb_table('lifecyclestep_adminapprove');

        // Adding fields to table lifecyclestep_adminapprove.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('processid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table lifecyclestep_adminapprove.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('processid_fk-u', XMLDB_KEY_FOREIGN_UNIQUE, ['processid'], 'tool_lifecycle_process', ['id']);

        // Conditionally launch create table for lifecyclestep_adminapprove.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Adminapprove savepoint reached.
        upgrade_plugin_savepoint(true, 2025040600, 'lifecyclestep', 'adminapprove');
    }
    return true;
}
