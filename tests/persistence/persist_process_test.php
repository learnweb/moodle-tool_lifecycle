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

use \tool_lifecycle\manager\process_manager;

/**
 * Tests creating storing and retrieving a process object.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_persist_process_testcase extends \advanced_testcase {

    /** workflow */
    private $workflow;

    /** course */
    private $course;

    public function setUp() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        $this->workflow = $generator->create_workflow_with_steps();
        $this->course = $this->getDataGenerator()->create_course();
    }

    /**
     * Test the creation of a process.
     */
    public function test_create() {
        $process = process_manager::create_process($this->course->id, $this->workflow->id);
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
        $process = process_manager::create_process($this->course->id, $this->workflow->id);
        $this->assertFalse($process->waiting);
        process_manager::set_process_waiting($process);
        $loadedprocess = process_manager::get_process_by_id($process->id);
        $this->assertTrue($loadedprocess->waiting);
    }

    /**
     * Tests deletion of a process when rolledback.
     */
    public function test_process_rollback() {
        $process = process_manager::create_process($this->course->id, $this->workflow->id);
        process_manager::rollback_process($process);
        $loadedprocess = process_manager::get_process_by_id($process->id);
        $this->assertNull($loadedprocess);
    }

    /**
     * Tests proceeding a process to the next step.
     */
    public function test_process_proceed() {
        $process = process_manager::create_process($this->course->id, $this->workflow->id);
        $this->assertEquals(0, $process->stepindex);
        process_manager::proceed_process($process);
        $loadedprocess = process_manager::get_process_by_id($process->id);
        $this->assertEquals(1, $loadedprocess->stepindex);
        process_manager::proceed_process($process);
        $loadedprocess = process_manager::get_process_by_id($process->id);
        $this->assertEquals(2, $loadedprocess->stepindex);
    }

}
