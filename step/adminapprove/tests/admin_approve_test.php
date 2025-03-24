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

namespace lifecyclestep_adminapprove;

namespace lifecyclestep_adminapprove;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../../tests/generator/lib.php');

use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\task\lifecycle_task;

/**
 * Tests the admin approve step.
 *
 * @package    lifecyclestep_adminapprove
 * @group      lifecyclestep_adminapprove
 * @group      lifecyclestep
 * @category   test
 * @covers     \tool_lifecycle\step\adminapprove
 * @copyright  2019 Justus Dieckmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class admin_approve_test extends \advanced_testcase {

    /**
     * Starts a manual trigger and checks that one mail is send.
     * @covers \tool_lifecycle\step\adminapprove
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws dml_transaction_exception
     * @throws moodle_exception
     */
    public function test_admin_mail(): void {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $workflow = $generator->create_workflow([], []);
        $trigger = $generator->create_trigger('manual', 'manual', $workflow->id);
        $generator->create_step('adminapprove', 'adminapprove', $workflow->id);
        workflow_manager::activate_workflow($workflow->id);

        // Create 4 courses.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $course4 = $this->getDataGenerator()->create_course();

        process_manager::manually_trigger_process($course1->id, $trigger->id);
        process_manager::manually_trigger_process($course2->id, $trigger->id);
        process_manager::manually_trigger_process($course3->id, $trigger->id);
        process_manager::manually_trigger_process($course4->id, $trigger->id);

        // Prevent output from the task execution.
        $this->setOutputCallback(function() {
        });

        // Create an email sink to query it after the processing.
        $sink = $this->redirectEmails();
        $task = new lifecycle_task();
        $task->execute();
        $this->assertCount(1, $sink->get_messages());
        $sink->close();
    }
}
