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

namespace lifecyclestep_makeinvisible;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../tests/generator/lib.php');

use PHPUnit\Framework\Attributes\CoversNothing;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\processor;

/**
 * Tests the make invisible step.
 *
 * @package    lifecyclestep_makeinvisible
 * @copyright  2019 Justus Dieckmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class make_invisible_test extends \advanced_testcase {

    /**
     * Setup the testcase.
     */
    public function setUp(): void {
        global $USER;

        parent::setUp();

        $this->resetAfterTest(true);

        // We do not need a sesskey check in theses tests.
        $USER->ignoresesskey = true;
    }

    /**
     * Test the visibility of courses after the step is executed.
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \moodle_exception
     */
    #[CoversNothing]
    public function test_make_invisible(): void {
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $workflow = $generator->create_workflow([], []);
        $trigger = $generator->create_trigger('manual', 'manual', $workflow->id);
        $generator->create_step('makeinvisible', 'makeinvisible', $workflow->id);
        $step = $generator->create_step('email', 'email', $workflow->id);
        settings_manager::save_settings($step->id, \tool_lifecycle\settings_type::STEP, 'email', null);
        workflow_manager::handle_action(\tool_lifecycle\action::WORKFLOW_ACTIVATE, $workflow->id);

        // Course1 is visible in an visible category. It should be hidden after step and shown after rollback.
        $course1 = $this->getDataGenerator()->create_course();
        // Course2 is invisible in an visible category. It should be hidden after step and hidden after rollback.
        $course2 = $this->getDataGenerator()->create_course();
        course_change_visibility($course2->id, false);
        $cat = \core_course_category::create(['name' => 'aaa']);
        // Course3 is visible in an (later) invisible category. It should be hidden after rollback.
        $course3 = $this->getDataGenerator()->create_course(['category' => $cat->id]);
        // Course4 is invisible, but changed to shown after step. It should remain shown after rollback.
        $course4 = $this->getDataGenerator()->create_course();
        course_change_visibility($course4->id, false);

        $process1 = process_manager::manually_trigger_process($course1->id, $trigger->id);
        $process2 = process_manager::manually_trigger_process($course2->id, $trigger->id);
        $process3 = process_manager::manually_trigger_process($course3->id, $trigger->id);
        $process4 = process_manager::manually_trigger_process($course4->id, $trigger->id);

        $processor = new processor();
        $processor->process_courses();

        $course1 = get_course($course1->id);
        $course2 = get_course($course2->id);

        $this->assertFalse((bool) $course1->visible);
        $this->assertFalse((bool) $course2->visible);

        course_change_visibility($course4->id, true);
        $cat->hide();

        $process1 = process_manager::get_process_by_id($process1->id);
        $process2 = process_manager::get_process_by_id($process2->id);
        $process3 = process_manager::get_process_by_id($process3->id);
        $process4 = process_manager::get_process_by_id($process4->id);

        process_manager::rollback_process($process1);
        process_manager::rollback_process($process2);
        process_manager::rollback_process($process3);
        process_manager::rollback_process($process4);

        $course1 = get_course($course1->id);
        $course2 = get_course($course2->id);
        $course3 = get_course($course3->id);
        $course4 = get_course($course4->id);

        $this->assertTrue((bool) $course1->visible);
        $this->assertFalse((bool) $course2->visible);
        $this->assertFalse((bool) $course3->visible);
        $this->assertTrue((bool) $course4->visible);
    }

}
