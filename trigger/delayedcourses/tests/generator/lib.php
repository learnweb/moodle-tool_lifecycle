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

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;

/**
 * lifecycletrigger_delayedcourses generator tests
 *
 * @package    lifecycletrigger_delayedcourses
 * @category   test
 * @copyright  2025 Thomas Niedermaier University of Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_delayedcourses_generator extends testing_module_generator {

    /**
     * Creates a trigger delayedcourses for an artificial workflow without steps.
     * @return trigger_subplugin the delayedcourses trigger.
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws moodle_exception
     */
    public static function create_workflow_with_delayedcourses_trigger() {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $record->includedelayedcourses = 1;
        $record->rollbackdelay = 120;
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
}
