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

use tool_lifecycle\action;
use tool_lifecycle\manager\workflow_manager;
use tool_lifecycle\entity\workflow;

/**
 * Tests the field is manual after activating workflows.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_workflow_is_manual_testcase extends \advanced_testcase {

    /** Icon of the trigger. */
    const MANUAL_TRIGGER1_ICON = 't/up';
    /** Action name of the trigger. */
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    /** Capability of the trigger. */
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';

    /** Instance of the manual workflow */
    private $manualworkflow;
    /** Instance of the automatic workflow */
    private $automaticworkflow;

    /**
     * Setup the testcase.
     * @throws coding_exception
     */
    public function setUp() {
        global $USER;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        $settings = new stdClass();
        $settings->icon = self::MANUAL_TRIGGER1_ICON;
        $settings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $settings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $this->manualworkflow = $generator->create_manual_workflow($settings);
        $this->automaticworkflow = $generator->create_workflow();

        $this->assertNull($this->manualworkflow->manual);
        $this->assertNull($this->automaticworkflow->manual);

        // We do not need a sesskey check in theses tests.
        $USER->ignoresesskey = true;
    }

    /**
     * Test to activate the manual workflow.
     */
    public function test_activate_manual() {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->manualworkflow->id);
        $reloadworkflow = workflow_manager::get_workflow($this->manualworkflow->id);
        $this->assertTrue(workflow_manager::is_active($this->manualworkflow->id));
        $this->assertTrue($reloadworkflow->manual);
    }

    /**
     * Test to activate the automatic workflow.
     */
    public function test_activate_automatic() {
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->automaticworkflow->id);
        $reloadworkflow = workflow_manager::get_workflow($this->automaticworkflow->id);
        $this->assertTrue(workflow_manager::is_active($this->automaticworkflow->id));
        $this->assertEquals(false, $reloadworkflow->manual);
    }
}
