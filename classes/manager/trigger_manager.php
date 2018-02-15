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
 * Manager for Trigger subplugins
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

use tool_cleanupcourses\entity\trigger_subplugin;
use tool_cleanupcourses\entity\workflow;

defined('MOODLE_INTERNAL') || die();

class trigger_manager extends subplugin_manager {

    /**
     * Creates a preset workflow for the trigger subplugin.
     * @param $subpluginname string name of the trigger subplugin.
     */
    public static function register_workflow($subpluginname) {
        $workflow = workflow_manager::create_workflow(
            get_string('pluginname', 'cleanupcoursestrigger_' . $subpluginname));
        $record = new \stdClass();
        $record->subpluginname = $subpluginname;
        $record->instancename = get_string('pluginname', 'cleanupcoursestrigger_' . $subpluginname);
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        self::insert_or_update($trigger);
        workflow_manager::activate_workflow($workflow->id);
    }

    /**
     * Returns a subplugin object.
     * @param int $subpluginid id of the subplugin
     * @return trigger_subplugin
     */
    public static function get_instance($instanceid) {
        return self::get_subplugin_by_id($instanceid);
    }

    /**
     * Returns all instances for a trigger subplugin.
     * @param string $subpluginname name of the subplugin
     * @return trigger_subplugin[]
     */
    public static function get_instances($subpluginname) {
        global $DB;
        $result = array();
        $records = $DB->get_records('tool_cleanupcourses_trigger', array('subpluginname' => $subpluginname));
        foreach ($records as $record) {
            $subplugin = trigger_subplugin::from_record($record);
            $result [] = $subplugin;
        }
        return $result;
    }

    /**
     * Returns a subplugin object.
     * @param int $subpluginid id of the subplugin
     * @return trigger_subplugin
     */
    private static function get_subplugin_by_id($subpluginid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('id' => $subpluginid));
        if ($record) {
            $subplugin = trigger_subplugin::from_record($record);
            return $subplugin;
        } else {
            return null;
        }
    }

    /**
     * Persists a subplugin to the database.
     * @param trigger_subplugin $subplugin
     */
    public static function insert_or_update(trigger_subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($subplugin->id) {
            $DB->update_record('tool_cleanupcourses_trigger', $subplugin);
        } else {
            $subplugin->id = $DB->insert_record('tool_cleanupcourses_trigger', $subplugin);
        }
        $transaction->allow_commit();
    }

    /**
     * Removes all trigger instances from the database.
     * Should only be used, when uninstalling the subplugin.
     * @param string $subpluginname trigger instance id
     */
    public static function remove_all_instances($subpluginname) {
        global $DB;
        $records = $DB->get_records('tool_cleanupcourses_trigger', array('subpluginname' => $subpluginname));
        foreach ($records as $record) {
            settings_manager::remove_settings($record->id, SETTINGS_TYPE_TRIGGER);
        }
        $DB->delete_records('tool_cleanupcourses_trigger', array('subpluginname' => $subpluginname));
    }

    /**
     * Returns the trigger instance for the workflow id.
     * @param $workflowid int id of the workflow definition.
     * @return null|trigger_subplugin returns null, if there is no trigger instance for the workflow.
     * Otherwise, the trigger instance is returned.
     */
    public static function get_trigger_for_workflow($workflowid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('workflowid' => $workflowid));
        if ($record) {
            $subplugin = trigger_subplugin::from_record($record);
            return $subplugin;
        } else {
            return null;
        }
    }

    /**
     * Gets the list of step subplugins.
     * @return array of step subplugins.
     */
    public static function get_trigger_types() {
        $subplugins = \core_component::get_plugin_list('cleanupcoursestrigger');
        $result = array();
        foreach (array_keys($subplugins) as $plugin) {
            $result[$plugin] = get_string('pluginname', 'cleanupcoursestrigger_' . $plugin);
        }
        return $result;
    }

    /**
     * Gets the list of step subplugins, which are not preset and can therefore be chosen from trigger form dropdown..
     * @return array of step subplugins.
     */
    public static function get_chooseable_trigger_types() {
        $triggers = self::get_trigger_types();
        $result = array();
        foreach ($triggers as $id => $trigger) {
            $lib = lib_manager::get_trigger_lib($id);
            if ($lib->has_multiple_instances()) {
                $result[$id] = $trigger;
            }
        }
        return $result;
    }

    /**
     * Removes all instances, which belong to the workflow instance.
     * @param $workflowid int id of the workflow.
     */
    public static function remove_instances_of_workflow($workflowid) {
        global $DB;
        $DB->delete_records('tool_cleanupcourses_trigger', array('workflowid' => $workflowid));
    }

    /**
     * Copies the trigger of a workflow to a new one.
     * @param $oldworkflowid int id of the old workflow
     * @param $newworkflowid int id of the new workflow
     */
    public static function duplicate_trigger($oldworkflowid, $newworkflowid) {
        $trigger = self::get_trigger_for_workflow($oldworkflowid);
        $settings = settings_manager::get_settings($trigger->id, SETTINGS_TYPE_TRIGGER);

        $trigger->id = null;
        $trigger->workflowid = $newworkflowid;
        self::insert_or_update($trigger);

        settings_manager::save_settings($trigger->id, SETTINGS_TYPE_TRIGGER, $trigger->subpluginname, $settings);
    }

}
