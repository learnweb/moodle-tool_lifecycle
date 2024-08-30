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
 * Tests the field is manual after activating workflows.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator/lib.php');
require_once(__DIR__ . '/../lib.php');

use mod_bigbluebuttonbn\settings;
use tool_lifecycle\local\backup\backup_lifecycle_workflow;
use tool_lifecycle\local\backup\restore_lifecycle_workflow;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\entity\workflow;

/**
 * Tests the field is manual after activating workflows.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class backup_and_restore_workflow_test extends \advanced_testcase {

    /** @var $workflow workflow */
    private $workflow;

    /** @var workflow[] $existingworkflows Array of workflows created in this test. */
    private $existingworkflows = [];

    /**
     * Setup the testcase.
     * @throws \coding_exception
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $this->workflow = $generator->create_workflow(['startdatedelay', 'categories'], ['email', 'createbackup', 'deletecourse']);
        $category = $this->getDataGenerator()->create_category();
        foreach (trigger_manager::get_triggers_for_workflow($this->workflow->id) as $trigger) {
            if ($trigger->subpluginname === 'categories') {
                settings_manager::save_setting($trigger->id, settings_type::TRIGGER, 'categories',
                        'categories', $category->id);
            }
        }

        foreach (workflow_manager::get_workflows() as $existingworkflow) {
            $this->existingworkflows[] = $existingworkflow->id;
        }
    }

    /**
     * Test to activate the manual workflow.
     * @covers \tool_lifecycle\local\manager\workflow_manager check if backup is created
     */
    public function test_backup_workflow(): void {
        $backuptask = new backup_lifecycle_workflow($this->workflow->id);
        $backuptask->execute();
        $filename = $backuptask->get_temp_filename();
        $handle = fopen($filename, 'r+b');
        $xmldata = fread($handle, filesize($filename));
        fclose($handle);

        $restoretask = new restore_lifecycle_workflow($xmldata);
        $errors = $restoretask->execute();

        $this->assertEmpty($errors, 'There should be no errors during restore');
        $workflows = workflow_manager::get_workflows();
        $this->assertCount(count($this->existingworkflows) + 1, $workflows);
        $newworkflow = null;
        foreach ($workflows as $workflow) {
            if (!array_key_exists($workflow->id, $this->existingworkflows)) {
                $newworkflow = $workflow;
            }
        }
        $this->assertNotNull($newworkflow);

        foreach (['title', 'displaytitle', 'manual'] as $property) {
            $this->assertEquals($this->workflow->$property, $newworkflow->$property);
        }
        foreach (['timeactive', 'timedeactive', 'sortindex'] as $property) {
            $this->assertEquals(null, $newworkflow->$property);
        }

        $oldsteps = step_manager::get_step_instances($this->workflow->id);
        $newsteps = step_manager::get_step_instances($newworkflow->id);
        $this->assertCount(count($oldsteps), $newsteps);
        while (count($oldsteps) > 0) {
            $oldstep = array_pop($oldsteps);
            $newstep = array_pop($newsteps);
            foreach (['subpluginname', 'sortindex'] as $property) {
                $this->assertEquals($oldstep->$property, $newstep->$property);
            }
            $this->assertEquals($newworkflow->id, $newstep->workflowid);
            $oldsettings = settings_manager::get_settings($oldstep->id, settings_type::STEP);
            $newsettings = settings_manager::get_settings($newstep->id, settings_type::STEP);
            $lib = \tool_lifecycle\local\manager\lib_manager::get_step_lib($newstep->subpluginname);
            $settingsdef = $lib->instance_settings();
            foreach ($settingsdef as $def) {
                if (array_key_exists($def->name, $oldsettings)) {
                    $this->assertArrayHasKey($def->name, $newsettings);
                    $this->assertEquals($oldsettings[$def->name], $newsettings[$def->name]);
                }
            }
        }

        $oldtrigger = trigger_manager::get_triggers_for_workflow($this->workflow->id);
        $newtrigger = trigger_manager::get_triggers_for_workflow($newworkflow->id);
        $this->assertCount(count($oldtrigger), $newtrigger);
        while (count($oldtrigger) > 0) {
            $oldtrig = array_pop($oldtrigger);
            $newtrig = array_pop($newtrigger);
            foreach (['subpluginname', 'sortindex'] as $property) {
                $this->assertEquals($oldtrig->$property, $newtrig->$property);
            }
            $this->assertEquals($newworkflow->id, $newtrig->workflowid);
            $oldsettings = settings_manager::get_settings($oldtrig->id, settings_type::TRIGGER);
            $newsettings = settings_manager::get_settings($newtrig->id, settings_type::TRIGGER);
            $lib = \tool_lifecycle\local\manager\lib_manager::get_trigger_lib($newtrig->subpluginname);
            $settingsdef = $lib->instance_settings();
            foreach ($settingsdef as $def) {
                if (array_key_exists($def->name, $oldsettings)) {
                    $this->assertArrayHasKey($def->name, $newsettings);
                    $this->assertEquals($oldsettings[$def->name], $newsettings[$def->name]);
                }
            }
        }
    }
}
