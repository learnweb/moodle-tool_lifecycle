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

namespace lifecyclestep_deletebackup;

use tool_lifecycle\step\deletebackup;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/mock_stream_wrapper.php');
require_once(__DIR__ . '/../lib.php');

/**
 * Tests that deleting course backups does not change the file permissions of the archives.
 *
 * @package    lifecyclestep_deletebackup
 * @category   test
 * @copyright  2026 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class delete_backups_test extends \advanced_testcase {
    /** @var int Id of the backup record under test. */
    private $recordid;

    #[\Override]
    protected function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest();
        mock_stream_wrapper::$files = ['backup.mbz'];
        mock_stream_wrapper::$chmodcalls = [];
        stream_wrapper_register('lifecyclemock', mock_stream_wrapper::class);
        set_config('backup_path', 'lifecyclemock://backups', 'tool_lifecycle');
        $this->recordid = $DB->insert_record('tool_lifecycle_backups', (object) [
            'courseid' => 2,
            'fullname' => 'Course',
            'shortname' => 'course',
            'backupfile' => 'backup.mbz',
            'backupcreated' => time() - 2 * DAYSECS,
            'step' => 1,
        ]);
    }

    #[\Override]
    protected function tearDown(): void {
        stream_wrapper_unregister('lifecyclemock');
        parent::tearDown();
    }

    /**
     * The archive must not be made world-writable before it is deleted.
     *
     * @covers \tool_lifecycle\step\deletebackup::delete_backups
     */
    public function test_backup_permissions_are_not_changed(): void {
        $step = new deletebackup();
        $step->delete_backups(2, 1);
        $this->assertSame([], mock_stream_wrapper::$chmodcalls);
    }

    /**
     * An expired backup file and its record are still deleted.
     *
     * @covers \tool_lifecycle\step\deletebackup::delete_backups
     */
    public function test_expired_backup_is_deleted(): void {
        global $DB;
        $step = new deletebackup();
        $results = $step->delete_backups(2, 1);
        $this->assertCount(1, $results);
        $this->assertSame([], mock_stream_wrapper::$files);
        $this->assertFalse($DB->record_exists('tool_lifecycle_backups', ['id' => $this->recordid]));
    }
}
