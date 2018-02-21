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

use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\settings_manager;

/**
 * Tests assembly of manual trigger tools.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2018 Tobias Reischmann, Jan Dageforde WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_manual_trigger_tools_testcase extends \advanced_testcase {
    const MANUAL_TRIGGER1_ICON = 't/up';
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';
    const MANUAL_TRIGGER2_ICON = 't/down';
    const MANUAL_TRIGGER2_DISPLAYNAME = 'Down';
    const MANUAL_TRIGGER2_CAPABILITY = 'moodle/course:view';

    public function setUp() {
        $this->resetAfterTest(false);

        $settings = new stdClass();
        $settings->icon = self::MANUAL_TRIGGER1_ICON;
        $settings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $settings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        tool_cleanupcourses_generator::create_manual_workflow($settings);

        $settings = new stdClass();
        $settings->icon = self::MANUAL_TRIGGER2_ICON;
        $settings->displayname = self::MANUAL_TRIGGER2_DISPLAYNAME;
        $settings->capability = self::MANUAL_TRIGGER2_CAPABILITY;
        tool_cleanupcourses_generator::create_manual_workflow($settings);
    }

    /**
     * Test setting and getting settings data for steps.
     */
    public function test_get_manual_trigger_tools_for_active_workflows() {
        $tools = \tool_cleanupcourses\manager\workflow_manager::get_manual_trigger_tools_for_active_workflows();
        $this->assertCount(2, $tools);
        $this->assertContainsOnly(\tool_cleanupcourses\local\data\manual_trigger_tool::class, $tools);
    }

}