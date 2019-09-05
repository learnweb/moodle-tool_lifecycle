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
 * lifecycletrigger_categories generator tests
 *
 * @package    lifecycletrigger_categories
 * @category   test
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use tool_lifecycle\entity\trigger_subplugin;
use tool_lifecycle\entity\workflow;
use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\manager\workflow_manager;
use tool_lifecycle\settings_type;

/**
 * lifecycletrigger_categories generator tests
 *
 * @package    lifecycletrigger_categories
 * @category   test
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_categories_generator extends testing_module_generator {

    /**
     * Creates a trigger startdatedelay for an artificial workflow without steps.
     * @param array $data Data which is used to fill the triggers with certain settings.
     * @return trigger_subplugin The created startdatedelay trigger.
     * @throws moodle_exception
     */
    public static function create_trigger_with_workflow($data) {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);
        // Create trigger.
        $record = new stdClass();
        $record->subpluginname = 'categories';
        $record->instancename = 'categories';
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);
        // Set delay setting.
        $settings = new stdClass();
        $settings->categories = $data['categories'];
        $settings->exclude = $data['exclude'];
        settings_manager::save_settings($trigger->id, settings_type::TRIGGER, $trigger->subpluginname, $settings);

        return $trigger;
    }
}
