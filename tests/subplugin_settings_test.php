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

use \tool_cleanupcourses\subplugin_manager;

/**
 * Tests the different state changes of the subplugin_settings.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_subplugin_settings_testcase extends \advanced_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
        tool_cleanupcourses_generator::setup_test_plugins();
    }

    /**
     * Test to put up the first subplugin.
     */
    public function test_up_first() {
        global $DB;
        $manager = new subplugin_manager();
        $manager->handle_action(ACTION_UP_SUBPLUGIN, 1);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 2, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 3, 'sortindex' => 3)));
    }

    /**
     * Test to put up the second subplugin.
     */
    public function test_up_second() {
        global $DB;
        $manager = new subplugin_manager();
        $manager->handle_action(ACTION_UP_SUBPLUGIN, 2);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 1, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 2, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 3, 'sortindex' => 3)));
    }

    /**
     * Test to put up the thrid subplugin.
     */
    public function test_up_third() {
        global $DB;
        $manager = new subplugin_manager();
        $manager->handle_action(ACTION_UP_SUBPLUGIN, 3);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 2, 'sortindex' => 3)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 3, 'sortindex' => 2)));
    }

    /**
     * Test to put down the first subplugin.
     */
    public function test_down_first() {
        global $DB;
        $manager = new subplugin_manager();
        $manager->handle_action(ACTION_DOWN_SUBPLUGIN, 1);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 1, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 2, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 3, 'sortindex' => 3)));
    }

    /**
     * Test to put down the second subplugin.
     */
    public function test_down_second() {
        global $DB;
        $manager = new subplugin_manager();
        $manager->handle_action(ACTION_DOWN_SUBPLUGIN, 2);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 2, 'sortindex' => 3)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 3, 'sortindex' => 2)));
    }

    /**
     * Test to put down the third subplugin.
     */
    public function test_down_third() {
        global $DB;
        $manager = new subplugin_manager();
        $manager->handle_action(ACTION_DOWN_SUBPLUGIN, 3);
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 1, 'sortindex' => 1)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 2, 'sortindex' => 2)));
        $this->assertNotEmpty($DB->get_records('tool_cleanupcourses_plugin', array('id' => 3, 'sortindex' => 3)));
    }

}
