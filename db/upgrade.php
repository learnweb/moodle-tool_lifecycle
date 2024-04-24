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
 * Update script for lifecycle plugin
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Fix any gaps in the workflows sortindex.
 */
function tool_lifecycle_fix_workflow_sortindex() {
    $workflows = \tool_lifecycle\local\manager\workflow_manager::get_active_workflows();
    for ($i = 1; $i <= count($workflows); $i++) {
        $workflow = $workflows[$i - 1];
        if ($workflow->sortindex != $i) {
            $workflow->sortindex = $i;
            \tool_lifecycle\local\manager\workflow_manager::insert_or_update($workflow);
        }
    }
}

/**
 * Update script for tool_lifecycle.
 * @param int $oldversion Version id of the previously installed version.
 * @return bool
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_tool_lifecycle_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017081101) {

        // Create table tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_workflow');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'title');
        $table->add_field('timeactive', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'active');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally create the table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Changing structure of table tool_lifecycle_step.
        $table = new xmldb_table('tool_lifecycle_step');
        $field = new xmldb_field('followedby');

        // Conditionally drop followedby field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'subpluginname');
        $key = new xmldb_key('workflowid_fk', XMLDB_KEY_FOREIGN, ['workflowid'], 'tool_lifecycle_workflow', ['id']);

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

        // Changing structure of table tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $field = new xmldb_field('followedby');

        // Conditionally drop followedby field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Add workflowfield to trigger.
        $field = new xmldb_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'enabled');
        $key = new xmldb_key('workflowid_fk', XMLDB_KEY_FOREIGN, ['workflowid'], 'tool_lifecycle_workflow', ['id']);

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        // Changing structure of table tool_lifecycle_process.
        $table = new xmldb_table('tool_lifecycle_process');
        $field = new xmldb_field('stepid');

        // Conditionally drop followedby field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'courseid');
        $key = new xmldb_key('workflowid_fk', XMLDB_KEY_FOREIGN, ['workflowid'], 'tool_lifecycle_workflow', ['id']);

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $dbman->add_key($table, $key);
        }

        $field = new xmldb_field('stepindex', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, null, 'workflowid');

        // Conditionally create the field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2017081101, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018021300) {

        // Define field sortindex to be added to tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_workflow');
        $field = new xmldb_field('sortindex', XMLDB_TYPE_INTEGER, '3', null, null, null, null, 'timeactive');

        // Conditionally launch add field sortindex.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018021300, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018021301) {

        // Define field type to be added to tool_lifecycle_settings.
        $table = new xmldb_table('tool_lifecycle_settings');
        $field = new xmldb_field('type', XMLDB_TYPE_CHAR, '7', null, XMLDB_NOTNULL, null, null, 'instanceid');

        // Conditionally launch add field type.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute('update {tool_lifecycle_settings} set type = \'step\'');

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018021301, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018021302) {

        // Define field workflowid to be added to tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $field = new xmldb_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'subpluginname');

        // Conditionally launch add field workflowid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field instancename to be added to tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $field = new xmldb_field('instancename', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'workflowid');

        // Conditionally launch add field instancename.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field enabled to be dropped from tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $field = new xmldb_field('enabled');

        // Conditionally launch drop field enabled.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field sortindex to be dropped from tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $field = new xmldb_field('sortindex');

        // Conditionally launch drop field sortindex.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field sortindex to be dropped from tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $field = new xmldb_field('sortindex');

        // Conditionally launch drop field sortindex.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define key workflowid_fk (foreign) to be added to tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $key = new xmldb_key('workflowid_fk', XMLDB_KEY_FOREIGN, ['workflowid'], 'tool_lifecycle_workflow', ['id']);

        // Launch add key workflowid_fk.
        $dbman->add_key($table, $key);

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018021302, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018022001) {

        // Define field manual to be added to tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_workflow');
        $field = new xmldb_field('manual', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'sortindex');

        // Conditionally launch add field manual.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018022001, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018022002) {

        // Define field manual to be added to tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_procdata');
        $field = new xmldb_field('key', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'subpluginname');

        // Launch rename field key.
        $dbman->rename_field($table, $field, 'keyname');

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018022002, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018022005) {
        $workflows = \tool_lifecycle\local\manager\workflow_manager::get_active_workflows();
        foreach ($workflows as $workflow) {
            if ($workflow->manual === null) {
                $trigger = \tool_lifecycle\local\manager\trigger_manager::get_triggers_for_workflow($workflow->id)[0];
                $lib = \tool_lifecycle\local\manager\lib_manager::get_trigger_lib($trigger->subpluginname);
                $workflow->manual = $lib->is_manual_trigger();
                \tool_lifecycle\local\manager\workflow_manager::insert_or_update($workflow);
            }
        }
        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018022005, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018022101) {

        // Define key courseid_fk (foreign) to be dropped form tool_lifecycle_process.
        $table = new xmldb_table('tool_lifecycle_process');
        $key = new xmldb_key('courseid_fk', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        // Launch drop key courseid_fk.
        $dbman->drop_key($table, $key);

        // Define key courseid_fk (foreign-unique) to be added to tool_lifecycle_process.
        $key = new xmldb_key('courseid_fk', XMLDB_KEY_FOREIGN_UNIQUE, ['courseid'], 'course', ['id']);

        // Launch add key courseid_fk.
        $dbman->add_key($table, $key);

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018022101, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018022102) {

        // Define field displaytitle to be added to tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_workflow');
        $field = new xmldb_field('displaytitle', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'manual');

        // Conditionally launch add field displaytitle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018022102, 'tool', 'lifecycle');
    }

    if ($oldversion < 2018101000) {

        // Define field sortindex to be added to tool_lifecycle_trigger.
        $table = new xmldb_table('tool_lifecycle_trigger');
        $field = new xmldb_field('sortindex', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, 1, 'workflowid');

        // Conditionally launch add field sortindex.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2018101000, 'tool', 'lifecycle');
    }

    if ($oldversion < 2019010102) {
        // Define field timedeactive to be added to tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_workflow');
        $field = new xmldb_field('timedeactive', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timeactive');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2019010102, 'tool', 'lifecycle');
    }

    if ($oldversion < 2019053100) {

        // Define field active to be dropped from tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_workflow');
        $field = new xmldb_field('active');

        // Conditionally launch drop field active.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2019053100, 'tool', 'lifecycle');
    }

    if ($oldversion < 2019082200) {

        $duration = get_config(null, 'lifecycle_duration');

        // Define field rollbackdelay to be added to tool_lifecycle_workflow.
        $table = new xmldb_table('tool_lifecycle_workflow');
        $field = new xmldb_field('rollbackdelay', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'displaytitle');

        // Conditionally launch add field rollbackdelay.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field finishdelay to be added to tool_lifecycle_workflow.
        $field = new xmldb_field('finishdelay', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'rollbackdelay');

        // Conditionally launch add field finishdelay.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field delayforallworkflows to be added to tool_lifecycle_workflow.
        $field = new xmldb_field('delayforallworkflows', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'finishdelay');

        // Conditionally launch add field delayforallworkflows.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $params = [$duration, $duration];

        $DB->execute('UPDATE {tool_lifecycle_workflow} SET finishdelay = ?, rollbackdelay = ?', $params);

        // Define table tool_lifecycle_delayed_workf to be created.
        $table = new xmldb_table('tool_lifecycle_delayed_workf');

        // Adding fields to table tool_lifecycle_delayed_workf.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('delayeduntil', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table tool_lifecycle_delayed_workf.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('workflowid_fk', XMLDB_KEY_FOREIGN, ['workflowid'], 'tool_lifecycle_workflow', ['id']);

        // Conditionally launch create table for tool_lifecycle_delayed_workf.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table tool_lifecycle_action_log to be created.
        $table = new xmldb_table('tool_lifecycle_action_log');

        // Adding fields to table tool_lifecycle_action_log.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('processid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('stepindex', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('action', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table tool_lifecycle_action_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('processid_fk', XMLDB_KEY_FOREIGN, ['processid'], 'tool_lifecycle_process', ['id']);
        $table->add_key('workflowid_fk', XMLDB_KEY_FOREIGN, ['workflowid'], 'tool_lifecycle_workflow', ['id']);
        $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Conditionally launch create table for tool_lifecycle_action_log.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lifecycle savepoint reached.

        upgrade_plugin_savepoint(true, 2019082200, 'tool', 'lifecycle');
    }

    if ($oldversion < 2019082300) {

        $duration = get_config(null, 'lifecycle_duration');

        unset_config('lifecycle_duration');

        set_config('duration', $duration, 'tool_lifecycle');

        // Lifecycle savepoint reached.

        upgrade_plugin_savepoint(true, 2019082300, 'tool', 'lifecycle');
    }

    if ($oldversion < 2020091800) {
        $sql = "SELECT p.* FROM {tool_lifecycle_process} p " .
                "LEFT JOIN {course} c ON p.courseid = c.id " .
                "WHERE c.id IS NULL";
        $processes = $DB->get_records_sql($sql);
        foreach ($processes as $procrecord) {
            $process = \tool_lifecycle\local\entity\process::from_record($procrecord);
            \tool_lifecycle\local\manager\process_manager::abort_process($process);
        }

        upgrade_plugin_savepoint(true, 2020091800, 'tool', 'lifecycle');
    }

    if ($oldversion < 2021112300) {

        // Define table tool_lifecycle_proc_error to be created.
        $table = new xmldb_table('tool_lifecycle_proc_error');

        // Adding fields to table tool_lifecycle_proc_error.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('workflowid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('stepindex', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('waiting', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestepchanged', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('errormessage', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('errortrace', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('errorhash', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('errortimecreated', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table tool_lifecycle_proc_error.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN_UNIQUE, ['courseid'], 'course', ['id']);
        $table->add_key('workflowid_fk', XMLDB_KEY_FOREIGN, ['workflowid'], 'tool_lifecycle_workflow', ['id']);

        // Conditionally launch create table for tool_lifecycle_proc_error.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2021112300, 'tool', 'lifecycle');
    }

    if ($oldversion < 2024042300) {

        tool_lifecycle_fix_workflow_sortindex();

        // Lifecycle savepoint reached.
        upgrade_plugin_savepoint(true, 2024042300, 'tool', 'lifecycle');

    }

    return true;
}
