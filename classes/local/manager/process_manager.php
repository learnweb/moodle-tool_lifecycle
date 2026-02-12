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
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\local\manager;

use core\event\course_deleted;
use Exception;
use stdClass;
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
            $record->timestampcreated = time();
            $process = process::from_record($record);
            $process->id = $DB->insert_record('tool_lifecycle_process', $process);
            return $process;
        }
        return null;
    }

    /**
     * Creates a process based on a manual trigger.
     * @param int $courseid Id of the course to be triggered.
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
                $workflow->manually !== true) {
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
        // Detect processes of not existing courses and move them to the proc_error table.
        $processeswithoutcourse = $DB->get_fieldset_select('tool_lifecycle_process', 'id',
            "courseid not in (SELECT id FROM {course}) ", []);
        foreach ($processeswithoutcourse as $processid) {
            $process = self::get_process_by_id($processid, true);
            $e = new \Exception(get_string('process_withnotexistingcourse', 'tool_lifecycle'));
            self::insert_process_error($process, $e);
        }
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
     * @param bool $coursedeleted If course does not exist (anymore)
     * @return process
     * @throws \dml_exception
     */
    public static function get_process_by_id($processid, $coursedeleted = false) {
        global $DB;
        $record = $DB->get_record('tool_lifecycle_process', ['id' => $processid]);
        if ($record) {
            return process::from_record($record, $coursedeleted);
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
     * Counts the processes for the given workflow created today.
     * @param int $workflowid id of the workflow
     * @return int number of processes.
     * @throws \dml_exception
     */
    public static function count_processes_by_workflow_created_today($workflowid) {
        global $DB;

        $todaystart = date('Y-m-d 00:00:00', );
        $todayend   = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $tstart = strtotime($todaystart);
        $tend   = strtotime($todayend);

        $sql = "SELECT count(1) FROM {tool_lifecycle_process}
                WHERE workflowid = :workflowid
                AND timestampcreated >= :tstart
                AND timestampcreated < :tend";

        return $DB->count_records_sql($sql, [
            'workflowid' => $workflowid,
            'tstart' => $tstart,
            'tend' => $tend,
        ]);
    }

    /**
     * Counts all process errors for the given workflow id.
     * @param int $workflowid id of the workflow
     * @return int number of process errors.
     * @throws \dml_exception
     */
    public static function count_process_errors_by_workflow($workflowid) {
        global $DB;
        return $DB->count_records('tool_lifecycle_proc_error', ['workflowid' => $workflowid]);
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
     * Returns all processes of a deleted course
     * @param int $courseid id of the course
     * @return array of proccesses
     * @throws \dml_exception
     */
    public static function get_processes_of_deleted_course($courseid) {
        global $DB;
        $records = $DB->get_records('tool_lifecycle_process', ['courseid' => $courseid]);
        $processes = [];
        foreach ($records as $record) {
            $processes[] = process::from_record($record, true);
        }
        return $processes;
    }

    /**
     * Proceeds the process to the next step.
     * @param process $process
     * @return mixed true if followed by another step; otherwise false.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function proceed_process(&$process) {
        global $DB;
        try {
            $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex + 1);
            process_proceeded::event_from_process($process)->trigger();
            if ($step) {
                $process->stepindex++;
                $process->waiting = false;
                $process->timestepchanged = time();
                $DB->update_record('tool_lifecycle_process', $process);
                return true;
            } else {
                unset($process->context);
                self::remove_process($process);
                return false;
            }
        } catch (\coding_exception $e) {
            return $e->getMessage();
        } catch (\dml_exception $e) {
            return $e->getMessage();
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
     * @return mixed true or errormessage
     */
    public static function rollback_process($process) {
        global $DB;

        try {
            process_rollback::event_from_process($process)->trigger();
            if (!$tosortindex = $DB->get_field('tool_lifecycle_step',
                'rollbacktosortindex', ['workflowid' => $process->workflowid, 'sortindex' => $process->stepindex])) {
                $tosortindex = 0;
            }
            for ($i = $process->stepindex; $i > $tosortindex; $i--) {
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
            if ($tosortindex == 0) {
                self::remove_process($process);
            }
        } catch (\coding_exception $e) {
            return $e->getMessage();
        } catch (\dml_exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * Removes the process and all data connected to it.
     * @param process $process process to be deleted.
     * @throws \dml_exception
     */
    private static function remove_process($process) {
        global $DB;
        $DB->delete_records('tool_lifecycle_procdata', ['processid' => $process->id]);
        $DB->delete_records('tool_lifecycle_process', ['id' => $process->id]);
    }

    /**
     * Return the process of a course.
     * @param int $courseid Id of the course.
     * @return process|null Process instance or null if none exists.
     * @throws \dml_exception
     */
    public static function get_process_by_course_id($courseid) {
        global $DB;
        if ($record = $DB->get_record('tool_lifecycle_process', ['courseid' => $courseid])) {
            return process::from_record($record);
        } else {
            return null;
        }
    }

    /**
     * Defines if a course is already part of a(nother workflow's) process.
     * @param int $courseid
     * @param int $workflowid
     * @return int 0 no other process, 1 if process of same workflow, 2 if process of other workflow
     * @throws \dml_exception
     */
    public static function has_other_process($courseid, $workflowid) {
        global $DB;

        if (!$ret = $DB->record_exists_select('tool_lifecycle_process',
            "courseid = $courseid and workflowid <> $workflowid")) {
            $ret = $DB->record_exists_select('tool_lifecycle_proc_error',
                "courseid = $courseid and workflowid <> $workflowid");
        }
        return $ret;
    }

    /**
     * Callback for the course deletion observer.
     * @param course_deleted $event The course deletion event.
     * @throws \dml_exception
     */
    public static function course_deletion_observed($event) {
        if (is_numeric($courseid = $event->get_data()['courseid'])) {
            $processes = self::get_processes_of_deleted_course($courseid);
            foreach ($processes as $process) {
                self::abort_process($process);
            }
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
     * Moves the process into the procerror table.
     *
     * @param process $process The process
     * @param Exception $e The exception
     * @return void
     * @throws \dml_exception
     */
    public static function insert_process_error(process $process, Exception $e) {
        global $DB;

        $procerror = new stdClass();
        $procerror->courseid = $process->courseid;
        $procerror->workflowid = $process->workflowid;
        $procerror->stepindex = $process->stepindex;
        $procerror->timestepchanged = $process->timestepchanged;
        $procerror->errormessage = get_class($e) . ': ' . $e->getMessage();
        $procerror->errortrace = $e->getTraceAsString();
        $procerror->errortimecreated = time();
        $m = '';
        foreach ($e->getTrace() as $v) {
            $m .= $v['file'] . ':' . $v['line'] . '::';
        }
        $procerror->errorhash = md5($m);
        $procerror->waiting = intval($process->waiting);

        $DB->insert_record('tool_lifecycle_proc_error', $procerror, false, false);
        $DB->delete_records('tool_lifecycle_process', ['id' => $process->id]);
    }

    /**
     * Return process from process-error-table back into the process board.
     * @param int $processid the processid
     * @return mixed a db record object with process fields only or false
     * @throws \dml_exception
     */
    public static function proceed_process_after_error(int $processid) {
        return self::return_to_processtable_after_error($processid);
    }

    /**
     * Roll back a process from process-error-table to process-table while setting the rollback delay
     * @param int $processerrorid the processid
     * @return mixed true or errormessage
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function rollback_process_after_error(int $processerrorid) {
        $process = self::return_to_processtable_after_error($processerrorid);
        delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, true, $process->workflowid);
        return self::rollback_process($process);
    }

    /**
     * Return to process from process-error-table.
     * @param int $processerrorid the processid of the process error table
     * @return mixed a db record object with process fields only or false
     * @throws \dml_exception
     */
    public static function return_to_processtable_after_error(int $processerrorid) {
        global $DB;

        if ($process = $DB->get_record('tool_lifecycle_proc_error', ['id' => $processerrorid])) {
            // Unset process-error-only field values and id.
            unset($process->id);
            unset($process->errormessage);
            unset($process->errortrace);
            unset($process->errorhash);
            unset($process->errortimecreated);
            // In case something went wrong before during deleting an error so that the process is already active again.
            if (!$processid = $DB->get_field('tool_lifecycle_process', 'id',
                ['courseid' => $process->courseid, 'workflowid' => $process->workflowid])) {
                $process->id = $DB->insert_record('tool_lifecycle_process', $process, true);
            } else {
                $process->id = $processid;
            }
            $DB->delete_records('tool_lifecycle_proc_error', ['id' => $processerrorid]);
            $process = self::get_process_by_id($process->id);
        }
        return $process;
    }
}
