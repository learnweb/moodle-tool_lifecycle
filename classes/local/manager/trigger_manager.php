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
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

use tool_lifecycle\action;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\settings_type;

/**
 * Manager for Trigger subplugins
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trigger_manager extends subplugin_manager {

    /**
     * Creates a preset workflow for the trigger subplugin.
     * @param string $subpluginname Name of the trigger subplugin.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function register_workflow($subpluginname) {
        $workflow = workflow_manager::create_workflow(
            get_string('pluginname', 'lifecycletrigger_' . $subpluginname));
        $record = new \stdClass();
        $record->subpluginname = $subpluginname;
        $record->instancename = get_string('pluginname', 'lifecycletrigger_' . $subpluginname);
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        self::insert_or_update($trigger);
        workflow_manager::activate_workflow($workflow->id);
    }

    /**
     * Returns a subplugin object.
     * @param int $instanceid Id of the subplugin.
     * @return trigger_subplugin
     * @throws \dml_exception
     */
    public static function get_instance($instanceid) {
        return self::get_subplugin_by_id($instanceid);
    }

    /**
     * Returns all instances for a trigger subplugin.
     * @param string $subpluginname name of the subplugin
     * @return trigger_subplugin[]
     * @throws \dml_exception
     */
    public static function get_instances($subpluginname) {
        global $DB;
        $result = [];
        $records = $DB->get_records('tool_lifecycle_trigger', ['subpluginname' => $subpluginname]);
        foreach ($records as $record) {
            $subplugin = trigger_subplugin::from_record($record);
            $result[] = $subplugin;
        }
        return $result;
    }

    /**
     * Returns a subplugin object.
     * @param int $subpluginid id of the subplugin
     * @return trigger_subplugin
     * @throws \dml_exception
     */
    private static function get_subplugin_by_id($subpluginid) {
        global $DB;
        $record = $DB->get_record('tool_lifecycle_trigger', ['id' => $subpluginid]);
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
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function insert_or_update(trigger_subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($subplugin->id) {
            $DB->update_record('tool_lifecycle_trigger', $subplugin);
        } else {
            $subplugin->sortindex = self::count_triggers_of_workflow($subplugin->workflowid) + 1;
            $subplugin->id = $DB->insert_record('tool_lifecycle_trigger', $subplugin);
        }
        $transaction->allow_commit();
    }

    /**
     * Removes all trigger instances from the database.
     * Should only be used, when uninstalling the subplugin.
     * @param string $subpluginname trigger instance id
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function remove_all_instances($subpluginname) {
        $triggers = self::get_instances($subpluginname);
        foreach ($triggers as $trigger) {
            self::remove($trigger->id);
        }
    }

    /**
     * Removes a trigger instance from the database.
     * @param int $triggerinstanceid trigger instance id
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    private static function remove($triggerinstanceid) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($record = $DB->get_record('tool_lifecycle_trigger', ['id' => $triggerinstanceid])) {
            $trigger = trigger_subplugin::from_record($record);
            self::remove_from_sortindex($trigger);
            settings_manager::remove_settings($trigger->id, settings_type::TRIGGER);
            $DB->delete_records('tool_lifecycle_trigger', (array) $trigger);
        }
        $transaction->allow_commit();
    }


    /**
     * Returns the triggers instances for the workflow id.
     * @param int $workflowid Id of the workflow definition.
     * @return trigger_subplugin[] returns the trigger instances for the workflow.
     * @throws \dml_exception
     */
    public static function get_triggers_for_workflow($workflowid) {
        global $DB;
        $records = $DB->get_records('tool_lifecycle_trigger', ['workflowid' => $workflowid], 'sortindex');
        $output = [];
        foreach ($records as $record) {
            $subplugin = trigger_subplugin::from_record($record);
            $output[] = $subplugin;
        }
        return $output;
    }

    /**
     * Removes a subplugin from the sortindex of a workflow and adjusts all other indizes.
     * @param trigger_subplugin $toberemoved
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    private static function remove_from_sortindex(&$toberemoved) {
        global $DB;
        if (isset($toberemoved->sortindex)) {
            $subplugins = $DB->get_records_select('tool_lifecycle_trigger',
                'workflowid = :workflowid AND sortindex > :sortindex',
                ['workflowid' => $toberemoved->workflowid, 'sortindex' => $toberemoved->sortindex]);
            foreach ($subplugins as $record) {
                $subplugin = trigger_subplugin::from_record($record);
                $subplugin->sortindex--;
                self::insert_or_update($subplugin);
            }
        }
    }

    /**
     * Changes the sortindex of a trigger by swapping it with another.
     * @param int $triggerid id of the trigger
     * @param bool $up tells if the trigger should be set up or down
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function change_sortindex($triggerid, $up) {
        global $DB;
        $trigger = self::get_instance($triggerid);
        // Prevent first entry to be put up even more.
        if ($trigger->sortindex == 1 && $up) {
            return;
        }
        // Prevent last entry to be put down even more.
        if ($trigger->sortindex == self::count_triggers_of_workflow($trigger->workflowid) && !$up) {
            return;
        }
        $index = $trigger->sortindex;
        if ($up) {
            $otherindex = $index - 1;
        } else {
            $otherindex = $index + 1;
        }
        $transaction = $DB->start_delegated_transaction();

        $otherrecord = $DB->get_record('tool_lifecycle_trigger',
            [
                'sortindex' => $otherindex,
                'workflowid' => $trigger->workflowid, ]
        );
        $othertrigger = trigger_subplugin::from_record($otherrecord);

        $trigger->sortindex = $otherindex;
        $othertrigger->sortindex = $index;
        self::insert_or_update($trigger);
        self::insert_or_update($othertrigger);

        $transaction->allow_commit();
    }

    /**
     * Gets the list of step subplugins.
     * @return array of step subplugins.
     * @throws \coding_exception
     */
    public static function get_trigger_types() {
        $subplugins = \core_component::get_plugin_list('lifecycletrigger');
        $result = [];
        foreach (array_keys($subplugins) as $plugin) {
            $result[$plugin] = get_string('pluginname', 'lifecycletrigger_' . $plugin);
        }
        return $result;
    }

    /**
     * Gets the list of step subplugins, which are not preset and can therefore be chosen from trigger form dropdown..
     * @return array of step subplugins.
     * @throws \coding_exception
     */
    public static function get_chooseable_trigger_types() {
        $triggers = self::get_trigger_types();
        $result = [];
        foreach ($triggers as $id => $trigger) {
            $lib = lib_manager::get_trigger_lib($id);
            if ($lib->has_multiple_instances()) {
                $result[$id] = $trigger;
            }
        }
        return $result;
    }


    /**
     * Handles an action for a workflow step.
     * @param string $action action to be executed
     * @param int $subpluginid id of the trigger instance
     * @param int $workflowid id of the workflow
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function handle_action($action, $subpluginid, $workflowid) {
        $trigger = self::get_instance($subpluginid);
        if ($trigger && $trigger->workflowid == $workflowid ) {
            if (!workflow_manager::is_active($workflowid)) {
                if ($action === action::UP_TRIGGER) {
                    self::change_sortindex($subpluginid, true);
                }
                if ($action === action::DOWN_TRIGGER) {
                    self::change_sortindex($subpluginid, false);
                }
                if ($action === action::TRIGGER_INSTANCE_DELETE) {
                    self::remove($subpluginid);
                }
            } else {
                \core\notification::add(get_string('active_workflow_not_changeable', 'tool_lifecycle'),
                    \core\notification::WARNING);
            }
        }
    }

    /**
     * Gets the count of triggers belonging to a workflow.
     * @param int $workflowid id of the workflow.
     * @return int count of the steps.
     * @throws \dml_exception
     */
    public static function count_triggers_of_workflow($workflowid) {
        global $DB;
        return $DB->count_records('tool_lifecycle_trigger',
            ['workflowid' => $workflowid]
        );
    }

    /**
     * Removes all instances, which belong to the workflow instance.
     * @param int $workflowid Id of the workflow.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function remove_instances_of_workflow($workflowid) {
        global $DB;
        $instances = self::get_triggers_for_workflow($workflowid);
        foreach ($instances as $instance) {
            settings_manager::remove_settings($instance->id, settings_type::TRIGGER);
        }
        $DB->delete_records('tool_lifecycle_trigger', ['workflowid' => $workflowid]);
    }

    /**
     * Copies the triggers of a workflow to a new one.
     * @param int $oldworkflowid Id of the old workflow
     * @param int $newworkflowid Id of the new workflow
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \moodle_exception
     */
    public static function duplicate_triggers($oldworkflowid, $newworkflowid) {
        $triggers = self::get_triggers_for_workflow($oldworkflowid);
        foreach ($triggers as $trigger) {
            $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);

            $trigger->id = null;
            $trigger->workflowid = $newworkflowid;
            self::insert_or_update($trigger);

            settings_manager::save_settings($trigger->id, settings_type::TRIGGER, $trigger->subpluginname, $settings);
        }

    }

}
