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
 * lifecycletrigger_delayedcourses generator tests
 *
 * @package    lifecycletrigger_delayedcourses
 * @category   test
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;

/**
 * lifecycletrigger_delayedcourses generator tests
 *
 * @package    lifecycletrigger_delayedcourses
 * @category   test
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_delayedcourses_generator extends testing_module_generator {

    /**
     * Creates a trigger delayedcourses for an artificial workflow without steps.
     * @return trigger_subplugin the created delayedcourses trigger.
     */
    public static function create_trigger_with_workflow() {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);
        // Create trigger.
        $record = new stdClass();
        $record->subpluginname = 'delayedcourses';
        $record->instancename = 'delayedcourses';
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);
        return $trigger;
    }

    /**
     * Creates a workflow, which delays only for upcomming processes of itself.
     * @return workflow.
     */
    public static function create_workflow() {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $record->rollbackdelay = 10000;
        $record->finishdelay = 10000;
        $record->delayforallworkflows = 0;
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);
        return $workflow;
    }

    /**
     * Creates a workflow, which delays upcomming processes for all workflows.
     * @return workflow.
     */
    public static function create_workflow_delaying_for_all_workflows() {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $record->rollbackdelay = 10000;
        $record->finishdelay = 10000;
        $record->delayforallworkflows = 1;
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);
        return $workflow;
    }
}
