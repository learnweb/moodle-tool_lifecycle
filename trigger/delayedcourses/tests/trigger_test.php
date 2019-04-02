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

namespace tool_lifecycle\trigger;

use tool_lifecycle\processor;
use tool_lifecycle\response\trigger_response;
use tool_lifecycle\manager\delayed_courses_manager;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

/**
 * Trigger test for delayed courses trigger.
 *
 * @package    tool_lifecycle_trigger
 * @category   delayedcourses
 * @group tool_lifecycle_trigger
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_delayedcourses_testcase extends \advanced_testcase {

    private $triggerinstance;

    /**@var processor Instance of the lifecycle processor */
    private $processor;

    public function setUp() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->processor = new processor();
        $this->triggerinstance = \tool_lifecycle_trigger_delayedcourses_generator::create_trigger_with_workflow();
    }

    /**
     * Tests that a course is not excluded by this plugin, when there exists no dalayed entry, yet.
     */
    public function test_course_not_delayed() {

        $course = $this->getDataGenerator()->create_course();

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance], []);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id == $element->id) {
                $found = true;
            }
        }
        $recordset->close();
        $this->assertFalse($found, 'The course should not have passed through since it should not be delay');
    }

    /**
     * Tests that a course is excluded by this plugin, when there exists a dalayed entry.
     */
    public function test_course_delayed() {

        $course = $this->getDataGenerator()->create_course();

        delayed_courses_manager::set_course_delayed($course->id, 2000);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance], []);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id == $element->id) {
                $found = true;
            }
        }
        $recordset->close();
        $this->assertTrue($found, 'The course should not have passed through in order to delay it');
    }

    /**
     * Tests that a course is not excluded by this plugin, when there exists a dalayed entry, but it is expired.
     */
    public function test_course_delay_expired() {

        $course = $this->getDataGenerator()->create_course();

        delayed_courses_manager::set_course_delayed($course->id, -2000);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance], []);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id == $element->id) {
                $found = true;
            }
        }
        $recordset->close();
        $this->assertFalse($found, 'The course should not have passed through since it should not be delay');
    }
}