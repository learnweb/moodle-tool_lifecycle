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

namespace tool_cleanupcourses\trigger;

use tool_cleanupcourses\response\trigger_response;
use tool_cleanupcourses\manager\delayed_courses_manager;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Trigger test for delayed courses trigger.
 *
 * @package    tool_cleanupcourses_trigger
 * @category   delayedcourses
 * @group tool_cleanupcourses_trigger
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_trigger_delayedcourses_testcase extends \advanced_testcase {

    /**
     * Tests that a course is not excluded by this plugin, when there exists no dalayed entry, yet.
     */
    public function test_course_not_delayed() {

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $trigger = new delayedcourses();
        $response = $trigger->check_course($course);
        $this->assertEquals($response, trigger_response::next());
    }

    /**
     * Tests that a course is excluded by this plugin, when there exists a dalayed entry.
     */
    public function test_course_delayed() {

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        delayed_courses_manager::set_course_delayed($course->id, 2000);

        $trigger = new delayedcourses();
        $response = $trigger->check_course($course);
        $this->assertEquals($response, trigger_response::exclude());
    }

    /**
     * Tests that a course is not excluded by this plugin, when there exists a dalayed entry, but it is expired.
     */
    public function test_course_delay_expired() {

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        delayed_courses_manager::set_course_delayed($course->id, -2000);

        $trigger = new delayedcourses();
        $response = $trigger->check_course($course);
        $this->assertEquals($response, trigger_response::next());
    }
}