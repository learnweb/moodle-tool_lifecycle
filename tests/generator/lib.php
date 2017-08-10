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

use tool_cleanupcourses\entity\trigger_subplugin;
use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\manager\trigger_manager;
use tool_cleanupcourses\manager\step_manager;

defined('MOODLE_INTERNAL') || die();


/**
 * tool_cleanupcourses generator tests
 *
 * @package    tool_cleanupcourses
 * @category   test
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_generator extends testing_module_generator {

    public static function setup_test_plugins() {
        global $DB;
        $DB->delete_records('tool_cleanupcourses_trigger');
        for ($i = 1; $i <= 3; $i++) {
            $record = array(
                    'id' => $i,
                    'subpluginname' => 'subplugin'.$i,
                    'enabled' => 1,
                    'sortindex' => $i,
            );
            $DB->insert_record_raw('tool_cleanupcourses_trigger', $record, true, true, true);
        }
    }

    /**
     * Creates an artificial trigger instance.
     */
    public static function create_trigger_with_workflow() {
        // Create Second Step.
        $step2 = new step_subplugin('mystepinstance', 'stepname');
        step_manager::insert_or_update($step2);

        // Create First Step.
        $step1 = new step_subplugin('mystepinstance', 'stepname');
        step_manager::insert_or_update($step1);
        step_manager::change_followedby($step1->id, $step2->id);

        // Create trigger.
        $record = new stdClass();
        $record->subpluginname = 'mytrigger';
        $record->followedby = $step1->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);
        return $trigger;
    }
}
