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
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Test generator
 *
 * @package    lifecycletrigger_customfieldsemester
 * @category   test
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 *             based on code 2020 by Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;

/**
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Test generator
 *
 * @package    lifecycletrigger_customfieldsemester
 * @category   test
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 *             based on code 2020 by Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_customfieldsemester_generator extends testing_module_generator {

    /**
     * Creates a trigger customfieldsemester for an artificial workflow without steps.
     *
     * @param string $customfieldshortname The shortname of the customfield which is used within the trigger.
     * @param int $delay The delay (in months) to be configured within the trigger.
     *
     * @return trigger_subplugin The created customfieldsemester trigger.
     * @throws moodle_exception
     */
    public static function create_trigger_with_workflow(string $customfieldshortname, int $delay) {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);

        // Create trigger.
        $record = new stdClass();
        $record->subpluginname = 'customfieldsemester';
        $record->instancename = 'customfieldsemester';
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);

        // Set delay setting.
        $settings = new stdClass();
        $settings->delay = $delay;
        $settings->customfield = $customfieldshortname;
        settings_manager::save_settings($trigger->id, settings_type::TRIGGER, $trigger->subpluginname, $settings);

        return $trigger;
    }
}
