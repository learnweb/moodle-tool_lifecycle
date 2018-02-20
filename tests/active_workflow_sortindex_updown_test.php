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

require_once(__DIR__ . '/generator/lib.php');
require_once(__DIR__ . '/../lib.php');

use tool_cleanupcourses\manager\workflow_manager;
use tool_cleanupcourses\entity\workflow;

/**
 * Tests the different state changes of the workflow sortindex for up and down action.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_workflow_sortindex_updown_testcase extends \advanced_testcase {

    private $workflow1;
    private $workflow2;
    private $workflow3;

    public function setUp() {
        $this->resetAfterTest(true);
        // Remove preset workflows.
        $workflows = workflow_manager::get_active_automatic_workflows();
        foreach ($workflows as $workflow) {
            workflow_manager::remove($workflow->id);
        }

        $this->workflow1 = tool_cleanupcourses_generator::create_workflow();
        $this->workflow2 = tool_cleanupcourses_generator::create_workflow();
        $this->workflow3 = tool_cleanupcourses_generator::create_workflow();

        $this->assertFalse($this->workflow1->active);
        $this->assertFalse($this->workflow2->active);
        $this->assertFalse($this->workflow3->active);
        $this->assertNull($this->workflow1->sortindex);
        $this->assertNull($this->workflow2->sortindex);
        $this->assertNull($this->workflow3->sortindex);
    }

    /**
     * Test to activate the first workflow.
     */
    public function test_activate_first() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertTrue(workflow_manager::is_active($this->workflow1->id));
        $this->assertEquals(1, $reloadworkflow->sortindex);
    }

    /**
     * Test to activate the first and the second workflow.
     */
    public function test_activate_second() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertTrue(workflow_manager::is_active($this->workflow2->id));
        $this->assertEquals(2, $reloadworkflow->sortindex);
    }

    /**
     * Test to activate all three workflow.
     */
    public function test_activate_third() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow3->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertTrue(workflow_manager::is_active($this->workflow3->id));
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put down the first workflow.
     */
    public function test_down_first() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(ACTION_DOWN_WORKFLOW, $this->workflow1->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put down the second workflow.
     */
    public function test_down_second() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(ACTION_DOWN_WORKFLOW, $this->workflow2->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);
    }

    /**
     * Test to put down the third workflow.
     */
    public function test_down_third() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(ACTION_DOWN_WORKFLOW, $this->workflow3->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put up the third workflow.
     */
    public function test_up_first() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(ACTION_UP_WORKFLOW, $this->workflow1->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put up the third workflow.
     */
    public function test_up_second() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(ACTION_UP_WORKFLOW, $this->workflow2->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);
    }

    /**
     * Test to put up the third workflow.
     */
    public function test_up_third() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow1->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow2->id);
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->workflow3->id);
        workflow_manager::handle_action(ACTION_UP_WORKFLOW, $this->workflow3->id);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow1->id);
        $this->assertEquals(1, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow2->id);
        $this->assertEquals(3, $reloadworkflow->sortindex);

        $reloadworkflow = workflow_manager::get_workflow($this->workflow3->id);
        $this->assertEquals(2, $reloadworkflow->sortindex);
    }

}
