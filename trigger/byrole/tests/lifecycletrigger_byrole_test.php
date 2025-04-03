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
 * The class contains a test script for the trigger subplugin byrole
 *
 * @package lifecycletrigger_byrole
 * @copyright  2017 Tobias Reischmann WWU Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecycletrigger_byrole;

use tool_lifecycle\processor;
use tool_lifecycle\local\response\trigger_response;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class lifecycletrigger_byrole_testcase
 * @category   test
 * @package    lifecycletrigger_byrole
 * @group      lifecycletrigger_byrole
 * @copyright  2017 Tobias Reischmann WWU Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lifecycletrigger_byrole_test extends \advanced_testcase {

    /** @var \tool_lifecycle\processor $processor Instance of the lifecycle processor */
    private $processor;

    /**
     * Set up environment for phpunit test.
     * @return mixed data for test
     */
    protected function setUp(): void {
        // Recommended in Moodle docs to always include CFG.
        global $CFG;
        $this->resetAfterTest(true);

        $this->processor = new processor();
    }

    /**
     * Test the locallib function for valid courses.
     * @covers \tool_lifecycle\trigger\byrole
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_lib_validcourse(): void {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('lifecycletrigger_byrole');
        $data = $generator->test_create_preparation();
        $recordset = $this->processor->get_course_recordset([$data['trigger']], []);

        foreach ($recordset as $element) {
            $this->assertNotEquals($data['teachercourse']->id, $element->id, 'The course should not have been triggered');
        }
        $exist = $DB->record_exists('lifecycletrigger_byrole', ['courseid' => $data['teachercourse']->id]);
        $this->assertEquals(false, $exist);
    }

    /**
     * Test the locallib function for a invalid course that is recognized for the first time.
     * @covers \tool_lifecycle\trigger\byrole
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_lib_norolecourse(): void {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('lifecycletrigger_byrole');
        $data = $generator->test_create_preparation();
        $recordset = $this->processor->get_course_recordset([$data['trigger']], []);

        foreach ($recordset as $element) {
            $this->assertNotEquals($data['norolecourse']->id, $element->id, 'The course should not have been triggered');
        }
        $exist = $DB->record_exists('lifecycletrigger_byrole', ['courseid' => $data['norolecourse']->id]);
        $this->assertEquals(true, $exist);
    }


    /**
     * Test the locallib function for a invalid course that is old enough to be triggered.
     * @covers \tool_lifecycle\trigger\byrole
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_lib_norolefoundcourse(): void {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('lifecycletrigger_byrole');
        $data = $generator->test_create_preparation();
        $recordset = $this->processor->get_course_recordset([$data['trigger']], []);

        $found = false;
        foreach ($recordset as $element) {
            if ($data['norolefoundcourse']->id == $element->id) {
                $found = true;
            }
        }
        $this->assertTrue($found, 'The course should have been triggered');
        $exist = $DB->record_exists('lifecycletrigger_byrole', ['courseid' => $data['norolefoundcourse']->id]);
        $this->assertEquals(true, $exist);
    }


    /**
     * Test the locallib function for a course that was invalid and has a responsible person again.
     * @covers \tool_lifecycle\trigger\byrole
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_lib_rolefoundagain(): void {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('lifecycletrigger_byrole');
        $data = $generator->test_create_preparation();

        $exist = $DB->record_exists('lifecycletrigger_byrole', ['courseid' => $data['rolefoundagain']->id]);
        $this->assertEquals(true, $exist);

        $recordset = $this->processor->get_course_recordset([$data['trigger']], []);

        foreach ($recordset as $element) {
            $this->assertNotEquals($data['rolefoundagain']->id, $element->id, 'The course should not have been triggered');
        }

        $exist = $DB->record_exists('lifecycletrigger_byrole', ['courseid' => $data['rolefoundagain']->id]);
        $this->assertEquals(false, $exist);
    }
}
