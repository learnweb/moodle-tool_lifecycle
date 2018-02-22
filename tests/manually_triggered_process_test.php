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

require_once(__DIR__ . '/generator/lib.php');
require_once(__DIR__ . '/../lib.php');

use tool_cleanupcourses\manager\workflow_manager;
use tool_cleanupcourses\manager\trigger_manager;
use tool_cleanupcourses\manager\process_manager;
use tool_cleanupcourses\cleanup_processor;

/**
 * Manually triggers a process and tests if process courses proceeds the process as expected.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_manually_triggered_process_testcase extends \advanced_testcase {
    const MANUAL_TRIGGER1_ICON = 't/up';
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';

    private $trigger;
    private $course;

    public function setUp() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_cleanupcourses');

        $triggersettings = new stdClass();
        $triggersettings->icon = self::MANUAL_TRIGGER1_ICON;
        $triggersettings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $triggersettings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $manualworkflow = $generator->create_manual_workflow($triggersettings);
        $generator->create_step("instance1", "dummy", $manualworkflow->id);

        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $manualworkflow->id);

        $this->course = $this->getDataGenerator()->create_course();
        $this->trigger = trigger_manager::get_trigger_for_workflow($manualworkflow->id);
    }

    /**
     * Test to proceed a manually triggered process to step index 1.
     */
    public function test_proceeding_of_manually_triggered_processes() {
        $process = process_manager::manually_trigger_process($this->course->id, $this->trigger->id);
        $this->assertEquals(0, $process->stepindex);
        var_dump($process);

        $processor = new cleanup_processor();
        $processor->process_courses();
        $process = process_manager::get_process_by_id($process->id);
        var_dump($process);

        $this->assertEquals(1, $process->stepindex);
    }

}
