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

namespace tool_lifecycle\trigger;

use tool_lifecycle\local\response\trigger_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Basic lib for the noenrolments lifecycletrigger.
 *
 * @package    lifecycletrigger_noenrolments
 * @copyright  2021 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class noenrolments extends base_automatic {
    #[\Override]
    public function check_course($course, $triggerid): trigger_response {
        // Every decision is already in the where statement.
        return trigger_response::trigger();
    }

    /**
     * Add SQL for to trigger a course.
     * @param int $notused
     * @return array A list containing the constructed SQL fragment and an array of parameters.
     */
    #[\Override]
    public function get_course_recordset_where($notused): array {
        $where = "  NOT EXISTS (
                        SELECT 1
                        FROM {user_enrolments} ue
                        JOIN {enrol} e ON e.id = ue.enrolid
                        WHERE e.courseid = c.id
                    )
        ";
        return [$where, []];
    }

    #[\Override]
    public function get_subpluginname(): string {
        return 'noenrolments';
    }

    #[\Override]
    public function get_icon(): string {
        return 'i/enrolmentsuspended';
    }
}
