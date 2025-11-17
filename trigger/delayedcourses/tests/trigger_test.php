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

namespace lifecycletrigger_delayedcourses;

use PHPUnit\Framework\Attributes\CoversNothing;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\processor;
use tool_lifecycle\settings_type;
use tool_lifecycle_trigger_delayedcourses_generator as generator;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Trigger test for delayedcourses trigger.
 *
 * @package    lifecycletrigger_delayedcourses
 * @copyright  2025 Thomas Niedermaier University of Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class trigger_test extends \advanced_testcase {

    /**@var processor Instance of the lifecycle processor */
    private $processor;

    /**@var \stdClass course delayed */
    private $coursedelayed;

    /**@var \stdClass course delayed for workflow */
    private $coursedelayedworkflow;

    /**@var \stdClass course not delayed */
    private $coursenotdelayed;

    /**@var trigger_subplugin instance of trigger */
    private $triggerinstance;

    /**
     * Setup function for the trigger test.
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $this->processor = new processor();
        $this->coursedelayed = $this->getDataGenerator()->create_course(
            ['fullname' => 'coursedelayed', 'shortname' => 'coursedelayed'],
        );
        $this->coursedelayedworkflow = $this->getDataGenerator()->create_course(
            ['fullname' => 'coursedelayedworkflow', 'shortname' => 'coursedelayedworkflow']
        );
        $this->coursenotdelayed = $this->getDataGenerator()->create_course(
            ['fullname' => 'coursenotdelayed', 'shortname' => 'coursenotdelayed']
        );
    }

    /**
     * Tests if trigger for inclusion of delayed courses works as expected.
     */
    #[CoversNothing]
    public function test_include_delayedcourses(): void {

        $this->triggerinstance = generator::create_workflow_with_delayedcourses_trigger();
        settings_manager::save_setting($this->triggerinstance->id, settings_type::TRIGGER,
            $this->triggerinstance->subpluginname, 'includegenerallydelayed', 1);
        delayed_courses_manager::set_course_delayed($this->coursedelayed->id,
            3600, 1);
        delayed_courses_manager::set_course_delayed_for_workflow($this->coursedelayedworkflow->id,
            true, $this->triggerinstance->workflowid);

        $triggeredcourses = $this->processor->get_course_recordset([$this->triggerinstance]);
        $founddelayed = false;
        $founddelayedworkflow = false;
        $foundnotdelayed = false;
        foreach ($triggeredcourses as $triggeredcourse) {
            if ($triggeredcourse->id == $this->coursedelayed->id) {
                $founddelayed = true;
            } else if ($triggeredcourse->id == $this->coursedelayedworkflow->id) {
                $founddelayedworkflow = true;
            } else if ($triggeredcourse->id == $this->coursenotdelayed->id) {
                $foundnotdelayed = true;
            }
        }
        $this->assertFalse($foundnotdelayed, 'The course without delay should not have been triggered');
        $this->assertTrue($founddelayed, 'The delayed course should have been triggered');
        $this->assertTrue($founddelayedworkflow, 'The course delayed for workflow should have been triggered');
    }

}
