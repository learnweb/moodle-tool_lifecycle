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
 * Tests the settings manager.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\workflow_manager;

/**
 * Tests the settings manager.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class settings_manager_test extends \advanced_testcase {

    /** @var step_subplugin $step Instance of the step plugin. */
    private $step;
    /** @var trigger_subplugin $trigger Instance of the trigger plugin. */
    private $trigger;
    /** @var workflow $workflow Instance of the workflow. */
    private $workflow;

    /** @var string constant value for the email. */
    const EMAIL_VALUE = 'value';
    /** @var int constant value for start delay. */
    const STARTDELAY_VALUE = 100;

    /**
     * Setup the testcase.
     * @throws \coding_exception
     */
    public function setUp(): void {
        $this->resetAfterTest(false);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        $this->workflow = $generator->create_workflow();
        $this->step = new step_subplugin('instancename', 'email', $this->workflow->id);
        step_manager::insert_or_update($this->step);
        $this->trigger = \tool_lifecycle\local\manager\trigger_manager::get_triggers_for_workflow($this->workflow->id)[0];
    }

    /**
     * Test setting and getting settings data for steps.
     * @covers \tool_lifecycle\local\manager\settings_manager
     */
    public function test_set_get_step_settings(): void {
        $data = new \stdClass();
        $data->subject = self::EMAIL_VALUE;
        settings_manager::save_settings($this->step->id, settings_type::STEP, $this->step->subpluginname, $data);
        $settings = settings_manager::get_settings($this->step->id, settings_type::STEP);
        $this->assertArrayHasKey('subject', $settings, 'No key \'subject\' in returned settings array');
        $this->assertEquals(self::EMAIL_VALUE, $settings['subject']);
    }

    /**
     * Test setting and getting settings data for triggers.
     * @covers \tool_lifecycle\local\manager\settings_manager
     */
    public function test_set_get_trigger_settings(): void {
        $data = new \stdClass();
        $data->delay = self::STARTDELAY_VALUE;
        settings_manager::save_settings($this->trigger->id, settings_type::TRIGGER, $this->trigger->subpluginname, $data);
        $settings = settings_manager::get_settings($this->trigger->id, settings_type::TRIGGER);
        $this->assertArrayHasKey('delay', $settings, 'No key \'delay\' in returned settings array');
        $this->assertEquals(self::STARTDELAY_VALUE, $settings['delay']);
    }

    /**
     * Test correct removal of setting, if steps, triggers or workflows are deleted.
     * @covers \tool_lifecycle\local\manager\settings_manager
     */
    public function test_remove_workflow(): void {
        global $DB;
        $data = new \stdClass();
        $data->subject = self::EMAIL_VALUE;
        settings_manager::save_settings($this->step->id, settings_type::STEP, $this->step->subpluginname, $data);
        $data = new \stdClass();
        $data->delay = 100;
        settings_manager::save_settings($this->trigger->id, settings_type::TRIGGER, $this->trigger->subpluginname, $data);
        $settingsstep = $DB->get_records('tool_lifecycle_settings', ['instanceid' => $this->step->id,
            'type' => settings_type::STEP, ]);
        $this->assertNotEmpty($settingsstep);
        $settingstrigger = $DB->get_records('tool_lifecycle_settings', ['instanceid' => $this->trigger->id,
            'type' => settings_type::TRIGGER, ]);
        $this->assertNotEmpty($settingstrigger);
        workflow_manager::remove($this->workflow->id);
        $settingsstep = $DB->get_records('tool_lifecycle_settings', ['instanceid' => $this->step->id,
            'type' => settings_type::STEP, ]);
        $this->assertEmpty($settingsstep);
        $settingstrigger = $DB->get_records('tool_lifecycle_settings', ['instanceid' => $this->trigger->id,
            'type' => settings_type::TRIGGER, ]);
        $this->assertEmpty($settingstrigger);
    }

}
