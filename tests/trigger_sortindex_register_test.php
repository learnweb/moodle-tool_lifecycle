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

use \tool_cleanupcourses\manager\trigger_manager;

/**
 * Tests the different state changes of the trigger sortindex for registering and deregistering new trigger.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_trigger_sortindex_register_testcase extends \advanced_testcase {

    const EXISTINGTRIGGER = 'startdatedelay';

    public function setUp() {
        $this->resetAfterTest(true);
        tool_cleanupcourses_generator::setup_test_plugins();
        // trigger_manager::register(self::EXISTINGTRIGGER);
    }

    /**
     * Test the initial setup of this testcase.
     */
    public function test_sortindex_init() {
        global $DB;
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'sortindex' => 3)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array(
                'subpluginname' => self::EXISTINGTRIGGER,
                'sortindex' => null,
                'enabled' => false)
            )
        );
    }

    /**
     * Test the proper registering of a new trigger subplugin.
     */
    public function test_enable_after_register() {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('subpluginname' => self::EXISTINGTRIGGER), 'id');
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, $record->id);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'sortindex' => 3)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array(
                'subpluginname' => self::EXISTINGTRIGGER,
                'sortindex' => 4,
                'enabled' => true)
            )
        );
    }

    /**
     * Test placing a new registered trigger subplugin in the middle.
     */
    public function test_placing_enabled_trigger_in_middle() {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('subpluginname' => self::EXISTINGTRIGGER), 'id');
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'sortindex' => 4)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array(
                'subpluginname' => self::EXISTINGTRIGGER,
                'sortindex' => 3,
                'enabled' => true)
            )
        );
    }

    /**
     * Test placing a new registered trigger subplugin at top.
     */
    public function test_placing_enabled_trigger_at_top() {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('subpluginname' => self::EXISTINGTRIGGER), 'id');
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'sortindex' => 3)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'sortindex' => 4)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array(
                'subpluginname' => self::EXISTINGTRIGGER,
                'sortindex' => 1,
                'enabled' => true)
            )
        );
    }

    /**
     * Test the proper deregistering of a trigger subplugin, when disabled.
     */
    public function test_deregister_disabled_trigger() {
        // trigger_manager::deregister(self::EXISTINGTRIGGER);
        $this->check_new_trigger_correctly_deregistered();
    }

    /**
     * Test the proper deregistering of a trigger subplugin, when enabled and with highest sortindex.
     */
    public function test_deregister_enabled_trigger_at_bottom() {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('subpluginname' => self::EXISTINGTRIGGER), 'id');
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, $record->id);
        // trigger_manager::deregister(self::EXISTINGTRIGGER);
        $this->check_new_trigger_correctly_deregistered();
    }

    /**
     * Test the proper deregistering of a trigger subplugin, when enabled and with sortindex in the middle.
     */
    public function test_deregister_enabled_trigger_in_middle() {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('subpluginname' => self::EXISTINGTRIGGER), 'id');
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        // trigger_manager::deregister(self::EXISTINGTRIGGER);
        $this->check_new_trigger_correctly_deregistered();
    }

    /**
     * Test the proper deregistering of a trigger subplugin, when enabled and with sortindex of 1.
     */
    public function test_deregister_enabled_trigger_at_top() {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('subpluginname' => self::EXISTINGTRIGGER), 'id');
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        trigger_manager::handle_action(ACTION_UP_TRIGGER, $record->id);
        // trigger_manager::deregister(self::EXISTINGTRIGGER);
        $this->check_new_trigger_correctly_deregistered();
    }

    /**
     * Checks that the new trigger is correctly deregistered and
     * that the sortindizes of all other triggers are in the initial order.
     */
    private function check_new_trigger_correctly_deregistered() {
        global $DB;
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'sortindex' => 1)),
            'First sortindex is wrong.');
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'sortindex' => 2)),
            'Second sortindex is wrong.');
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'sortindex' => 3)),
            'Third sortindex is wrong.');
        $this->assertEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('subpluginname' => self::EXISTINGTRIGGER)));
    }
}
