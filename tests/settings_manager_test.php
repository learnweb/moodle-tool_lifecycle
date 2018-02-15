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

use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\settings_manager;

/**
 * Tests the settings manager.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_settings_manager_testcase extends \advanced_testcase {

    /** step_subplugin */
    private $step;
    private $trigger;

    const EMAIL_VALUE = 'value';
    const STARTDELAY_VALUE = 100;

    public function setUp() {
        $this->resetAfterTest(false);
        $workflow = tool_cleanupcourses_generator::create_workflow();
        $this->step = new step_subplugin('instancename', 'email', $workflow->id);
        step_manager::insert_or_update($this->step);
        $this->trigger = \tool_cleanupcourses\manager\trigger_manager::get_trigger_for_workflow($workflow->id);
    }

    /**
     * Test setting and getting settings data for steps.
     */
    public function test_set_get_step_settings() {
        $data = new stdClass();
        $data->subject = self::EMAIL_VALUE ;
        settings_manager::save_settings($this->step->id, SETTINGS_TYPE_STEP, $this->step->subpluginname, $data);
        $settings = settings_manager::get_settings($this->step->id, SETTINGS_TYPE_STEP);
        $this->assertArrayHasKey('subject', $settings, 'No key \'subject\' in returned settings array');
        $this->assertEquals(self::EMAIL_VALUE, $settings['subject']);
    }

    /**
     * Test setting and getting settings data for steps.
     */
    public function test_set_get_trigger_settings() {
        $data = new stdClass();
        $data->delay = self::STARTDELAY_VALUE;
        settings_manager::save_settings($this->trigger->id, SETTINGS_TYPE_TRIGGER, $this->trigger->subpluginname, $data);
        $settings = settings_manager::get_settings($this->trigger->id, SETTINGS_TYPE_TRIGGER);
        $this->assertArrayHasKey('delay', $settings, 'No key \'delay\' in returned settings array');
        $this->assertEquals(self::STARTDELAY_VALUE, $settings['delay']);
    }

}