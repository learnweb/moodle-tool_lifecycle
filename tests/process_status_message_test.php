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

use tool_cleanupcourses\manager\workflow_manager;

/**
 * Tests assembly of manual trigger tools.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2018 Tobias Reischmann, Jan Dageforde WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_process_status_message_testcase extends \advanced_testcase {
    const MANUAL_TRIGGER1_ICON = 't/up';
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';
    private $workflow;


    public function setUp() {
        $this->resetAfterTest(false);

        $settings = new stdClass();
        $settings->icon = self::MANUAL_TRIGGER1_ICON;
        $settings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $settings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $this->workflow = tool_cleanupcourses_generator::create_manual_workflow($settings);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow->id);

        tool_cleanupcourses_generator::create_step("instance1", "dummy", $this->workflow->id);
        tool_cleanupcourses_generator::create_step("instance2", "email", $this->workflow->id);
    }

    /**
     * Test getting status message for a process.
     */
    public function test_get_status_message() {
        $process = tool_cleanupcourses_generator::create_process(2, $this->workflow->id);
        $message = \tool_cleanupcourses\manager\interaction_manager::get_process_status_message($process->id);
        $this->assertEquals(get_string("workflow_started", "tool_cleanupcourses"), $message);

        \tool_cleanupcourses\manager\process_manager::proceed_process($process);
        $message = \tool_cleanupcourses\manager\interaction_manager::get_process_status_message($process->id);
        // TODO change message
        $this->assertEquals("", $message);

        \tool_cleanupcourses\manager\process_manager::proceed_process($process);
        $message = \tool_cleanupcourses\manager\interaction_manager::get_process_status_message($process->id);
        $this->assertEquals(get_string('status_message_requiresattention', 'cleanupcoursesstep_email'), $message);
    }
}