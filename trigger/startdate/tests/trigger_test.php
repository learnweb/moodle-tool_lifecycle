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
 * Trigger test for start date trigger.
 *
 * @package    lifecycletrigger_startdate
 * @group      lifecycletrigger
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecycletrigger_startdate;

use PHPUnit\Framework\Attributes\CoversClass;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\processor;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Trigger test for start date trigger.
 *
 * @package    lifecycletrigger_startdate
 * @group      lifecycletrigger
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class trigger_test extends \advanced_testcase {

    /** @var $triggerinstance trigger_subplugin Instance of the trigger. */
    private $triggerinstance;

    /** @var $processor processor Instance of the lifecycle processor. */
    private $processor;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->processor = new processor();
        $this->triggerinstance = \tool_lifecycle_trigger_startdate_generator::create_trigger_with_workflow();
    }

    /**
     * Tests if a course, which has a start date within the instance settings, is triggered by this plugin.
     */
    public function test_course_within(): void {

        $course = $this->getDataGenerator()->create_course(['startdate' => time() - 7 * DAYSECS]);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The course should have been triggered');
    }

    /**
     * Tests if a course, which has a start date after the time window, is not triggered by this plugin.
     */
    public function test_younger_course(): void {

        $course = $this->getDataGenerator()->create_course(['startdate' => time() - 4 * DAYSECS]);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'The course should not have been triggered');
    }

    /**
     * Tests if a course without a start date is not triggered by this plugin.
     */
    public function test_course_without_startdate(): void {

        $course = $this->getDataGenerator()->create_course(['startdate' => 0]);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'The course should not have been triggered');
    }

    /**
     * Tests if a course, which has a start date before the time window, is triggered by this plugin.
     */
    public function test_older_course(): void {

        $course = $this->getDataGenerator()->create_course(['startdate' => time() - 11 * DAYSECS]);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'The course should not have been triggered');
    }
}
