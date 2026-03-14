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
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Trigger test
 *
 * @package    lifecycletrigger_customfieldsemester
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 *             based on code 2020 by Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecycletrigger_customfieldsemester;

use DateTime;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\processor;
use customfield_semester\data_controller;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

// Constants to be used throughout the tests.
define('TEST_LIFECYCLETRIGGER_CUSTOMFIELDSEMESTER_DELAY', 12);
define('TEST_LIFECYCLETRIGGER_CUSTOMFIELDSEMESTER_SUMMERTERMSTART', 4);
define('TEST_LIFECYCLETRIGGER_CUSTOMFIELDSEMESTER_WINTERTERMSTART', 10);

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Trigger test
 *
 * @package    lifecycletrigger_customfieldsemester
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 *             based on code 2020 by Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class trigger_test extends \advanced_testcase {

    /** @var $triggerinstance trigger_subplugin Instance of the trigger. */
    private $triggerinstance;

    /** @var $processor processor Instance of the lifecycle processor. */
    private $processor;

    /** @var $fieldcategory int Custom field category ID. */
    private $fieldcategory;

    /**
     * Initial set up.
     */
    public function setUp(): void {
        // Standard setup.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a new lifecycle processor.
        $this->processor = new processor();

        // Create a new custom field category.
        $this->fieldcategory = self::getDataGenerator()->create_custom_field_category(['name' => 'Other fields']);

        // Set global config of custom field of type semester.
        set_config('summertermstartmonth', TEST_LIFECYCLETRIGGER_CUSTOMFIELDSEMESTER_SUMMERTERMSTART, 'customfield_semester');
        set_config('wintertermstartmonth', TEST_LIFECYCLETRIGGER_CUSTOMFIELDSEMESTER_WINTERTERMSTART, 'customfield_semester');

        // Create a new custom field of type semester.
        // The submitted configdata is the standard configuration of the custom field and not relevant for this test.
        $customfield = ['shortname' => 'lectureterm', 'name' => 'Lecture term', 'type' => 'semester',
                'configdata' => ['showmonthsintofuture' => 6, 'defaultmonthsintofuture' => 3, 'beginofsemesters' => 2007],
                'categoryid' => $this->fieldcategory->get('id'), ];
        self::getDataGenerator()->create_custom_field($customfield);

        // Create the workflow including the trigger.
        $this->triggerinstance = \tool_lifecycle_trigger_customfieldsemester_generator::create_trigger_with_workflow(
            $customfield['shortname'], TEST_LIFECYCLETRIGGER_CUSTOMFIELDSEMESTER_DELAY);

        // Call parent setup.
        parent::setUp();
    }

    /**
     * Tests if term-independent courses are not triggered by this plugin.
     *
     * This test does not test a particular class or function.
     */
    public function test_termindependent_course(): void {
        // Create a course with a customfield semester value set to the term-independent value.
        $customfieldvalue = ['shortname' => 'lectureterm', 'value' => 1];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        // Get the courses recordset which are triggered by this trigger.
        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);

        // Check if the course which we just have created is among the course recordset.
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }

        // Assert that the course was not found / triggered.
        $this->assertFalse($found, 'The course should not have been triggered');
    }

    /**
     * Tests if courses of the current term are not triggered by this plugin.
     *
     * This test does not test a particular class or function.
     */
    public function test_current_course(): void {
        // Create a course with a customfield semester value of the current term.
        $semesterid = data_controller::get_semester_for_datetime(new DateTime('now'));
        $customfieldvalue = ['shortname' => 'lectureterm', 'value' => $semesterid];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        // Get the courses recordset which are triggered by this trigger.
        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);

        // Check if the course which we just have created is among the course recordset.
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }

        // Assert that the course was not found / triggered.
        $this->assertFalse($found, 'The course should not have been triggered');
    }

    /**
     * Tests if courses of the next term are not triggered by this plugin.
     *
     * This test does not test a particular class or function.
     */
    public function test_new_course(): void {
        // Create a course with a customfield semester value of the next term.
        $semesterid = data_controller::get_semester_for_datetime(new DateTime('+6 months'));
        $customfieldvalue = ['shortname' => 'lectureterm', 'value' => $semesterid];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        // Get the courses recordset which are triggered by this trigger.
        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);

        // Check if the course which we just have created is among the course recordset.
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }

        // Assert that the course was not found / triggered.
        $this->assertFalse($found, 'The course should not have been triggered');
    }

    /**
     * Tests if courses of the last term are not triggered by this plugin.
     *
     * This test does not test a particular class or function.
     */
    public function test_old_course(): void {
        // Create a course with a customfield semester value of the last term.
        $semesterid = data_controller::get_semester_for_datetime(new DateTime('-6 months'));
        $customfieldvalue = ['shortname' => 'lectureterm', 'value' => $semesterid];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        // Get the courses recordset which are triggered by this trigger.
        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);

        // Check if the course which we just have created is among the course recordset.
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }

        // Assert that the course was not found / triggered.
        $this->assertFalse($found, 'The course should not have been triggered');
    }

    /**
     * Tests if courses of the term before the last term are triggered by this plugin.
     *
     * This test does not test a particular class or function.
     */
    public function test_outdated_course(): void {
        // Create a course with a customfield semester value of the term before the last term.
        $semesterid = data_controller::get_semester_for_datetime(new DateTime('-12 months'));
        $customfieldvalue = ['shortname' => 'lectureterm', 'value' => $semesterid];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        // Get the courses recordset which are triggered by this trigger.
        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);

        // Check if the course which we just have created is among the course recordset.
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }

        // Assert that the course was found / triggered.
        $this->assertTrue($found, 'The course should have been triggered');
    }

    /**
     * Tests if courses two terms before the last term are triggered by this plugin.
     *
     * This test does not test a particular class or function.
     */
    public function test_reallyoutdated_course(): void {
        // Create a course with a customfield semester value two terms before the last term.
        $semesterid = data_controller::get_semester_for_datetime(new DateTime('-18 months'));
        $customfieldvalue = ['shortname' => 'lectureterm', 'value' => $semesterid];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        // Get the courses recordset which are triggered by this trigger.
        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);

        // Check if the course which we just have created is among the course recordset.
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }

        // Assert that the course was found / triggered.
        $this->assertTrue($found, 'The course should have been triggered');
    }

    /**
     * Tests if courses, which have a customfield semester in the future, are not triggered by this plugin.
     * In addition, a second custom course field, which has a value that could trigger the course, is added to the course
     * but must be ignored by the trigger.
     *
     * This test does not test a particular class or function.
     */
    public function test_young_course_with_second_customcourse_field(): void {
        // Add an additional custom course field of type semester.
        $customfield = ['shortname' => 'lectureterm2', 'name' => 'Lecture term 2', 'type' => 'semester',
                'categoryid' => $this->fieldcategory->get('id'), ];
        self::getDataGenerator()->create_custom_field($customfield);

        // Create a course with a (significant) customfield semester value in the future and
        // a (insignificant) customfield semester value in the past.
        $semesterid = data_controller::get_semester_for_datetime(new DateTime('+6 months'));
        $customfieldvalue = ['shortname' => 'lectureterm', 'value' => $semesterid];
        $semesterid2 = data_controller::get_semester_for_datetime(new DateTime('-36 months'));
        $customfieldvalue2 = ['shortname' => 'lectureterm2', 'value' => $semesterid2];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue, $customfieldvalue2]]);

        // Get the courses recordset which are triggered by this trigger.
        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);

        // Check if the course which we just have created is among the course recordset.
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }

        // Assert that the course was not found / triggered.
        $this->assertFalse($found, 'The course should not have been triggered');
    }
}
