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
 * Step subplugin to delete a course context using Catalyst batch deletion task.
 *
 * @package lifecyclestep_uclcontextdelete
 */

namespace tool_lifecycle\step;

use stdClass;
use tool_lifecycle\local\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class uclcontextdelete extends libbase {

    /**
     * Add course to Catalyst batch deletion queue.
     */
    public function process_course($processid, $instanceid, $course) {
        global $DB;

        // Safety: never delete front page course.
        if ((int)$course->id === 1) {
            return step_response::rollback();
        }

        // Queue course into Catalyst batch deletion table.
        $record = new \stdClass();
        $record->courseid   = $course->id;
        $record->timeadded  = time();
        $record->status     = 0; // 0 = pending (Catalyst convention)

        // Avoid duplicates.
        if (!$DB->record_exists('tool_catmaintenance_delcourse', ['courseid' => $course->id])) {
            $DB->insert_record('tool_catmaintenance_delcourse', $record);
        }

        return step_response::proceed();
    }

    public function get_subpluginname() {
        return 'uclcontextdelete';
    }
}
