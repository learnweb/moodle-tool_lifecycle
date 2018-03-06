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

use \tool_cleanupcourses\manager\process_manager;
use \tool_cleanupcourses\manager\process_data_manager;
use \tool_cleanupcourses\manager\step_manager;

/**
 * Tests creating storing and retrieving process data.
 * @package    tool_cleanupcourses
 * @category   test
 * @group      tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_persist_process_data_testcase extends \advanced_testcase {

    /** process*/
    private $process;

    const KEY = 'key123';
    const VALUE = 'value123';

    public function setUp() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_cleanupcourses');

        $workflow = $generator->create_workflow_with_steps();
        $course = $this->getDataGenerator()->create_course();
        $this->process = process_manager::create_process($course->id, $workflow->id);
        // Move process to the first step.
        process_manager::proceed_process($this->process);
    }

    /**
     * Test the getting and setting of process data.
     */
    public function test_get_set_process_data() {
        $step = step_manager::get_step_instance_by_workflow_index($this->process->workflowid, $this->process->stepindex);
        process_data_manager::set_process_data(
            $this->process->id,
            $step->id,
            self::KEY,
            self::VALUE
        );
        $value = process_data_manager::get_process_data(
            $this->process->id,
            $step->id,
            self::KEY
        );
        $this->assertEquals(self::VALUE, $value);
    }

}
