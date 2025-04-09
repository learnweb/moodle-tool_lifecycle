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
 * Manager for Delayed Courses.
 *
 * Each entry tells that the trigger-check for a certain course is delayed until a certain timestamp.
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\local\manager;

use tool_lifecycle\local\entity\workflow;

define('DELAYTYPE_ROLLBACK', 1);
define('DELAYTYPE_FINISHED', 2);

/**
 * Manager for Delayed Courses.
 *
 * Each entry tells that the trigger-check for a certain course is delayed until a certain timestamp.
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delayed_courses_manager {

    /**
     * Sets a delay for a course for specific workflow.
     * @param int $courseid Id of the course.
     * @param bool $becauserollback True, if the delay is caused by a rollback.
     * @param int|workflow $workfloworid Id of the workflow.
     * @throws \dml_exception
     */
    public static function set_course_delayed_for_workflow($courseid, $becauserollback, $workfloworid) {
        global $DB;
        if (is_object($workfloworid)) {
            $workflow = $workfloworid;
        } else {
            $workflow = workflow_manager::get_workflow($workfloworid);
        }
        if ($becauserollback) {
            $duration = $workflow->rollbackdelay;
            $type = DELAYTYPE_ROLLBACK;
        } else {
            $duration = $workflow->finishdelay;
            $type = DELAYTYPE_FINISHED;
        }
        if ($workflow->delayforallworkflows) {
            self::set_course_delayed($courseid, $duration, $type);
        } else {
            $delayeduntil = time() + $duration;
            $record = $DB->get_record('tool_lifecycle_delayed_workf',
                        ['courseid' => $courseid, 'workflowid' => $workflow->id]);
            if (!$record) {
                $record = new \stdClass();
                $record->courseid = $courseid;
                $record->workflowid = $workflow->id;
                $record->delayeduntil = $delayeduntil;
                $record->type = $type;
                $DB->insert_record('tool_lifecycle_delayed_workf', $record);
            } else {
                if ($record->delayeduntil < $delayeduntil) {
                    $record->delayeduntil = $delayeduntil;
                    $record->type = $type;
                    $DB->update_record('tool_lifecycle_delayed_workf', $record);
                }
            }
        }

    }

    /**
     * Get the delayed courses for specific workflow.
     * @param int $workflowid Id of the workflow.
     * @return array
     * @throws \dml_exception
     */
    public static function get_delayed_courses_for_workflow($workflowid) {
        global $DB;
        $sql = 'SELECT courseid FROM {tool_lifecycle_delayed_workf} WHERE delayeduntil > :now AND workflowid = :workflowid';
        return $DB->get_fieldset_sql($sql, ['now' => time(), 'workflowid' => $workflowid]);
    }

    /**
     * Creates an instance of a delayed course.
     * @param int $courseid id of the course
     * @param int $duration number of seconds
     * @throws \dml_exception
     */
    public static function set_course_delayed($courseid, $duration, $type = 0) {
        global $DB;
        $delayeduntil = time() + $duration;
        $record = $DB->get_record('tool_lifecycle_delayed', ['courseid' => $courseid]);
        if (!$record) {
            $record = new \stdClass();
            $record->courseid = $courseid;
            $record->delayeduntil = $delayeduntil;
            $record->type = $type;
            $DB->insert_record('tool_lifecycle_delayed', $record);
        } else {
            if ($record->delayeduntil < $delayeduntil) {
                $record->delayeduntil = $delayeduntil;
                $record->type = $type;
                $DB->update_record('tool_lifecycle_delayed', $record);
            }
        }
    }

    /**
     * Queries if a course was delayed.
     * @param int $courseid id of the course
     * @return null|int timestamp until when the course is delayed (null if no entry exists).
     * @throws \dml_exception
     */
    public static function get_course_delayed($courseid) {
        global $DB;
        $record = $DB->get_record('tool_lifecycle_delayed', ['courseid' => $courseid]);
        if ($record) {
            return $record->delayeduntil;
        } else {
            return null;
        }
    }

    /**
     * Queries if a course was delayed for a given workflow.
     * @param int $courseid id of the course
     * @param int $workflowid id of the workflow
     * @return null|int timestamp until when the course is delayed (null if no entry exists).
     * @throws \dml_exception
     */
    public static function get_course_delayed_workflow($courseid, $workflowid) {
        global $DB;
        $record = $DB->get_record('tool_lifecycle_delayed_workf', ['courseid' => $courseid, 'workflowid' => $workflowid]);
        if ($record) {
            return $record->delayeduntil;
        } else {
            return null;
        }
    }

    /**
     * Build where sql for the processor to select only delayed courses.
     * @return array
     */
    public static function get_course_delayed_wheresql() {
        $where = "{course}.id IN (SELECT courseid FROM {tool_lifecycle_delayed} WHERE delayeduntil > :now)";
        $params = ["now" => time()];
        return [$where, $params];
    }

    /**
     * Get the globally delayed courses.
     * @return array array of course ids.
     * @throws \dml_exception
     */
    public static function get_globally_delayed_courses() {
        global $DB;
        $sql = 'SELECT courseid FROM {tool_lifecycle_delayed} WHERE delayeduntil > :now';
        return $DB->get_fieldset_sql($sql, ['now' => time()]);
    }

    /**
     * Deletes the delay entry for a course.
     * @param int $courseid id of the course
     * @throws \dml_exception
     */
    public static function remove_delay_entry($courseid) {
        global $DB;
        $DB->delete_records('tool_lifecycle_delayed', ['courseid' => $courseid]);
    }

    /**
     * Returns output html whether delay is of type rollback or finished.
     * @param int $type id of the delay type
     * @throws \dml_exception
     */
    public static function delaytype_html($type) {
        global $OUTPUT;
        $typehtml = "";
        if ($type == DELAYTYPE_ROLLBACK) {
            $typehtml = $OUTPUT->render(new \pix_icon('e/undo',
                get_string('rolledback', 'tool_lifecycle'),'moodle',['class' => 'ml-1']));
        } else if ($type == DELAYTYPE_FINISHED) {
            $typehtml = $OUTPUT->render(new \pix_icon('e/tick',
                get_string('finished', 'tool_lifecycle'),'moodle',['class' => 'ml-1']));
        }
        return $typehtml;
    }
}
