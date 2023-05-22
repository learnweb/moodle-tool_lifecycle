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
 * Scheduled task for notify admin upon process errors
 *
 * @package tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\task;

/**
 * Scheduled task for notify admin upon process errors
 *
 * @package tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lifecycle_error_notify_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('lifecycle_error_notify_task', 'tool_lifecycle');
    }

    /**
     * Do the job.
     */
    public function execute() {
        global $DB, $CFG;

        $lastrun = get_config('tool_lifecycle', 'adminerrornotifylastrun');
        if (!$lastrun) {
            $lastrun = 0;
        }

        $currenttime = time();

        $errorcount = $DB->count_records_select('tool_lifecycle_proc_error', 'errortimecreated > :lastrun',
                ['lastrun' => $lastrun]);

        set_config('adminerrornotifylastrun', $currenttime, 'tool_lifecycle');

        if (!$errorcount) {
            return;
        }

        $obj = new \stdClass();
        $obj->amount = $errorcount;
        $obj->url = $CFG->wwwroot . '/admin/tool/lifecycle/errors.php';

        email_to_user(get_admin(), \core_user::get_noreply_user(),
            get_string('notifyerrorsemailsubject', 'tool_lifecycle', $obj),
            get_string('notifyerrorsemailcontent', 'tool_lifecycle', $obj),
            get_string('notifyerrorsemailcontenthtml', 'tool_lifecycle', $obj)
        );
    }
}
