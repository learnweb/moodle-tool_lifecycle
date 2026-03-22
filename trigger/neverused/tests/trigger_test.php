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
 * @package    lifecycletrigger_neverused
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecycletrigger_neverused;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\processor;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Trigger test for neverused trigger.
 *
 * @package    lifecycletrigger_neverused
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class trigger_test extends \advanced_testcase {

    /** @var $triggerinstance trigger_subplugin Instance of the trigger. */
    private $triggerinstance;

    /** @var $processor processor Instance of the lifecycle processor. */
    private $processor;

    /**
     * Setup for the Tests.
     * @return void
     * @throws \moodle_exception
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->processor = new processor();

        $this->triggerinstance = \tool_lifecycle_trigger_neverused_generator::create_trigger_with_workflow();
    }


    /**
     * Tests if unused courses, which are older than 365 days are triggered by this plugin.
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_unused_course_old(): void {
        $startdate = time() - 24 * 60 * 60 * 730; // Minus two years.
        $course = $this->getDataGenerator()->create_course(['startdate' => $startdate]);

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
     * Tests if unused courses, which are older than 365 days are triggered by this plugin.
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_unused_course_younger(): void {
        $startdate = time() - 24 * 60 * 60 * 30; // Minus 30 days.
        $course = $this->getDataGenerator()->create_course(['startdate' => $startdate]);

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
