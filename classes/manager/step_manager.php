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
 * Manager for Subplugins
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\entity\trigger_subplugin;

defined('MOODLE_INTERNAL') || die();

class step_manager extends subplugin_manager {

    /**
     * Returns a subplugin object.
     * @param int $subpluginid id of the step instance
     * @return step_subplugin
     */
    public static function get_subplugin_by_instance_id($stepinstanceid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_step', array('id' => $stepinstanceid));
        if ($record) {
            $subplugin = step_subplugin::from_record($record);
            return $subplugin;
        } else {
            return null;
        }
    }

    /**
     * Persists a subplugin to the database.
     * @param step_subplugin $subplugin
     */
    public static function insert_or_update(step_subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($subplugin->id) {
            $DB->update_record('tool_cleanupcourses_step', $subplugin);
        } else {
            $subplugin->id = $DB->insert_record('tool_cleanupcourses_step', $subplugin);
            $record = $DB->get_record('tool_cleanupcourses_step', array('id' => $subplugin->id));
            $subplugin = step_subplugin::from_record($record);
        }
        $transaction->allow_commit();
    }

    /**
     * Removes a step instance from the database.
     * @param int $stepinstanceid step instance id
     */
    private static function remove($stepinstanceid) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($record = $DB->get_record('tool_cleanupcourses_step', array('id' => $stepinstanceid))) {

            $othersteps = $DB->get_records('tool_cleanupcourses_step', array('followedby' => $stepinstanceid));
            foreach ($othersteps as $steprecord) {
                $step = step_subplugin::from_record($steprecord);
                $step->followedby = null;
                step_manager::insert_or_update($step);
            }

            $othertrigger = $DB->get_records('tool_cleanupcourses_trigger', array('followedby' => $stepinstanceid));
            foreach ($othertrigger as $triggerrecord) {
                $trigger = trigger_subplugin::from_record($triggerrecord);
                $trigger->followedby = null;
                trigger_manager::insert_or_update($trigger);
            }
            $DB->delete_records('tool_cleanupcourses_step', (array) $record);
        }
        $transaction->allow_commit();
    }

    /**
     * Gets the list of step instances.
     * @return array of step instances.
     */
    public static function get_step_instances() {
        global $DB;
        $records = $DB->get_records('tool_cleanupcourses_step');
        $steps = array();
        foreach ($records as $id => $record) {
            $steps[$id] = $record->instancename;
        }
        return $steps;
    }

    /**
     * Gets a specific step instance.
     * @param int $instanceid id of the instance
     * @return step_subplugin step instance.
     */
    public static function get_step_instance($instanceid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_step', array('id' => $instanceid));
        if ($record) {
            return step_subplugin::from_record($record);
        }
        return null;
    }

    /**
     * Gets the list of step subplugins.
     * @return array of step subplugins.
     */
    public static function get_step_types() {
        $subplugins = \core_component::get_plugin_list('cleanupcoursesstep');
        $result = array();
        foreach ($subplugins as $id => $plugin) {
            $result[$id] = get_string('pluginname', 'cleanupcoursesstep_' . $id);
        }
        return $result;
    }

    /**
     * Changes the followedby of a trigger.
     * @param int $subpluginid id of the trigger
     * @param int $followedby id of the step
     */
    public static function change_followedby($subpluginid, $followedby) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        $step = step_manager::get_subplugin_by_instance_id($subpluginid);
        if (!$step) {
            return; // TODO: Throw error.
        }
        $followedby = step_manager::get_subplugin_by_instance_id($followedby);

        // If step is not defined clear followedby.
        if ($followedby) {
            $step->followedby = $followedby->id;
        } else {
            $step->followedby = null;
        }

        step_manager::insert_or_update($step);

        $transaction->allow_commit();
    }

    /**
     * Handles an action of the subplugin_settings.
     * @param string $action action to be executed
     * @param int $subplugin id of the subplugin
     */
    public static function handle_action($action, $subplugin) {
        if ($action === ACTION_FOLLOWEDBY_STEP) {
            step_manager::change_followedby($subplugin, optional_param('followedby', null, PARAM_INT));
        }
        if ($action === ACTION_STEP_INSTANCE_DELETE) {
            step_manager::remove($subplugin);
        }
    }
}
