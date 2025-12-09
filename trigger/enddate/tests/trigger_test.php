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
 * Trigger test for start date delay trigger.
 *
 * @package    lifecycletrigger_enddate
 * @group      lifecycletrigger
 * @copyright  2025 Ostfalia
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecycletrigger_enddate;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\processor;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

/**
 * Trigger test for end date trigger.
 *
 * @package    lifecycletrigger_enddate
 * @group      lifecycletrigger
 * @copyright  2025 Ostfalia
 * @copyright  2017 Tobias Reischmann WWU
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
        $this->triggerinstance = \tool_lifecycle_trigger_enddate_generator::create_trigger_with_workflow();
    }

    /**
     * Tests if courses, which are has ended 10 days before are not triggered by this plugin.
     * @covers \tool_lifecycle\processor \tool_lifecycle\trigger\startdatedelay
     */
    public function test_young_course(): void {

        $course = $this->getDataGenerator()->create_course(
                ['startdate' => time() - 200 * DAYSECS, 'enddate' => time() - 10 * DAYSECS + 10]);

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
     * Tests if courses without and enddate are not triggered by this plugin.
     * @covers \tool_lifecycle\processor \tool_lifecycle\trigger\startdatedelay
     */
    public function test_never_ending_course(): void {

        $course = $this->getDataGenerator()->create_course(
            ['startdate' => time() - 200 * DAYSECS]);

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
     * Tests if courses, which have ended before 10 days and a few seconds are triggered by this plugin.
     * @covers \tool_lifecycle\processor \tool_lifecycle\trigger\startdatedelay
     */
    public function test_old_course(): void {

        $course = $this->getDataGenerator()->create_course(['startdate' => time() - 300 * DAYSECS,
                'enddate' => time() - 10 * DAYSECS - 10]);

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
}
