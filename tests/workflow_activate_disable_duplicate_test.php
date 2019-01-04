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

use tool_lifecycle\manager\workflow_manager;
use tool_lifecycle\entity\workflow;

/**
 * Tests activating, disabling and duplicating workflows
 *
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_workflow_activate_disable_duplicate_testcase extends workflow_actions_test_setup {

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

    // @todo
}
