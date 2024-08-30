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
 * Tests the different state changes of the workflow sortindex for up and down action.
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator/lib.php');
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/workflow_actions_testcase.php');

use tool_lifecycle\local\manager\workflow_manager;

/**
 * Tests the different state changes of the workflow sortindex for up and down action.
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class active_workflow_sortindex_updown_test extends workflow_actions_testcase {

    /**
     * Test to put down the first workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager move actions down
     */
    public function test_down_first(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(action::DOWN_WORKFLOW, $this->workflow1->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put down the second workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager move actions down
     */
    public function test_down_second(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(action::DOWN_WORKFLOW, $this->workflow2->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);
    }

    /**
     * Test to put down the third workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager move actions down
     */
    public function test_down_third(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(action::DOWN_WORKFLOW, $this->workflow3->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put up the third workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager move actions up
     */
    public function test_up_first(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(action::UP_WORKFLOW, $this->workflow1->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put up the third workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager move actions up
     */
    public function test_up_second(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(action::UP_WORKFLOW, $this->workflow2->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put up the third workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager move actions up
     */
    public function test_up_third(): void {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(action::UP_WORKFLOW, $this->workflow3->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);
    }

}
