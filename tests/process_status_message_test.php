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
 * Tests assembly of manual trigger tools.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 Tamara Gunkel, Jan Dageforde WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;
use tool_lifecycle\action;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\workflow_manager;

/**
 * Tests assembly of manual trigger tools.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 Tamara Gunkel, Jan Dageforde WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class process_status_message_test extends \advanced_testcase {
    /** Icon of the manual trigger. */
    const MANUAL_TRIGGER1_ICON = 't/up';
    /** Display name of the manual trigger. */
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    /** Capability of the manual trigger. */
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';

    /** @var workflow $workflow Workflow of this test. */
    private $workflow;

    /** @var \tool_lifecycle_generator $generator Instance of the test generator. */
    private $generator;

    /**
     * Setup the testcase.
     * @throws \coding_exception
     */
    public function setUp(): void {
        global $USER;

        // We do not need a sesskey check in theses tests.
        $USER->ignoresesskey = true;

        $this->resetAfterTest(false);
        $this->generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $settings = new \stdClass();
        $settings->icon = self::MANUAL_TRIGGER1_ICON;
        $settings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $settings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $this->workflow = $this->generator->create_manual_workflow($settings);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow->id);

        $this->generator->create_step("instance1", "createbackup", $this->workflow->id);
        $this->generator->create_step("instance2", "email", $this->workflow->id);
    }

    /**
     * Test getting status message for a process.
     * @covers \tool_lifecycle\local\manager\interaction_manager
     */
    public function test_get_status_message(): void {
        $process = $this->generator->create_process(2, $this->workflow->id);
        $message = \tool_lifecycle\local\manager\interaction_manager::get_process_status_message($process->id);
        $this->assertEquals(get_string("workflow_started", "tool_lifecycle"), $message);

        \tool_lifecycle\local\manager\process_manager::proceed_process($process);
        $message = \tool_lifecycle\local\manager\interaction_manager::get_process_status_message($process->id);
        $this->assertEquals(get_string("workflow_is_running", "tool_lifecycle"), $message);

        \tool_lifecycle\local\manager\process_manager::proceed_process($process);
        $message = \tool_lifecycle\local\manager\interaction_manager::get_process_status_message($process->id);
        $this->assertEquals(get_string('status_message_requiresattention', 'lifecyclestep_email'), $message);
    }
}
