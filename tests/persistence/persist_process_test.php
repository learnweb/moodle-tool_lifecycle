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

require_once(__DIR__ . '/../../lib.php');

use \tool_cleanupcourses\manager\process_manager;

/**
 * Tests creating storing and retrieving a process object.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses_persist
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_persist_process_testcase extends \advanced_testcase {

    /** trigger_subplugin */
    private $trigger;

    /** course */
    private $course;

    public function setUp() {
        $this->resetAfterTest(true);
        $this->trigger = tool_cleanupcourses_generator::create_trigger_with_workflow();
        $this->course = $this->getDataGenerator()->create_course();
    }

    /**
     * Test the creation of a process.
     */
    public function test_create() {
        $process = process_manager::create_process($this->course->id, $this->trigger);
        $this->assertNotNull($process);
        $this->assertNotEmpty($process->id);
        $processes = process_manager::get_processes();
        $this->assertEquals(1, count($processes));
        $loadedprocess = $processes[0];
        $this->assertEquals($process, $loadedprocess);
    }

    /**
     * Tests setting a process on waiting.
     */
    public function test_process_waiting() {
        $process = process_manager::create_process($this->course->id, $this->trigger);
        $this->assertFalse($process->waiting);
        process_manager::set_process_waiting($process);
        $loadedprocess = process_manager::get_process_by_id($process->id);
        $this->assertTrue($loadedprocess->waiting);
    }

    /**
     * Tests deletion of a process when rolledback.
     */
    public function test_process_rollback() {
        $process = process_manager::create_process($this->course->id, $this->trigger);
        process_manager::rollback_process($process);
        $loadedprocess = process_manager::get_process_by_id($process->id);
        $this->assertNull($loadedprocess);
    }

    /**
     * Tests proceeding a process to the next step.
     */
    public function test_process_proceed() {
        $process = process_manager::create_process($this->course->id, $this->trigger);
        $step1 = $process->stepid;
        process_manager::proceed_process($process);
        $step2 = $process->stepid;
        $loadedprocess = process_manager::get_process_by_id($process->id);
        $this->assertEquals($step2, $loadedprocess->stepid);
        $this->assertNotEquals($step1, $loadedprocess->stepid);
        $this->assertNotEmpty($loadedprocess->stepid);
    }

}
