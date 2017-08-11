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
 * Update script for course cleanup
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_tool_cleanupcourses_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017081101) {

        // Create table tool_cleanupcourses_workflow.
        $table = new xmldb_table('tool_cleanupcourses_workflow');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'title');
        $table->add_field('timeacive', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'active');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally create the table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Changing structure of table tool_cleanupcourses_step.
        $table = new xmldb_table('tool_cleanupcourses_step');
        $field = new xmldb_field('followedby');

        // Conditionally drop followedby field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'subpluginname');
        $key = new xmldb_key('workflowid_fk', XMLDB_KEY_FOREIGN, array('workflowid'), 'tool_cleanupcourses_workflow', array('id'));

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        $field = new xmldb_field('sortindex', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, null, 'workflowid');

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Changing structure of table tool_cleanupcourses_trigger.
        $table = new xmldb_table('tool_cleanupcourses_trigger');
        $field = new xmldb_field('followedby');

        // Conditionally drop followedby field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Add workflowfield to trigger.
        $field = new xmldb_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'enabled');
        $key = new xmldb_key('workflowid_fk', XMLDB_KEY_FOREIGN, array('workflowid'), 'tool_cleanupcourses_workflow', array('id'));

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        // Changing structure of table tool_cleanupcourses_process.
        $table = new xmldb_table('tool_cleanupcourses_process');
        $field = new xmldb_field('stepid');

        // Conditionally drop followedby field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'courseid');
        $key = new xmldb_key('workflowid_fk', XMLDB_KEY_FOREIGN, array('workflowid'), 'tool_cleanupcourses_workflow', array('id'));

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        $field = new xmldb_field('sortindex', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, null, 'workflowid');

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Cleanupcourses savepoint reached.
        upgrade_plugin_savepoint(true, 2017081101, 'tool', 'cleanupcourses');
    }

    return true;
}