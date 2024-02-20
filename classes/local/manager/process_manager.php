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
 * Manager for Life Cycle Processes
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

use core\event\course_deleted;
use Exception;
use tool_lifecycle\local\entity\process;
use tool_lifecycle\event\process_proceeded;
use tool_lifecycle\event\process_rollback;

/**
 * Manager for Life Cycle Processes
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_manager {

    /**
     * Creates a process for the course for a certain workflow.
     * @param int $courseid id of the course
     * @param int $workflowid id of the workflow
     * @return process|null
     * @throws \dml_exception
     */
    public static function create_process($courseid, $workflowid) {
        global $DB;
        if ($workflowid !== null) {
            $record = new \stdClass();
            $record->id = null;
            $record->courseid = $courseid;
            $record->workflowid = $workflowid;
            $record->timestepchanged = time();
            $process = process::from_record($record);
            $process->id = $DB->insert_record('tool_lifecycle_process', $process);
            return $process;
        }
        return null;
    }

    /**
     * Creates a process based on a manual trigger.
     * @param int $courseid Id of the course to be triggerd.
     * @param int $triggerid Id of the triggering trigger.
     * @return process the triggered process instance.
     * @throws \moodle_exception for invalid workflow definition or missing trigger.
     */
    public static function manually_trigger_process($courseid, $triggerid) {
        $trigger = trigger_manager::get_instance($triggerid);
        if (!$trigger) {
            throw new \moodle_exception('trigger_does_not_exist', 'tool_lifecycle');
        }
        $workflow = workflow_manager::get_workflow($trigger->workflowid);
        if (!$workflow || !workflow_manager::is_active($workflow->id) || !workflow_manager::is_valid($workflow->id) ||
                $workflow->manual !== true) {
            throw new \moodle_exception('cannot_trigger_workflow_manually', 'tool_lifecycle');
        }
        return self::create_process($courseid, $workflow->id);
    }

    /**
     * Returns all current active processes.
     * @return process[]
     * @throws \dml_exception
     */
    public static function get_processes() {
        global $DB;
        $records = $DB->get_records('tool_lifecycle_process');
        $processes = [];
        foreach ($records as $record) {
            $processes[] = process::from_record($record);
        }
        return $processes;
    }

    /**
     * Creates a process for the course which is at the respective step the trigger is followed by.
     * @param int $processid id of the process
     * @return process
     * @throws \dml_exception
     */
    public static function get_process_by_id($processid) {
        global $DB;
        $record = $DB->get_record('tool_lifecycle_process', ['id' => $processid]);
        if ($record) {
            return process::from_record($record);
        } else {
            return null;
        }
    }

    /**
     * Counts all processes for the given workflow id.
     * @param int $workflowid id of the workflow
     * @return int number of processes.
     * @throws \dml_exception
     */
    public static function count_processes_by_workflow($workflowid) {
        global $DB;
        return $DB->count_records('tool_lifecycle_process', ['workflowid' => $workflowid]);
    }

    /**
     * Returns all processes for given workflow id
     * @param int $workflowid id of the workflow
     * @return array of proccesses initiated by specifed workflow id
     * @throws \dml_exception
     */
    public static function get_processes_by_workflow($workflowid) {
        global $DB;
        $records = $DB->get_records('tool_lifecycle_process', ['workflowid' => $workflowid]);
        $processes = [];
        foreach ($records as $record) {
            $processes[] = process::from_record($record);
        }
        return $processes;
    }

    /**
     * Proceeds the process to the next step.
     * @param process $process
     * @return true, if followed by another step; otherwise false.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function proceed_process(&$process) {
        global $DB;
        $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex + 1);
        process_proceeded::event_from_process($process)->trigger();
        if ($step) {
            $process->stepindex++;
            $process->waiting = false;
            $process->timestepchanged = time();
            $DB->update_record('tool_lifecycle_process', $process);
            return true;
        } else {
            self::remove_process($process);
            return false;
        }
    }

    /**
     * Sets the process status on waiting.
     * @param process $process
     * @throws \dml_exception
     */
    public static function set_process_waiting(&$process) {
        global $DB;
        $process->waiting = true;
        $DB->update_record('tool_lifecycle_process', $process);
    }

    /**
     * Currently only removes the current process.
     * @param process $process process the rollback should be triggered for.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function rollback_process($process) {
        process_rollback::event_from_process($process)->trigger();
        for ($i = $process->stepindex; $i >= 1; $i--) {
            $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $i);
            $lib = lib_manager::get_step_lib($step->subpluginname);
            try {
                $course = get_course($process->courseid);
            } catch (\dml_missing_record_exception $e) {
                // Course no longer exists!
                break;
            }
            $lib->rollback_course($process->id, $step->id, $course);
        }
        self::remove_process($process);
    }

    /**
     * Removes the process and all data connected to it.
     * @param process $process process to be deleted.
     * @throws \dml_exception
     */
    private static function remove_process($process) {
        global $DB;
        $DB->delete_records('tool_lifecycle_procdata', ['processid' => $process->id]);
        $DB->delete_records('tool_lifecycle_process', (array) $process);
    }

    /**
     * Return the process of a course.
     * @param int $courseid Id of the course.
     * @return process|null Process instance or null if none exists.
     * @throws \dml_exception
     */
    public static function get_process_by_course_id($courseid) {
        global $DB;
        $record = $DB->get_record('tool_lifecycle_process', ['courseid' => $courseid]);
        if ($record) {
            return process::from_record($record);
        } else {
            return null;
        }
    }

    /**
     * Callback for the course deletion observer.
     * @param course_deleted $event The course deletion event.
     * @throws \dml_exception
     */
    public static function course_deletion_observed($event) {
        $process = self::get_process_by_course_id($event->get_data()['courseid']);
        if ($process) {
            self::abort_process($process);
        }
    }

    /**
     * Aborts a running process.
     * @param process $process The process to abort.
     * @throws \dml_exception
     */
    public static function abort_process($process) {
        $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex);
        $steplib = lib_manager::get_step_lib($step->subpluginname);
        $steplib->abort_course($process);
        self::remove_process($process);
    }

    /**
     * Moves a process into the procerror table.
     *
     * @param process $process The process
     * @param Exception $e The exception
     * @return void
     */
    public static function insert_process_error(process $process, Exception $e) {
        global $DB;

        $procerror = (object) clone $process;
        $procerror->errormessage = get_class($e) . ': ' . $e->getMessage();
        $procerror->errortrace = $e->getTraceAsString();
        $procerror->errortimecreated = time();
        $m = '';
        foreach ($e->getTrace() as $v) {
            $m .= $v['file'] . ':' . $v['line'] . '::';
        }
        $procerror->errorhash = md5($m);
        $procerror->waiting = intval($procerror->waiting);

        $DB->insert_record_raw('tool_lifecycle_proc_error', $procerror, false, false, true);
        $DB->delete_records('tool_lifecycle_process', ['id' => $process->id]);
    }

    /**
     * Proceed process from procerror back into the process board.
     * @param int $processid the processid
     * @return void
     */
    public static function proceed_process_after_error(int $processid) {
        global $DB;
        $process = $DB->get_record('tool_lifecycle_proc_error', ['id' => $processid]);
        // Unset process error entries.
        unset($process->errormessage);
        unset($process->errortrace);
        unset($process->errorhash);
        unset($process->errortimecreated);

        $DB->insert_record_raw('tool_lifecycle_process', $process, false, false, true);
        $DB->delete_records('tool_lifecycle_proc_error', ['id' => $process->id]);
    }

    /**
     * Rolls back a process from procerror table
     * @param int $processid the processid
     * @return void
     */
    public static function rollback_process_after_error(int $processid) {
        global $DB;

        $process = $DB->get_record('tool_lifecycle_proc_error', ['id' => $processid]);
        // Unset process error entries.
        unset($process->errormessage);
        unset($process->errortrace);
        unset($process->errorhash);
        unset($process->errortimecreated);

        $DB->insert_record_raw('tool_lifecycle_process', $process, false, false, true);
        $DB->delete_records('tool_lifecycle_proc_error', ['id' => $process->id]);

        delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, true, $process->workflowid);
        self::rollback_process($process);
    }
}
