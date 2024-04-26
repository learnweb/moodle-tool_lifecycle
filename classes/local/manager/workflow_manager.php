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
 * Manager for Life Cycle Workflows
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

use tool_lifecycle\action;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\backup\backup_lifecycle_workflow;
use tool_lifecycle\local\data\manual_trigger_tool;
use tool_lifecycle\settings_type;

/**
 * Manager for Life Cycle Workflows
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class workflow_manager {

    /**
     * Persists a workflow to the database.
     *
     * @param workflow $workflow
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function insert_or_update(workflow &$workflow) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($workflow->id) {
            $DB->update_record('tool_lifecycle_workflow', $workflow);
        } else {
            $workflow->id = $DB->insert_record('tool_lifecycle_workflow', $workflow);
        }
        $transaction->allow_commit();
        return $workflow->id;
    }

    /**
     * Remove a workflow from the database.
     *
     * @param int $workflowid id of the workflow
     * @param boolean $hard if set, will remove the workflow without checking if it's removable! Mainly for testing.
     * @throws \dml_exception
     */
    public static function remove($workflowid, $hard = false) {
        global $DB;
        if ($hard || self::is_removable($workflowid)) {
            $workflow = self::get_workflow($workflowid);
            self::remove_from_sortindex($workflow);
            trigger_manager::remove_instances_of_workflow($workflowid);
            step_manager::remove_instances_of_workflow($workflowid);
            $DB->delete_records('tool_lifecycle_workflow', ['id' => $workflowid]);
        }
    }

    /**
     * Disables a workflow
     *
     * @param int $workflowid id of the workflow
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function disable($workflowid) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $workflow = self::get_workflow($workflowid);
        if ($workflow && self::is_disableable($workflowid)) {
            $workflow->timeactive = null;
            self::remove_from_sortindex($workflow);
            $workflow->sortindex = null;
            $workflow->timedeactive = time();
            $DB->update_record('tool_lifecycle_workflow', $workflow);
        }
        $transaction->allow_commit();
    }

    /**
     * Removes a workflow from the sortindex.
     *
     * @param workflow $toberemoved
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function remove_from_sortindex($toberemoved) {
        global $DB;
        if (isset($toberemoved->sortindex)) {
            $workflows = self::get_active_automatic_workflows();
            foreach ($workflows as $workflow) {
                if ($workflow->sortindex > $toberemoved->sortindex) {
                    $workflow->sortindex--;
                    $DB->update_record('tool_lifecycle_workflow', $workflow);
                }
            }
        }
    }

    /**
     * Deletes all running processes of given workflow
     *
     * @param int $workflowid id of the workflow
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function abortprocesses($workflowid) {
        $processes = process_manager::get_processes_by_workflow($workflowid);
        foreach ($processes as $process) {
            process_manager::rollback_process($process);
        }
    }

    /**
     * Returns a workflow instance if one with the is is available.
     *
     * @param int $workflowid id of the workflow
     * @return workflow|null
     * @throws \dml_exception
     */
    public static function get_workflow($workflowid) {
        global $DB;
        $record = $DB->get_record('tool_lifecycle_workflow', ['id' => $workflowid]);
        if ($record) {
            $workflow = workflow::from_record($record);
            return $workflow;
        } else {
            return null;
        }
    }

    /**
     * Returns all existing workflows.
     *
     * @return workflow[]
     * @throws \dml_exception
     */
    public static function get_workflows() {
        global $DB;
        $result = [];
        $records = $DB->get_records('tool_lifecycle_workflow');
        foreach ($records as $record) {
            $result[] = workflow::from_record($record);
        }
        return $result;
    }

    /**
     * Returns all active workflows.
     *
     * @return workflow[]
     * @throws \dml_exception
     */
    public static function get_active_workflows() {
        global $DB;
        $records = $DB->get_records_sql(
            'SELECT * FROM {tool_lifecycle_workflow}
                  WHERE timeactive IS NOT NULL ORDER BY sortindex');
        $result = [];
        foreach ($records as $record) {
            $result[] = workflow::from_record($record);
        }
        return $result;
    }

    /**
     * Returns all active automatic workflows.
     *
     * @return workflow[]
     * @throws \dml_exception
     */
    public static function get_active_automatic_workflows() {
        global $DB;
        $records = $DB->get_records_sql(
            'SELECT * FROM {tool_lifecycle_workflow}
                  WHERE timeactive IS NOT NULL AND
                  manual = ? ORDER BY sortindex', [false]);
        $result = [];
        foreach ($records as $record) {
            $result[] = workflow::from_record($record);
        }
        return $result;
    }

    /**
     * Returns triggers of active manual workflows.
     *
     * @return trigger_subplugin[]
     * @throws \dml_exception
     */
    public static function get_active_manual_workflow_triggers() {
        global $DB;
        $sql = 'SELECT t.* FROM {tool_lifecycle_workflow} w JOIN {tool_lifecycle_trigger} t ON t.workflowid = w.id' .
            ' WHERE w.timeactive IS NOT NULL AND w.manual = ?';
        $records = $DB->get_records_sql($sql, [true]);
        $result = [];
        foreach ($records as $record) {
            $result[] = trigger_subplugin::from_record($record);
        }
        return $result;
    }

    /**
     * Returns tools for all active manual workflows.
     * You need to check the capability based on course and user before diplaying it.
     *
     * @return manual_trigger_tool[] list of tools, available in the whole system.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_manual_trigger_tools_for_active_workflows() {
        $triggers = self::get_active_manual_workflow_triggers();
        $tools = [];
        foreach ($triggers as $trigger) {
            $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);
            $tools[] = new manual_trigger_tool($trigger->id, $settings['icon'], $settings['displayname'], $settings['capability']);
        }
        return $tools;
    }

    /**
     * Activate a workflow
     *
     * @param int $workflowid id of the workflow
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function activate_workflow($workflowid) {
        global $DB;
        if (!self::is_valid($workflowid)) {
            \core\notification::add(
                get_string('invalid_workflow_cannot_be_activated', 'tool_lifecycle'),
                \core\notification::WARNING);
            return;
        }
        $transaction = $DB->start_delegated_transaction();
        $workflow = self::get_workflow($workflowid);
        if (!self::is_active($workflow->id)) {
            // TODO: Rethink behaviour for multiple triggers.
            $trigger = trigger_manager::get_triggers_for_workflow($workflowid)[0];
            $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
            $workflow->manual = $lib->is_manual_trigger();
            $workflow->timeactive = time();
            if (!$workflow->manual) {
                $workflow->sortindex = count(self::get_active_automatic_workflows()) + 1;
            }
            self::insert_or_update($workflow);
        }
        $transaction->allow_commit();
    }

    /**
     * Resets the 'does a manual workflow exist?'-cache.
     */
    private static function reset_has_workflow_cache() {
        $cache = \cache::make('tool_lifecycle', 'application');
        $cache->delete('workflowactive');
    }

    /**
     * Handles an action of the subplugin_settings.
     *
     * @param string $action action to be executed
     * @param int $workflowid id of the workflow
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \moodle_exception
     */
    public static function handle_action($action, $workflowid) {
        if (!empty($action)) {
            require_sesskey();
        }
        if ($action === action::WORKFLOW_ACTIVATE) {
            self::activate_workflow($workflowid);
            self::reset_has_workflow_cache();
        } else if ($action === action::UP_WORKFLOW) {
            self::change_sortindex($workflowid, true);
        } else if ($action === action::DOWN_WORKFLOW) {
            self::change_sortindex($workflowid, false);
        } else if ($action === action::WORKFLOW_DUPLICATE) {
            self::duplicate_workflow($workflowid);
        } else if ($action === action::WORKFLOW_BACKUP) {
            self::backup_workflow($workflowid);
        } else if ($action === action::WORKFLOW_DISABLE) {
            self::disable($workflowid);
            self::reset_has_workflow_cache();
            return; // Return, since we do not want to redirect outside to deactivated workflows.
        } else if ($action === action::WORKFLOW_ABORTDISABLE) {
            self::disable($workflowid);
            self::abortprocesses($workflowid);
            self::reset_has_workflow_cache();
            return; // Return, since we do not want to redirect outside to deactivated workflows.
        } else if ($action === action::WORKFLOW_ABORT) {
            self::abortprocesses($workflowid);
            return; // Return, since we do not want to redirect outside to deactivated workflows.
        } else if ($action === action::WORKFLOW_DELETE) {
            // Check workflow wasn't already deleted, in case someone refreshes the page.
            if (self::get_workflow($workflowid) &&
                self::is_removable($workflowid)) {
                self::remove($workflowid);
                self::reset_has_workflow_cache();
            } else {
                \core\notification::add(get_string('workflow_not_removeable', 'tool_lifecycle')
                    , \core\notification::WARNING);
            }
        } else {
            // If no action has been called. Continue.
            return;
        }
    }

    /**
     * Changes the sortindex of a workflow by swapping it with another.
     *
     * @param int $workflowid id of the workflow
     * @param bool $up tells if the workflow should be set up or down
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function change_sortindex($workflowid, $up) {
        global $DB;
        $workflow = self::get_workflow($workflowid);
        // Prevent first entry to be put up even more.
        if ($workflow->sortindex == 1 && $up) {
            return;
        }
        // Prevent inactive workflows to change sortindex.
        if (!self::is_active($workflow->id)) {
            return;
        }
        // Prevent last entry to be put down even more.
        if ($workflow->sortindex == count(self::get_active_automatic_workflows()) && !$up) {
            return;
        }
        $index = $workflow->sortindex;
        if ($up) {
            $otherindex = $index - 1;
        } else {
            $otherindex = $index + 1;
        }
        $transaction = $DB->start_delegated_transaction();

        $otherrecord = $DB->get_record('tool_lifecycle_workflow',
            [
                'sortindex' => $otherindex, ]
        );
        $otherworkflow = workflow::from_record($otherrecord);

        $workflow->sortindex = $otherindex;
        $otherworkflow->sortindex = $index;
        self::insert_or_update($workflow);
        self::insert_or_update($otherworkflow);

        $transaction->allow_commit();
    }

    /**
     * Checks if the workflow definition is valid.
     * The main purpose of this function is, to check if a trigger definition exists and if this definition is complete.
     *
     * @param int $workflowid Id of the workflow.
     * @return bool true, if the definition is valid.
     */
    public static function is_valid($workflowid) {
        $triggers = trigger_manager::get_triggers_for_workflow($workflowid);
        if (empty($triggers)) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the workflow is active.
     *
     * @param int $workflowid Id of the workflow.
     * @return bool true, if the workflow is active.
     * @throws \dml_exception
     */
    public static function is_active($workflowid) {
        $workflow = self::get_workflow($workflowid);
        return $workflow->timeactive != null;
    }

    /**
     * Checks if the workflow is deactive.
     *
     * @param int $workflowid Id of the workflow.
     * @return bool true, if the workflow was deactivated.
     * @throws \dml_exception
     */
    public static function is_deactivated($workflowid) {
        $workflow = self::get_workflow($workflowid);
        if ($workflow->timedeactive) {
            return true;
        }
        return false;
    }

    /**
     * Creates a workflow with a specific title. Is used to create preset workflows for trigger plugins or for
     * duplication of workflows.
     *
     * @param string $title Title of the workflow.
     * @param string $displaytitle Display title of the workflow.
     * @return workflow the created workflow.
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function create_workflow($title, $displaytitle = null) {
        $record = new \stdClass();
        $record->title = $title;
        if (!is_null($displaytitle)) {
            $record->displaytitle = $displaytitle;
        }
        $workflow = workflow::from_record($record);
        self::insert_or_update($workflow);
        return $workflow;
    }

    /**
     * Duplicates a workflow including its trigger, all its steps and their settings.
     *
     * @param int $workflowid Id of the workflow to copy.
     * @return workflow the created workflow.
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function duplicate_workflow($workflowid) {
        $oldworkflow = self::get_workflow($workflowid);
        try {
            $newtitle = get_string('workflow_duplicate_title', 'tool_lifecycle', $oldworkflow->title);
        } catch (\coding_exception $e) {
            $newtitle = $oldworkflow->title;
        }
        $newworkflow = self::create_workflow($newtitle, $oldworkflow->displaytitle);
        $newworkflow->rollbackdelay = $oldworkflow->rollbackdelay;
        $newworkflow->finishdelay = $oldworkflow->finishdelay;
        $newworkflow->delayforallworkflows = $oldworkflow->delayforallworkflows;
        self::insert_or_update($newworkflow);
        // Copy trigger and steps using the new workflow id.
        trigger_manager::duplicate_triggers($workflowid, $newworkflow->id);
        step_manager::duplicate_steps($workflowid, $newworkflow->id);
        return $newworkflow;
    }

    /**
     * Calls the backup process for the workflow, which will send the workflow structure with all
     * subplugins as a xml file to the client.
     * @param int $workflowid id of the workflow to be backed up.
     * @throws \moodle_exception
     */
    public static function backup_workflow($workflowid) {
        $task = new backup_lifecycle_workflow($workflowid);
        $task->execute();
        $task->send_temp_file();
    }

    /**
     * Checks if it should be possible to disable a workflow
     *
     * @param int $workflowid Id of the workflow.
     * @return bool
     */
    public static function is_disableable($workflowid) {
        $trigger = trigger_manager::get_triggers_for_workflow($workflowid);
        if (!empty($trigger)) {
            $lib = lib_manager::get_trigger_lib($trigger[0]->subpluginname);
        }
        if (!isset($lib) || $lib->has_multiple_instances()) {
            return true;
        }
        return false;
    }

    /**
     * Workflows should only be editable if never been activated before
     *
     * @param int $workflowid Id of the workflow
     * @return bool
     * @throws \dml_exception
     */
    public static function is_editable($workflowid) {
        if (self::is_active($workflowid) ||
            self::is_deactivated($workflowid)) {
            return false;
        }
        return true;
    }

    /**
     * Workflows should only be abortable if disabled but some processes are still running
     *
     * @param int $workflowid Id of the workflow.
     * @return bool
     * @throws \dml_exception
     */
    public static function is_abortable($workflowid) {
        $countprocesses = process_manager::count_processes_by_workflow($workflowid);
        if ($countprocesses > 0) {
            return true;
        }
        return false;
    }

    /**
     * Workflows should only be removable if disableable and no more processes are running
     *
     * @param int $workflowid Id of the workflow.
     * @return bool
     * @throws \dml_exception
     */
    public static function is_removable($workflowid) {
        $countprocesses = process_manager::count_processes_by_workflow($workflowid);
        if (self::is_disableable($workflowid) && $countprocesses == 0) {
            return true;
        }
        return false;
    }
}
