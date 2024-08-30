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
 * Manually triggers a process and tests if process courses proceeds the process as expected.
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator/lib.php');
require_once(__DIR__ . '/../lib.php');

use tool_lifecycle\action;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\processor;
use tool_lifecycle\settings_type;

/**
 * Manually triggers a process and tests if process courses proceeds the process as expected.
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class manually_triggered_process_test extends \advanced_testcase {
    /** Icon of the manual trigger. */
    const MANUAL_TRIGGER1_ICON = 't/up';
    /** Display name of the manual trigger. */
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    /** Capability of the manual trigger. */
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';


    /** @var trigger_subplugin[] $trigger Instances of the triggers under test. */
    private $trigger;
    /** @var array $course Instance of the course under test. */
    private $course;

    /**
     * Setup the testcase.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function setUp(): void {
        global $USER;

        // We do not need a sesskey check in theses tests.
        $USER->ignoresesskey = true;

        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        $triggersettings = new \stdClass();
        $triggersettings->icon = self::MANUAL_TRIGGER1_ICON;
        $triggersettings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $triggersettings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $manualworkflow = $generator->create_manual_workflow($triggersettings);
        $step = $generator->create_step("instance1", "createbackup", $manualworkflow->id);
        settings_manager::save_settings($step->id, settings_type::STEP, "createbackup",
                ["maximumbackupspercron" => 10]
        );

        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $manualworkflow->id);

        $this->course = $this->getDataGenerator()->create_course();
        $this->trigger = trigger_manager::get_triggers_for_workflow($manualworkflow->id)[0];
    }

    /**
     * Test to proceed a manually triggered process to step index 1.
     * @covers \tool_lifecycle\local\manager\process_manager test if manual process started
     */
    public function test_proceeding_of_manually_triggered_processes(): void {
        $process = process_manager::manually_trigger_process($this->course->id, $this->trigger->id);
        $this->assertEquals(0, $process->stepindex);

        $processor = new processor();
        $processor->process_courses();
        $process = process_manager::get_process_by_id($process->id);

        $this->assertNull($process);
    }

}
