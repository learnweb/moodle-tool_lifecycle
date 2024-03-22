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


    /** @var trigger_subplugin $trigger Instances of the triggers under test. */
    private $trigger;
    /** @var array $course Instance of the course under test. */
    private $course;

    /**
     * Setup the testcase.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function setUp(): void {
        global $USER, $DB;

        // We do not need a sesskey check in theses tests.
        $USER->ignoresesskey = true;

        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $triggersettings = new \stdClass();
        $triggersettings->icon = self::MANUAL_TRIGGER1_ICON;
        $triggersettings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $triggersettings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $manualworkflow = $generator->create_manual_workflow($triggersettings);
        $step = $generator->create_step("instance1", "deletecourse", $manualworkflow->id);
        settings_manager::save_settings($step->id, settings_type::STEP, "deletecourse",
                ["maximumdeletionspercron" => 10]
        );

        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $manualworkflow->id);

        $this->course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);

        // Corrupt course.
        $DB->execute('UPDATE {course_modules} SET instance = 0');
        $this->trigger = trigger_manager::get_triggers_for_workflow($manualworkflow->id)[0];
    }

    /**
     * Test if the correct process error was put into the table.
     * @covers \tool_lifecycle\processor
     */
    public function test_process_error_in_table(): void {
        global $DB;
        $process = process_manager::manually_trigger_process($this->course->id, $this->trigger->id);

        // The delete course step really wants to print output.
        ob_start();
        $processor = new processor();
        $processor->process_courses();
        ob_end_clean();

        $records = $DB->get_records('tool_lifecycle_proc_error');

        $this->assertEquals(1, count($records));
        $this->assertEquals(0, $DB->count_records('tool_lifecycle_process'));

        $record = reset($records);

        $this->assertEquals($this->course->id, $record->courseid);
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $this->assertStringContainsString("Trying to get property 'id' of non-object", $record->errormessage);
        } else {
            $this->assertStringContainsString("Attempt to read property \"id\" on bool", $record->errormessage);
        }
        $this->assertEquals($process->id, $record->id);
    }

}
