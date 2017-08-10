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

require_once(__DIR__ . '/../../lib.php');

use \tool_cleanupcourses\entity\step_subplugin;
use \tool_cleanupcourses\manager\step_manager;

/**
 * Tests creating storing and retrieving a step object.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_persist_step_testcase extends \advanced_testcase {

    /** step_subplugin */
    private $step;

    const INSTANCENAME = 'myinstance';
    const STEPNAME = 'stepname';


    public function setUp() {
        $this->resetAfterTest(true);
        $this->step = new step_subplugin(self::INSTANCENAME, self::STEPNAME);
    }

    /**
     * Test that after an insert the id from the database is set within the step object.
     */
    public function test_set_step_id() {
        $this->assertEmpty($this->step->id);
        step_manager::insert_or_update($this->step);
        $this->assertNotEmpty($this->step->id);
    }

    /**
     * Test that the object which is stored in the database is the same as the one being retrieved from it.
     */
    public function test_insert_and_get() {
        step_manager::insert_or_update($this->step);
        $id = $this->step->id;
        $loadedstep = step_manager::get_step_instance($id);
        $this->assertEquals($this->step, $loadedstep);
        $loadedsteps = step_manager::get_step_instances();
        $this->assertEquals(1, count($loadedsteps));
        $loadedsteps = step_manager::get_step_instances_by_subpluginname(self::STEPNAME);
        $this->assertEquals(1, count($loadedsteps));

    }

    /**
     * Test that the step can be removed correctly.
     */
    public function test_remove() {
        step_manager::insert_or_update($this->step);
        $stepid = $this->step->id;

        // Create a second step, which is followed by the one under test.
        $step2 = new step_subplugin(self::INSTANCENAME, self::STEPNAME);
        step_manager::insert_or_update($step2);
        step_manager::change_followedby($step2->id, $stepid);
        $step2 = step_manager::get_step_instance($step2->id);
        $this->assertEquals($stepid, $step2->followedby);

        // Delete the step under test and assert it was correctly removed from the DB.
        step_manager::handle_action(ACTION_STEP_INSTANCE_DELETE, $stepid);
        $loadedstep = step_manager::get_step_instance($stepid);
        $this->assertNull($loadedstep);

        // Check if the step under test was removed as followedby from the second steps record.
        $step2 = step_manager::get_step_instance($step2->id);
        $this->assertNull($step2->followedby);
    }

}
