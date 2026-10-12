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

namespace lifecyclestep_duplicate;

use PHPUnit\Framework\Attributes\Group;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\step\duplicate;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/lifecycle/step/duplicate/lib.php');

/**
 * Tests for the duplicate step.
 *
 * @package    lifecyclestep_duplicate
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    /**
     * Run process_course() with a duplication that fails with the given error code.
     *
     * @param string $errorcode error code of the moodle_exception thrown by the duplication
     * @return array step response and process data
     */
    private function process_with_failing_duplication(string $errorcode): array {
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $course = $this->getDataGenerator()->create_course();
        $workflow = $generator->create_workflow([], []);
        $step = $generator->create_step('duplicate', 'duplicate', $workflow->id);
        $process = $generator->create_process($course->id, $workflow->id);
        process_data_manager::set_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSEFULLNAME, 'Kopie');
        process_data_manager::set_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSESHORTNAME, 'kopie');

        $lib = $this->getMockBuilder(duplicate::class)->onlyMethods(['duplicate_course'])->getMock();
        $lib->method('duplicate_course')->willThrowException(new \moodle_exception($errorcode));

        $response = $lib->process_course($process->id, $step->id, $course);
        $shortname = process_data_manager::get_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSESHORTNAME);
        return [$response, $shortname];
    }

    #[Group('baseline')]
    /**
     * A failed duplication must not be reported as success.
     *
     * @covers \tool_lifecycle\step\duplicate::process_course
     */
    public function test_failed_duplication_is_not_proceeded(): void {
        $this->resetAfterTest();

        $this->expectException(\moodle_exception::class);
        $this->process_with_failing_duplication('nopermissions');
    }

    #[Group('baseline')]
    /**
     * A taken shortname lets the step wait for a new shortname.
     *
     * @covers \tool_lifecycle\step\duplicate::process_course
     */
    public function test_shortname_taken_waits_for_new_shortname(): void {
        $this->resetAfterTest();

        [$response, $shortname] = $this->process_with_failing_duplication('shortnametaken');

        $this->assertEquals(step_response::waiting(), $response);
        $this->assertEquals('', $shortname);
    }
}
