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
 * lifecycletrigger_sitecourse generator tests
 *
 * @package    lifecycletrigger_sitecourse
 * @category   test
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;

/**
 * lifecycletrigger_sitecourse generator tests
 *
 * @package    lifecycletrigger_sitecourse
 * @category   test
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_sitecourse_generator extends testing_module_generator {

    /**
     * Creates a trigger sitecourse for an artificial workflow without steps.
     * @return trigger_subplugin the created sitecourse trigger.
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
        $record->subpluginname = 'sitecourse';
        $record->instancename = 'sitecourse';
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);
        return $trigger;
    }
}
