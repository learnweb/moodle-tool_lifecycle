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

namespace tool_lifecycle;

use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\manager\process_manager;

/**
 * Tests that interactions are only accepted for the step a process currently sits on.
 *
 * @package    tool_lifecycle
 * @category   test
 * @copyright  2026 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class interaction_manager_test extends \advanced_testcase {
    /** @var \tool_lifecycle\local\entity\process Process under test. */
    private $process;

    /** @var \tool_lifecycle\local\entity\step_subplugin The email step instance (sortindex 2). */
    private $emailstep;

    /**
     * Creates a workflow with a createbackup step (index 1) followed by an email step (index 2) and a process.
     */
    private function create_process(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $workflow = $generator->create_workflow(['startdatedelay'], ['createbackup']);
        $this->emailstep = $generator->create_step('email', 'email', $workflow->id);
        $course = $this->getDataGenerator()->create_course();
        $this->process = $generator->create_process($course->id, $workflow->id);
    }

    /**
     * A step of the workflow must not be accepted while the process sits on another step.
     *
     * @covers \tool_lifecycle\local\manager\interaction_manager::handle_interaction
     */
    public function test_interaction_rejects_step_the_process_is_not_at(): void {
        global $DB;
        $this->create_process();
        // The process waits on the first step (createbackup), not on the email step.
        $DB->set_field('tool_lifecycle_process', 'stepindex', 1, ['id' => $this->process->id]);

        try {
            interaction_manager::handle_interaction($this->emailstep->id, $this->process->id, 'keep');
            $this->fail('Expected an exception for a step the process is not at.');
        } catch (\invalid_parameter_exception $e) {
            $this->assertStringContainsString(get_string('nostepfound', 'tool_lifecycle'), $e->getMessage());
        }
        // The process must neither be rolled back nor delayed.
        $this->assertNotNull(process_manager::get_process_by_id($this->process->id));
        $this->assertEquals(1, $DB->get_field('tool_lifecycle_process', 'stepindex', ['id' => $this->process->id]));
        $this->assertFalse($DB->record_exists('tool_lifecycle_delayed_workf', ['courseid' => $this->process->courseid]));
    }

    /**
     * The step the process currently sits on handles the interaction.
     *
     * @covers \tool_lifecycle\local\manager\interaction_manager::handle_interaction
     */
    public function test_interaction_accepts_current_step(): void {
        global $DB;
        $this->create_process();
        $DB->set_field('tool_lifecycle_process', 'stepindex', $this->emailstep->sortindex, ['id' => $this->process->id]);

        $this->assertTrue(interaction_manager::handle_interaction($this->emailstep->id, $this->process->id, 'keep'));
        // The email step answers "keep" with a rollback of the process.
        $this->assertNull(process_manager::get_process_by_id($this->process->id));
        $this->assertTrue($DB->record_exists('tool_lifecycle_delayed_workf', ['courseid' => $this->process->courseid]));
    }
}
