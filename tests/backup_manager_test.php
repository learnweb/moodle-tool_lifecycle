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
 * Tests the beckup manager.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

use tool_lifecycle\local\manager\backup_manager;

/**
 * Tests the beckup manager.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class backup_manager_test extends \advanced_testcase {

    /** @var array $course Instance of the course under test. */
    private $course;

    /**
     * Setup the testcase.
     */
    public function setUp(): void {
        $this->resetAfterTest(false);
        $this->course = $this->getDataGenerator()->create_course();
    }

    /**
     * Test creating a backup for a course.
     * @covers \tool_lifecycle\local\manager\backup_manager create backup
     */
    public function test_backup_create(): void {
        global $DB;
        $result = backup_manager::create_course_backup($this->course->id);
        $this->assertTrue($result);
        $backups = $DB->get_records('tool_lifecycle_backups');
        $this->assertEquals(1, count($backups));
    }

    /**
     * Test redirect without errors when starting to restore a backup.
     * @covers \tool_lifecycle\local\manager\backup_manager restore backup
     */
    public function test_backup_restore(): void {
        global $DB;
        $backups = $DB->get_records('tool_lifecycle_backups');
        $this->assertEquals(1, count($backups));
        $backupid = array_pop($backups)->id;
        try {
            backup_manager::restore_course_backup($backupid);
        } catch (\moodle_exception $e) {
            $this->assertEquals('redirecterrordetected', $e->errorcode);
        }
    }


}
