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
 * Manager for Cleanup Course Workflows
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

use tool_cleanupcourses\entity\workflow;

defined('MOODLE_INTERNAL') || die();

class workflow_manager {

    /**
     * Remove a workflow from the database.
     * @param workflow $workflow
     */
    public static function insert_or_update(workflow &$workflow) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($workflow->id) {
            $DB->update_record('tool_cleanupcourses_workflow', $workflow);
        } else {
            $workflow->id = $DB->insert_record('tool_cleanupcourses_workflow', $workflow);
        }
        $transaction->allow_commit();
    }

    /**
     * Persists a workflow to the database.
     * @param int $workflowid id of the workflow
     */
    public static function remove($workflowid) {
        global $DB;
        trigger_manager::remove_instances_of_workflow($workflowid);
        step_manager::remove_instances_of_workflow($workflowid);
        $DB->delete_records('tool_cleanupcourses_workflow', array('id' => $workflowid));
    }

    /**
     * Returns a workflow instance if one with the is is available.
     * @param int $workflowid id of the workflow
     * @return workflow|null
     */
    public static function get_workflow($workflowid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_workflow', array('id' => $workflowid));
        if ($record) {
            $workflow = workflow::from_record($record);
            return $workflow;
        } else {
            return null;
        }
    }

    /**
     * Returns all active workflows.
     * @return workflow[]
     */
    public static function get_active_workflows() {
        global $DB;
        $records = $DB->get_records('tool_cleanupcourses_workflow', array('active' => true),
            'sortindex ASC');
        $result = array();
        foreach ($records as $record) {
            $result [] = workflow::from_record($record);
        }
        return $result;
    }

    /**
     * Returns all active automatic workflows.
     * @return workflow[]
     */
    public static function get_active_automatic_workflows() {
        global $DB;
        $records = $DB->get_records('tool_cleanupcourses_workflow', array('active' => true, 'manual' => false),
            'sortindex ASC');
        $result = array();
        foreach ($records as $record) {
            $result [] = workflow::from_record($record);
        }
        return $result;
    }

    /**
     * Activate a workflow
     * @param int $workflowid id of the workflow
     */
    public static function activate_workflow($workflowid) {
        global $DB, $OUTPUT;
        if (!self::is_valid($workflowid)) {
            echo $OUTPUT->notification(
                get_string('invalid_workflow_cannot_be_activated', 'tool_cleanupcourses'),
                'warning');
            return;
        }
        $transaction = $DB->start_delegated_transaction();
        $workflow = self::get_workflow($workflowid);
        if (!$workflow->active) {
            $trigger = trigger_manager::get_trigger_for_workflow($workflowid);
            $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
            $workflow->manual = $lib->is_manual_trigger();
            $workflow->active = true;
            $workflow->timeactive = time();
            if (!$workflow->manual) {
                $workflow->sortindex = count(self::get_active_automatic_workflows()) + 1;
            }
            self::insert_or_update($workflow);
        }
        $transaction->allow_commit();
    }

    /**
     * Handles an action of the subplugin_settings.
     * @param string $action action to be executed
     * @param int $workflowid id of the workflow
     */
    public static function handle_action($action, $workflowid) {
        global $OUTPUT;
        if ($action === ACTION_WORKFLOW_ACTIVATE) {
            self::activate_workflow($workflowid);
        }
        if ($action === ACTION_UP_WORKFLOW) {
            self::change_sortindex($workflowid, true);
        }
        if ($action === ACTION_DOWN_WORKFLOW) {
            self::change_sortindex($workflowid, false);
        }
        if ($action === ACTION_WORKFLOW_DUPLICATE) {
            self::duplicate_workflow($workflowid);
        }
        if ($action === ACTION_WORKFLOW_DELETE) {
            if (self::is_active($workflowid)) {
                echo $OUTPUT->notification(get_string('active_workflow_not_removeable', 'tool_cleanupcourses')
                    , 'warning');

            } else {
                self::remove($workflowid);
            }
        }
    }

    /**
     * Changes the sortindex of a workflow by swapping it with another.
     * @param int $workflowid id of the workflow
     * @param bool $up tells if the workflow should be set up or down
     */
    public static function change_sortindex($workflowid, $up) {
        global $DB;
        $workflow = self::get_workflow($workflowid);
        // Prevent first entry to be put up even more.
        if ($workflow->sortindex == 1 && $up) {
            return;
        }
        // Prevent inactive workflows to change sortindex.
        if (!$workflow->active) {
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

        $otherrecord = $DB->get_record('tool_cleanupcourses_workflow',
            array(
                'sortindex' => $otherindex)
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
     * @param $workflowid int id of the workflow.
     * @return bool true, if the definition is valid.
     */
    public static function is_valid($workflowid) {
        $trigger = trigger_manager::get_trigger_for_workflow($workflowid);
        if ($trigger === null) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the workflow is active.
     * @param $workflowid int id of the workflow.
     * @return bool true, if the workflow is active.
     */
    public static function is_active($workflowid) {
        $workflow = self::get_workflow($workflowid);
        return $workflow->active;
    }

    /**
     * Creates a workflow with a specific title. Is used to create preset workflows for trigger plugins.
     * @param $title string title of the workflow. Usually the pluginname of the trigger.
     * @return workflow the created workflow.
     */
    public static function create_workflow($title) {
        $record = new \stdClass();
        $record->title = $title;
        $workflow = workflow::from_record($record);
        self::insert_or_update($workflow);
        return $workflow;
    }

    /**
     * Duplicates a workflow including its trigger, all its steps and their settings.
     * @param $workflowid int id of the workflow to copy.
     * @return workflow the created workflow.
     */
    public static function duplicate_workflow($workflowid) {
        $oldworkflow = self::get_workflow($workflowid);
        try {
            $newtitle = get_string('workflow_duplicate_title', 'tool_cleanupcourses', $oldworkflow->title);
        } catch (\coding_exception $e) {
            $newtitle = $oldworkflow->title;
        }
        $newworkflow = self::create_workflow($newtitle);
        self::insert_or_update($newworkflow);
        // Copy trigger and steps using the new workflow id.
        trigger_manager::duplicate_trigger($workflowid, $newworkflow->id);
        step_manager::duplicate_steps($workflowid, $newworkflow->id);
        return $newworkflow;
    }

}
