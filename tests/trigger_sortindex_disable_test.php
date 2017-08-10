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
 * Tests the different state changes of the trigger sortindex for enable and disable action.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_trigger_sortindex_disable_testcase extends \advanced_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
        tool_cleanupcourses_generator::setup_test_plugins();
    }

    /**
     * Test to disable the first subplugin.y
     */
    public function test_disable_first() {
        global $DB;
        trigger_manager::handle_action(ACTION_DISABLE_TRIGGER, 1);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 1, 'enabled' => 0, 'sortindex' => null)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 2, 'enabled' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 3, 'enabled' => 1, 'sortindex' => 2)));
    }

    /**
     * Test to disable the second subplugin.
     */
    public function test_disable_second() {
        global $DB;
        trigger_manager::handle_action(ACTION_DISABLE_TRIGGER, 2);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 1, 'enabled' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 2, 'enabled' => 0, 'sortindex' => null)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 3, 'enabled' => 1, 'sortindex' => 2)));
    }

    /**
     * Test to disable the third subplugin.
     */
    public function test_disable_third() {
        global $DB;
        trigger_manager::handle_action(ACTION_DISABLE_TRIGGER, 3);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 1, 'enabled' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 2, 'enabled' => 1, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger',
            array('id' => 3, 'enabled' => 0, 'sortindex' => null)));
    }

    /**
     * Test to disable and enable the first subplugin.
     */
    public function test_disable_and_enable_first() {
        global $DB;
        trigger_manager::handle_action(ACTION_DISABLE_TRIGGER, 1);
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, 1);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'enabled' => 1, 'sortindex' => 3)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'enabled' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'enabled' => 1, 'sortindex' => 2)));
    }

    /**
     * Test to disable and enable the second subplugin.
     */
    public function test_disable_and_enable_second() {
        global $DB;
        trigger_manager::handle_action(ACTION_DISABLE_TRIGGER, 2);
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, 2);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'enabled' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'enabled' => 1, 'sortindex' => 3)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'enabled' => 1, 'sortindex' => 2)));
    }

    /**
     * Test to disable and enable the third subplugin.
     */
    public function test_disable_and_enable_third() {
        global $DB;
        trigger_manager::handle_action(ACTION_DISABLE_TRIGGER, 3);
        trigger_manager::handle_action(ACTION_ENABLE_TRIGGER, 3);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 1, 'enabled' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 2, 'enabled' => 1, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_trigger', array('id' => 3, 'enabled' => 1, 'sortindex' => 3)));
    }

}
