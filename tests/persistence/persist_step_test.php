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
 * Tests creating storing and retrieving a step object.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

use tool_lifecycle\action;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\step_manager;

/**
 * Tests creating storing and retrieving a step object.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class persist_step_test extends \advanced_testcase {

    /** @var workflow $workflow Instance of the workflow. */
    private $workflow;

    /** @var tool_lifecycle_generator $generator Instance of the test generator. */
    private $generator;

    /**
     * Setup the testcase.
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
        $this->generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        $this->workflow = $this->generator->create_workflow();
    }

    /**
     * Test that after an insert the id from the database is set within the step object.
     * @covers \tool_lifecycle\local\manager\step_manager
     */
    public function test_add_step(): void {
        $step = $this->generator->create_step(
            'instance1',
            'subpluginname',
            $this->workflow->id);
        $this->assertNotEmpty($step->id);
        $this->assertEquals($this->workflow->id, $step->workflowid);
        $this->assertEquals(1, $step->sortindex);
    }

    /**
     * Test that sortindizes are created correclty when creating multiple steps.
     * @covers \tool_lifecycle\local\manager\step_manager
     */
    public function test_add_multiple_steps(): void {
        $step1 = $this->generator->create_step(
            'instance1',
            'subpluginname',
            $this->workflow->id);
        $step2 = $this->generator->create_step(
            'instance2',
            'subpluginname',
            $this->workflow->id);
        $step3 = $this->generator->create_step(
            'instance3',
            'subpluginname',
            $this->workflow->id);
        $this->assertEquals(1, $step1->sortindex);
        $this->assertEquals(2, $step2->sortindex);
        $this->assertEquals(3, $step3->sortindex);
    }

    /**
     * Test that the step can be removed correctly.
     * @covers \tool_lifecycle\local\manager\step_manager
     */
    public function test_remove_step(): void {
        $step1 = $this->generator->create_step(
            'instance1',
            'subpluginname',
            $this->workflow->id);
        $step2 = $this->generator->create_step(
            'instance2',
            'subpluginname',
            $this->workflow->id);
        $step3 = $this->generator->create_step(
            'instance3',
            'subpluginname',
            $this->workflow->id);
        // Delete first step.
        step_manager::handle_action(action::STEP_INSTANCE_DELETE, $step1->id, $this->workflow->id);
        $step1 = step_manager::get_step_instance($step1->id);
        $step2 = step_manager::get_step_instance($step2->id);
        $step3 = step_manager::get_step_instance($step3->id);
        $this->assertNull($step1);
        $this->assertEquals(1, $step2->sortindex);
        $this->assertEquals(2, $step3->sortindex);
        // Delete third step.
        step_manager::handle_action(action::STEP_INSTANCE_DELETE, $step3->id, $this->workflow->id);
        $step3 = step_manager::get_step_instance($step3->id);
        $step2 = step_manager::get_step_instance($step2->id);
        $this->assertNull($step3);
        $this->assertEquals(1, $step2->sortindex);
    }

    /**
     * Test that sortindizes are still created correctly, when some steps were already removed.
     * @covers \tool_lifecycle\local\manager\step_manager
     */
    public function test_add_after_remove_step(): void {
        $step1 = $this->generator->create_step(
            'instance1',
            'subpluginname',
            $this->workflow->id);
        $step2 = $this->generator->create_step(
            'instance2',
            'subpluginname',
            $this->workflow->id);

        // Delete first step.
        step_manager::handle_action(action::STEP_INSTANCE_DELETE, $step1->id, $this->workflow->id);

        $step3 = $this->generator->create_step(
            'instance3',
            'subpluginname',
            $this->workflow->id);

        $step1 = step_manager::get_step_instance($step1->id);
        $step2 = step_manager::get_step_instance($step2->id);
        $step3 = step_manager::get_step_instance($step3->id);
        $this->assertNull($step1);
        $this->assertEquals(1, $step2->sortindex);
        $this->assertEquals(2, $step3->sortindex);
    }

}
