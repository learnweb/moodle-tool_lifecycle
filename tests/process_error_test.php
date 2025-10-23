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
 * Checks whether process errors are properly inserted into the table.
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\process_manager;

/**
 * Checks whether process errors are properly inserted into the table.
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class process_error_test extends \advanced_testcase {
    /** Icon of the manual trigger. */
    const MANUAL_TRIGGER1_ICON = 't/up';
    /** Display name of the manual trigger. */
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    /** Capability of the manual trigger. */
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';
    /** The not existing course id. */
    const NOTEXISTING_COURSE_ID = 999;

    /** @var \tool_lifecycle_generator $generator Instance of the test generator. */
    private $generator;

    /** @var workflow $workflow Generated and activated workflow. */
    private $workflow;

    /**
     * Set up the testcase.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function setUp(): void {
        global $USER;

        parent::setUp();

        // We do not need a sesskey check in these tests.
        $USER->ignoresesskey = true;
        $this->resetAfterTest(true);
        $this->generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        // Create manual trigger and a duplicate step for our workflow.
        $triggersettings = new \stdClass();
        $triggersettings->icon = self::MANUAL_TRIGGER1_ICON;
        $triggersettings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $triggersettings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $this->workflow = $this->generator->create_manual_workflow($triggersettings);
        $this->generator->create_step("instance1", "duplicate", $this->workflow->id);

        // Activate workflow.
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow->id);
    }

    /**
     * Test if the correct process error was put into the table.
     * @covers \tool_lifecycle\processor
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_process_error_in_table(): void {
        global $DB;

        // Create entry in process table with not existing course id.
        $this->generator->create_process(self::NOTEXISTING_COURSE_ID, $this->workflow->id, true);

        // Process all the processes of our active workflow.
        $processor = new processor();
        $processor->process_courses();

        // Get all the entries of the proc_error table.
        $errorrecords = $DB->get_records('tool_lifecycle_proc_error');

        // It should be exactly one.
        $this->assertCount(1, $errorrecords);
        // And no entry in the process table any more.
        $this->assertEquals(0, $DB->count_records('tool_lifecycle_process'));

        $errorrecord = reset($errorrecords);

        // Has the only entry in the proc_error table the expected course and workflow id and a message?
        $this->assertEquals(self::NOTEXISTING_COURSE_ID, $errorrecord->courseid);
        $this->assertEquals($this->workflow->id, $errorrecord->workflowid);
        $this->assertNotEmpty($errorrecord->errormessage);
    }

}
