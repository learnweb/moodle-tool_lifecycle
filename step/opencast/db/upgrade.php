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
 * Update script for lifecycles subplugin opencast
 *
 * @package     lifecyclestep_opencast
 * @copyright   2026 Farbod Zamani Boroujeni, elan e.V.
 * @author      Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_opencast\local\settings_api;

defined('MOODLE_INTERNAL') || die();

// Require the necessary constants.
require_once(__DIR__ . '/../lib.php');

/**
 * Upgrade script for lifecycles subplugin opencast.
 * @param int $oldversion Version id of the previously installed version.
 * @throws coding_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_lifecyclestep_opencast_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026031700) {
        $tags = get_config('lifecyclestep_opencast', 'workflowtags');
        if (empty($tags)) {
            $tags = \tool_lifecycle\step\opencast::DEFAULT_OPENCAST_WORKFLOW_TAGS;
        }
        $ratelimiter = get_config('lifecyclestep_opencast', 'ratelimiter');
        if (empty($ratelimiter)) {
            $ratelimiter = LIFECYCLESTEP_OPENCAST_SELECT_NO;
        }

        // Get the configured OC instances.
        $ocinstances = settings_api::get_ocinstances();
        $newsinstanceetting = [];
        // Iterate over the instances.
        foreach ($ocinstances as $instance) {
            $newsinstanceetting["ocworkflowtags{$instance->id}"] = $tags;
        }

        $opencaststepinstances = step_manager::get_step_instances_by_subpluginname('opencast');
        foreach ($opencaststepinstances as $step) {
            $newsinstanceetting['ocratelimiter'] = $ratelimiter;

            foreach ($newsinstanceetting as $settingsname => $settingsvalue) {
                settings_manager::save_settings(
                    $step->id,
                    'step',
                    'opencast',
                    [$settingsname => $settingsvalue]
                );
            }
        }

        // Now we remove the configs.
        unset_config('workflowtags', 'lifecyclestep_opencast');
        unset_config('ratelimiter', 'lifecyclestep_opencast');

        // Opencast step savepoint reached.
        upgrade_plugin_savepoint(true, 2026031700, 'lifecyclestep', 'opencast');
    }

    if ($oldversion < 2026040700) {
        // Define table lifecyclestep_opencast_process_status to be created.
        $table = new xmldb_table('lifecyclestep_opencast_process_status');

        // Adding fields to table lifecyclestep_opencast_process_status.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('processid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('stepid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('decision', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table lifecyclestep_opencast_process_status.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for lifecyclestep_opencast_process_status.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Opencast savepoint reached.
        upgrade_plugin_savepoint(true, 2026040700, 'lifecyclestep', 'opencast');
    }

    return true;
}
