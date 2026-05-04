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

namespace lifecycletrigger_noenrolments;

use PHPUnit\Framework\Attributes\CoversClass;
use tool_lifecycle\processor;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

/**
 * Trigger test for noenrolments trigger.
 *
 * @package    lifecycletrigger_noenrolments
 * @copyright  2021 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \tool_lifecycle\trigger\noenrolments
 */
final class noenrolments_test extends \advanced_testcase {
    /** @var processor Instance of the lifecycle processor. */
    private processor $processor;

    #[\Override]
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->processor = new processor();
        \lifecycletrigger_noenrolments_generator::create_trigger_with_workflow();
    }

    /**
     * Tests if courses, which have no enrolments are triggered by this plugin.
     */
    public function test_noenrolments_course_without_enrolments(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course();

        ob_start();
        $this->processor->call_trigger();
        ob_end_clean();
        $processcourseids = array_map(fn($process) => $process->courseid, $DB->get_records('tool_lifecycle_process'));
        $this->assertTrue(in_array($course->id, $processcourseids));
    }

    /**
     * Tests if courses which have enrolments are not triggered by this plugin.
     */
    public function test_noenrolments_course_with_enrolments(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $enrolplugin = enrol_get_plugin('manual');
        $enrolinstance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual']);

        $user1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $enrolplugin->enrol_user($enrolinstance, $user1->id, $studentrole->id);
        ob_start();
        $this->processor->call_trigger();
        ob_end_clean();
        $processcourseids = array_map(fn($process) => $process->courseid, $DB->get_records('tool_lifecycle_process'));
        $this->assertFalse(in_array($course->id, $processcourseids));
    }

    /**
     * Tests if course, which has had enrolments, but not anymore, is triggered by this plugin.
     */
    public function test_noenrolments_course_with_no_enrolments_anymore(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $enrolplugin = enrol_get_plugin('manual');
        $enrolinstance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual']);
        $enrolinstance2 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'manual']);

        $user1 = $this->getDataGenerator()->create_user();
        $teacher1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $enrolplugin->enrol_user($enrolinstance, $user1->id, $studentrole->id);
        $enrolplugin->enrol_user($enrolinstance, $teacher1->id, $teacherrole->id);
        $enrolplugin->enrol_user($enrolinstance2, $teacher1->id, $teacherrole->id);
        $enrolplugin->unenrol_user($enrolinstance, $user1->id);
        $enrolplugin->unenrol_user($enrolinstance, $teacher1->id);

        ob_start();
        $this->processor->call_trigger();
        ob_end_clean();
        $processcourseids = array_map(fn($process) => $process->courseid, $DB->get_records('tool_lifecycle_process'));
        $this->assertTrue(in_array($course->id, $processcourseids));
        $this->assertFalse(in_array($course2->id, $processcourseids));
    }
}
