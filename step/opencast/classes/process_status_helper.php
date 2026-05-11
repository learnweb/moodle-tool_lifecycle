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
 * Helper class for handling database interactions related to process status.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_opencast;

/**
 * Helper class for handling database interactions related to process status.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_status_helper {
    /** @var string Table name for process status records. */
    const TABLE_NAME = 'lifecyclestep_opencast_process_status';

    // DECISIONS.
    /** @var string Decision value for aborting the process. */
    const DECISION_ABORT = 'abort';
    /** @var string Decision value for confirming the process. */
    const DECISION_CONFIRM = 'confirm';
    /** @var string Decision value for pending decision. */
    const DECISION_PENDING = 'pending';

    /** @var array<string> List of valid decision values. */
    const DECISION_VALUES = [
        self::DECISION_CONFIRM,
        self::DECISION_PENDING,
        self::DECISION_ABORT,
    ];

    /** @var string Status value for processing. */
    const STATUS_PROCESSING = 'processing';
    /** @var string Status value for completed. */
    const STATUS_COMPLETED = 'completed';
    /** @var string Status value for waiting. */
    const STATUS_WAITING = 'waiting';
    /** @var string Status value for aborted. */
    const STATUS_ABORTED = 'aborted';

    /** @var array<string> List of valid status values. */
    const STATUS_VALUES = [
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_WAITING,
        self::STATUS_ABORTED,
    ];

    /**
     * Save or update a process status record.
     *
     * @param int $courseid The course ID.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @param string $status The status value.
     * @param string $decision The decision value.
     * @param int|null $userid The user ID, defaults to current user.
     * @return int|bool The record ID on insert, or true on update.
     */
    public static function save_or_update(
        int $courseid,
        int $processid,
        int $stepid,
        string $status,
        string $decision,
        ?int $userid = null
    ) {
        global $DB, $USER;
        if (empty($userid) && !empty($USER)) {
            $userid = $USER->id;
        }

        $currentrecord = self::read($courseid, $processid, $stepid);

        if (empty($currentrecord)) {
            $record = (object) [
                'courseid' => $courseid,
                'processid' => $processid,
                'stepid' => $stepid,
                'status' => $status,
                'decision' => $decision,
                'userid' => $userid,
            ];
            return $DB->insert_record(self::TABLE_NAME, $record);
        }

        $currentrecord->userid = $userid;
        $currentrecord->status = $status;
        $currentrecord->decision = $decision;

        return $DB->update_record(self::TABLE_NAME, $currentrecord);
    }

    /**
     * Read a process status record.
     *
     * @param int $courseid The course ID.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @param string $fields Comma-separated list of fields to retrieve, defaults to all.
     * @return object|null The record object or null if not found.
     */
    public static function read(int $courseid, int $processid, int $stepid, string $fields = '') {
        global $DB;
        $fieldstoread = !empty($fields) ? $fields : '*';
        $conditions = [
            'courseid' => $courseid,
            'processid' => $processid,
            'stepid' => $stepid,
        ];
        $record = $DB->get_record(
            self::TABLE_NAME,
            $conditions,
            $fieldstoread
        );
        return $record;
    }

    /**
     * Read a process status record by course and step.
     *
     * @param int $courseid The course ID.
     * @param int $stepid The step ID.
     * @param string $fields Comma-separated list of fields to retrieve, defaults to all.
     * @return object|null The record object or null if not found.
     */
    public static function read_by_course(int $courseid, int $stepid, string $fields = '') {
        global $DB;
        $fieldstoread = !empty($fields) ? $fields : '*';
        $conditions = [
            'courseid' => $courseid,
            'stepid' => $stepid,
        ];
        $record = $DB->get_record(
            self::TABLE_NAME,
            $conditions,
            $fieldstoread
        );
        return $record;
    }

    /**
     * Check if there are pending entries for a step.
     *
     * @param int $stepid The step ID.
     * @return bool True if there are pending entries, false otherwise.
     */
    public static function has_pending_entries(int $stepid) {
        global $DB;
        $pendings = [
            self::STATUS_WAITING,
            self::STATUS_PROCESSING,
        ];
        [$insql, $inparams] = $DB->get_in_or_equal($pendings);
        $sql = "SELECT count(1) FROM {" . self::TABLE_NAME . "}
                WHERE stepid = ? AND status {$insql}";

        $haspendingentries = $DB->count_records_sql($sql, [
            $stepid,
            ...$inparams,
        ]);

        return $haspendingentries > 0;
    }

    /**
     * Remove a process status entry.
     *
     * @param int $courseid The course ID.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @return void
     */
    public static function remove_entry(int $courseid, int $processid, int $stepid) {
        global $DB;
        $DB->delete_records(
            self::TABLE_NAME,
            [
                'stepid' => $stepid,
                'courseid' => $courseid,
                'processid' => $processid,
            ]
        );
    }

    /**
     * Map a decision to its corresponding status.
     *
     * @param string $decision The decision value.
     * @return string The corresponding status value.
     */
    public static function map_status_by_decision($decision) {
        $status = match ($decision) {
            self::DECISION_ABORT => self::STATUS_ABORTED,
            self::DECISION_CONFIRM => self::STATUS_PROCESSING,
            self::DECISION_PENDING => self::STATUS_WAITING,
            default => self::STATUS_WAITING
        };

        return $status;
    }

    /**
     * Cron job to clean up old process status records.
     *
     * Deletes records for steps that have no pending entries.
     *
     * @return void
     */
    public static function cleanup_cron() {
        global $DB;

        mtrace("Getting all records ordered by stepid...");
        $stepids = $DB->get_fieldset_sql('SELECT DISTINCT stepid FROM {' . self::TABLE_NAME . '} ORDER BY stepid ASC');

        mtrace("Available number of records: " . count($stepids));

        foreach ($stepids as $stepid) {
            mtrace("Processing records with stepid: " . $stepid);
            $haspendingentries = self::has_pending_entries((int) $stepid);
            if (!$haspendingentries) {
                $success = $DB->delete_records(
                    self::TABLE_NAME,
                    ['stepid' => $stepid]
                );

                $msg = $success ? 'Records deleted!' : 'Unable to delete records.';
                mtrace($msg);
            } else {
                mtrace('There are pending processing for this step, skipping...');
            }
        }
    }
}
