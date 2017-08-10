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

    if ($oldversion < 2017081000) {

        // Create table tool_cleanupcourses_wf_steps.
        $table = new xmldb_table('tool_cleanupcourses_wf_steps');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('stepid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'workflowid');
        $table->add_field('sortindex', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, null, 'stepid');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('workflowid_fk', XMLDB_KEY_FOREIGN, array('workflowid'), 'tool_cleanupcourses_workflow', array('id'));
        $table->add_key('stepid_fk', XMLDB_KEY_FOREIGN, array('stepid'), 'tool_cleanupcourses_step', array('id'));

        // Conditionally launch add field id.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Create table tool_cleanupcourses_workflow.
        $table = new xmldb_table('tool_cleanupcourses_workflow');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'title');
        $table->add_field('timeacive', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'active');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch add field id.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Changing precision of field followedby on table tool_cleanupcourses_step to (10).
        $table = new xmldb_table('tool_cleanupcourses_step');
        $field = new xmldb_field('followedby', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'subpluginname');

        // Launch change of precision for field followedby.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field followedby on table tool_cleanupcourses_trigger to (10).
        $table = new xmldb_table('tool_cleanupcourses_trigger');
        $field = new xmldb_field('followedby', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'enabled');

        // Launch change of precision for field followedby.
        $dbman->change_field_precision($table, $field);

        // Cleanupcourses savepoint reached.
        upgrade_plugin_savepoint(true, 2017081000, 'tool', 'cleanupcourses');
    }

    return true;
}