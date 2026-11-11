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
 * Unit tests for the lifecyclestep_email.
 *
 * @package    lifecyclestep_email
 * @copyright  2024 Justus Dieckmann, University of Münster.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecyclestep_email;

use tool_lifecycle\step\email;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Unit tests for the lifecyclestep_email lib.php.
 *
 * @package    lifecyclestep_email
 * @category   test
 * @coversDefaultClass \tool_lifecycle\step\email
 * @copyright  2024 Justus Dieckmann, University of Münster.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {

    /**
     * Tests \tool_lifecycle\step\email::replace_placeholders.
     */
    public function test_replace_placeholders(): void {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user(['firstname' => 'Jane', 'lastname' => 'Doe']);
        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'Course 1', 'shortname' => 'C1']);
        $course2 = $this->getDataGenerator()->create_course(['fullname' => 'Course 2', 'shortname' => 'C2']);
        $lib = new email();
        $response = $lib->replace_placeholders(
            [
                "##firstname##\n##lastname##\n##courses##\n##shortcourses##",
                "##firstname##<br>##lastname##<br>##courses-html##<br>##shortcourses-html##",
            ],
            $user1,
            [
                (object) ['courseid' => $course1->id],
                (object) ['courseid' => $course2->id],
            ]
        );
        $this->assertCount(2, $response);
        $this->assertEquals("Jane\nDoe\nCourse 1\r\nCourse 2\nC1\r\nC2", $response[0]);
        $this->assertEquals("Jane<br>Doe<br>Course 1<br>Course 2<br>C1<br>C2", $response[1]);
    }

    /**
     * Tests the ##courses-table## placeholder of \tool_lifecycle\step\email::replace_placeholders.
     */
    public function test_replace_placeholders_courses_table(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $category = $this->getDataGenerator()->create_category(['name' => 'Cat A']);
        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'Course 1', 'shortname' => 'C1',
            'category' => $category->id, 'startdate' => make_timestamp(2026, 2, 16)]);
        $course2 = $this->getDataGenerator()->create_course(['fullname' => 'Course 2', 'shortname' => 'C2',
            'startdate' => 0]);
        $lib = new email();

        [$html] = $lib->replace_placeholders(['<p>Hi</p>##courses-table##'], $user, [
            (object) ['courseid' => $course2->id],
            (object) ['courseid' => $course1->id],
        ]);

        $this->assertStringNotContainsString('##courses-table##', $html);
        $this->assertStringStartsWith('<p>Hi</p>', $html);
        $this->assertEquals(1, substr_count($html, '<table'));
        $this->assertEquals(1, substr_count($html, '<thead>'));
        $this->assertEquals(2, substr_count($html, '<tr class="')); // Two data rows, ordered by name.
        $this->assertLessThan(strpos($html, 'Course 2'), strpos($html, 'Course 1'));
        $this->assertStringContainsString(course_get_url($course1->id)->out(), $html);
        $this->assertStringContainsString(userdate($course1->startdate, get_string('strftimedate', 'langconfig')), $html);
        $this->assertStringContainsString('Cat A', $html);
        $this->assertStringNotContainsString(get_string('tools', 'tool_lifecycle'), $html);

        // No courses: placeholder is removed.
        [$empty] = $lib->replace_placeholders(['x##courses-table##y'], $user, []);
        $this->assertEquals('xy', $empty);
    }
}
