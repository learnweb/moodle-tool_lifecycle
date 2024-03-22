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
 * Tests activating, disabling and duplicating workflows
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator/lib.php');
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/workflow_actions_testcase.php');

use tool_lifecycle\local\manager\workflow_manager;

/**
 * Tests activating, disabling and duplicating workflows
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class workflow_activate_disable_duplicate_test extends workflow_actions_testcase {

    /**
     * Test to activate the first workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager
     */
    public function test_activate_first(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertTrue(workflow_manager::is_active($this->workflow1->id));
        $this->assertEquals(1, $reloadworkflow->sortindex);
    }

    /**
     * Test to activate the first and the second workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager
     */
    public function test_activate_second(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertTrue(workflow_manager::is_active($this->workflow2->id));
        $this->assertEquals(2, $reloadworkflow->sortindex);
    }

    /**
     * Test to activate all three workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager
     */
    public function test_activate_third(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow3->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertTrue(workflow_manager::is_active($this->workflow3->id));
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to deactivate the first workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager
     */
    public function test_deactivate_first(): void {
        workflow_manager::handle_action(action::WORKFLOW_ABORTDISABLE, $this->workflow1->id);
        $this->assertFalse(workflow_manager::is_active($this->workflow1->id));
    }

    /**
     * Test to duplicate the first workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager
     */
    public function test_duplicate_first(): void {
        workflow_manager::handle_action(action::WORKFLOW_DUPLICATE, $this->workflow1->id);
        $workflows = workflow_manager::get_workflows();
        $this->assertCount(4, $workflows);

        // Retrieve the duplicated workflow.
        $duplicate = null;
        $existingworkflowids = [$this->workflow1->id, $this->workflow2->id, $this->workflow3->id];
        foreach ($workflows as $workflow) {
            if (!array_search($workflow->id, $existingworkflowids)) {
                $duplicate = $workflow;
                break;
            }
        }
        $this->assertEquals($this->workflow1->displaytitle, $duplicate->displaytitle);
        $workflow1stepcount = count(\tool_lifecycle\local\manager\step_manager::get_step_instances($this->workflow1->id));
        $duplicatestepcount = count(\tool_lifecycle\local\manager\step_manager::get_step_instances($duplicate->id));
        $this->assertEquals($workflow1stepcount, $duplicatestepcount);
    }

}
