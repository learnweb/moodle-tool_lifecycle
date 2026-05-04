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

use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;

/**
 * Test data generator for lifecycletrigger_noenrolments.
 *
 * @package    lifecycletrigger_noenrolments
 * @category   test
 * @copyright  2021 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lifecycletrigger_noenrolments_generator extends testing_module_generator {
    /**
     * Creates a trigger noenrolments for an artificial workflow with a deletecourse step.
     *
     * @return trigger_subplugin the created noenrolments trigger.
     * @throws moodle_exception
     */
    public static function create_trigger_with_workflow(): trigger_subplugin {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);
        // Create trigger.
        $record = new stdClass();
        $record->subpluginname = 'noenrolments';
        $record->instancename = 'noenrolments';
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);
        // Create step (required for workflow validation).
        $step = new step_subplugin('deletecourse', 'deletecourse', $workflow->id);
        step_manager::insert_or_update($step);
        $settings = new stdClass();
        $settings->maximumdeletionspercron = 10;
        settings_manager::save_settings($step->id, settings_type::STEP, 'deletecourse', $settings);
        // Activate the workflow.
        workflow_manager::activate_workflow($workflow->id);
        return $trigger;
    }
}
