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
 * Tests the field is manual after activating workflows.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_workflow_is_manual_testcase extends \advanced_testcase {

    private $manual_workflow;
    private $automatic_workflow;

    public function setUp() {
        $this->resetAfterTest(true);

        $this->manual_workflow = tool_cleanupcourses_generator::create_manual_workflow();
        $this->automatic_workflow = tool_cleanupcourses_generator::create_workflow();

        $this->assertNull($this->manual_workflow->manual);
        $this->assertNull($this->automatic_workflow->manual);
    }

    /**
     * Test to activate the manual workflow.
     */
    public function test_activate_manual() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->manual_workflow->id);
        $reloadworkflow = workflow_manager::get_workflow($this->manual_workflow->id);
        $this->assertTrue(workflow_manager::is_active($this->manual_workflow->id));
        $this->assertTrue($reloadworkflow->manual);
    }

    /**
     * Test to activate the automatic workflow.
     */
    public function test_activate_automatic() {
        workflow_manager::handle_action(ACTION_WORKFLOW_ACTIVATE, $this->automatic_workflow->id);
        $reloadworkflow = workflow_manager::get_workflow($this->automatic_workflow->id);
        $this->assertTrue(workflow_manager::is_active($this->automatic_workflow->id));
        $this->assertEquals(false, $reloadworkflow->manual);
    }
}
