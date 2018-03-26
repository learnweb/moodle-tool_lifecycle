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

defined('MOODLE_INTERNAL') || die();

use tool_lifecycle\manager\workflow_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\local\data\manual_trigger_tool;

/**
 * Tests assembly of manual trigger tools.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 Tobias Reischmann, Jan Dageforde WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_manual_trigger_tools_testcase extends \advanced_testcase {
    const MANUAL_TRIGGER1_ICON = 't/up';
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';
    const MANUAL_TRIGGER2_ICON = 't/down';
    const MANUAL_TRIGGER2_DISPLAYNAME = 'Down';
    const MANUAL_TRIGGER2_CAPABILITY = 'moodle/course:view';
    private $workflow1;
    private $workflow2;

    public function setUp() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        $triggersettings = new stdClass();
        $triggersettings->icon = self::MANUAL_TRIGGER1_ICON;
        $triggersettings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $triggersettings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $this->workflow1 = $generator->create_manual_workflow($triggersettings);

        $triggersettings = new stdClass();
        $triggersettings->icon = self::MANUAL_TRIGGER2_ICON;
        $triggersettings->displayname = self::MANUAL_TRIGGER2_DISPLAYNAME;
        $triggersettings->capability = self::MANUAL_TRIGGER2_CAPABILITY;
        $this->workflow2 = $generator->create_manual_workflow($triggersettings);
    }

    /**
     * Test getting manual trigger tools of active workflows.
     */
    public function test_get_manual_trigger_tools_for_one_active_workflow() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        $tools = workflow_manager::get_manual_trigger_tools_for_active_workflows();
        $this->assertCount(1, $tools);
        $this->assertContainsOnly(manual_trigger_tool::class, $tools);
        $trigger = trigger_manager::get_trigger_for_workflow($this->workflow2->id);
        $tool = new manual_trigger_tool($trigger->id, self::MANUAL_TRIGGER2_ICON,
            self::MANUAL_TRIGGER2_DISPLAYNAME, self::MANUAL_TRIGGER2_CAPABILITY);
        $this->assertEquals($tool, $tools[0]);
    }

    /**
     * Test getting manual trigger tools of active workflows.
     */
    public function test_get_manual_trigger_tools_for_active_workflows() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        $tools = workflow_manager::get_manual_trigger_tools_for_active_workflows();
        $this->assertCount(2, $tools);
        $this->assertContainsOnly(\tool_lifecycle\local\data\manual_trigger_tool::class, $tools);
        $trigger = trigger_manager::get_trigger_for_workflow($this->workflow1->id);
        $expectedtool = new manual_trigger_tool($trigger->id, self::MANUAL_TRIGGER1_ICON,
            self::MANUAL_TRIGGER1_DISPLAYNAME, self::MANUAL_TRIGGER1_CAPABILITY);
        $this->assert_tool_exist($expectedtool, $tools);

        $trigger = trigger_manager::get_trigger_for_workflow($this->workflow2->id);
        $expectedtool = new manual_trigger_tool($trigger->id, self::MANUAL_TRIGGER2_ICON,
            self::MANUAL_TRIGGER2_DISPLAYNAME, self::MANUAL_TRIGGER2_CAPABILITY);
        $this->assert_tool_exist($expectedtool, $tools);

    }

    /**
     * Test if a specific manual_trigger_tool exist within an array.
     * @param $expectedtool manual_trigger_tool searched trigger_tool.
     * @param $tools manual_trigger_tool[] haystack.
     */
    private function assert_tool_exist($expectedtool, $tools) {
        $found = false;
        foreach ($tools as $tool) {
            $equalvalues = true;
            foreach ($tool as $key => $value) {
                if ($expectedtool->$key !== $value) {
                    $equalvalues = false;
                }
            }
            if ($equalvalues) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

}