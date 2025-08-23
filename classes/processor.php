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
 * Offers functionality to trigger, process and finish lifecycle processes.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameLowerCase

namespace tool_lifecycle;

use tool_lifecycle\event\process_rollback;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\event\process_triggered;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\local\response\step_interactive_response;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\local\response\trigger_response;

/**
 * Offers functionality to trigger, process and finish lifecycle processes.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class processor {

    /**
     * Processes the trigger plugins for all relevant courses.
     */
    public function call_trigger() {
        global $FULLSCRIPT;

        $run = str_contains($FULLSCRIPT, 'run.php');

        $activeworkflows = workflow_manager::get_active_automatic_workflows();
        $exclude = [];

        if (!defined('BEHAT_SITE_RUNNING')) {
            if ($run) {
                echo \html_writer::div(get_string ('active_workflows_header_title', 'tool_lifecycle').
                    ": ".count($activeworkflows));
            } else {
                mtrace(get_string ('active_workflows_header_title', 'tool_lifecycle').
                    ": ".count($activeworkflows));
            }
        }

        foreach ($activeworkflows as $workflow) {
            $countcourses = 0;
            $counttriggered = 0;
            $countexcluded = 0;

            if (!defined('BEHAT_SITE_RUNNING')) {
                if ($run) {
                    echo \html_writer::div('Calling triggers for workflow "' . $workflow->title . '" '.
                        userdate(time(), get_string('strftimedatetimeaccurate')));
                } else {
                    mtrace('Calling triggers for workflow "' . $workflow->title . '" '.
                        userdate(time(), get_string('strftimedatetimeaccurate')));
                }
            }
            $triggers = trigger_manager::get_triggers_for_workflow($workflow->id);
            if (!$workflow->includesitecourse) {
                $exclude[] = 1;
            }
            if (!$workflow->includedelayedcourses) {
                $exclude = array_merge(delayed_courses_manager::get_delayed_courses_for_workflow($workflow->id),
                    delayed_courses_manager::get_globally_delayed_courses(), $exclude);
            }
            $recordset = $this->get_course_recordset($triggers, $exclude);
            while ($recordset->valid()) {
                $course = $recordset->current();
                $countcourses++;
                foreach ($triggers as $trigger) {
                    $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
                    $response = $lib->check_course($course, $trigger->id);
                    if ($response == trigger_response::next()) {
                        $recordset->next();
                        continue 2;
                    }
                    if ($response == trigger_response::exclude()) {
                        array_push($exclude, $course->id);
                        $countexcluded++;
                        $recordset->next();
                        continue 2;
                    }
                    if ($response == trigger_response::trigger()) {
                        continue;
                    }
                }
                // If all trigger instances agree, that they want to trigger a process, we do so.
                $process = process_manager::create_process($course->id, $workflow->id);
                process_triggered::event_from_process($process)->trigger();
                $counttriggered++;
                $recordset->next();
            }
            if (!defined('BEHAT_SITE_RUNNING')) {
                if ($run) {
                    echo \html_writer::div("   $countcourses courses processed.");
                    echo \html_writer::div("   $counttriggered courses triggered.");
                    echo \html_writer::div("   $countexcluded courses excluded.");
                } else {
                    mtrace("   $countcourses courses processed.");
                    mtrace("   $counttriggered courses triggered.");
                    mtrace("   $countexcluded courses excluded.");
                }
            }
        }
    }

    /**
     * Calls the process_course() method of each step submodule currently responsible for a given course.
     */
    public function process_courses() {
        foreach (process_manager::get_processes() as $process) {
            while (true) {

                try {
                    $course = get_course($process->courseid);
                } catch (\dml_missing_record_exception $e) {
                    // Course no longer exists!
                    break;
                }

                if ($process->stepindex == 0) {
                    if (!process_manager::proceed_process($process)) {
                        // Happens for a workflow with no step.
                        delayed_courses_manager::set_course_delayed_for_workflow($course->id,
                            false, $process->workflowid);
                        break;
                    }
                }

                $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex);
                $lib = lib_manager::get_step_lib($step->subpluginname);
                try {
                    if ($process->waiting) {
                        $result = $lib->process_waiting_course($process->id, $step->id, $course);
                    } else {
                        $result = $lib->process_course($process->id, $step->id, $course);
                    }
                } catch (\Exception $e) {
                    process_manager::insert_process_error($process, $e);
                    break;
                }
                if ($result == step_response::waiting()) {
                    process_manager::set_process_waiting($process);
                    break;
                } else if ($result == step_response::proceed()) {
                    if (!process_manager::proceed_process($process)) {
                        delayed_courses_manager::set_course_delayed_for_workflow($course->id,
                            false, $process->workflowid);
                        break;
                    }
                } else if ($result == step_response::rollback()) {
                    delayed_courses_manager::set_course_delayed_for_workflow($course->id,
                        true, $process->workflowid);
                    process_manager::rollback_process($process);
                    break;
                } else {
                    throw new \moodle_exception('Return code \''. var_dump($result) . '\' is not allowed!');
                }
            }
        }

    }

    /**
     * In case we are in an interactive environment because the user is lead through the interactive interfaces
     * of multiple steps, this function cares for a redirection and processing through these steps until we reach a
     * no longer interactive state of the workflow.
     *
     * @param int $processid Id of the process
     * @return boolean if true, interaction finished.
     *      If false, the current step is still processing and cares for displaying the view.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course_interactive($processid) {
        $process = process_manager::get_process_by_id($processid);
        $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex + 1);
        // If there is no next step, then proceed, which will delete/finish the process.
        if (!$step) {
            delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, false, $process->workflowid);
            process_manager::proceed_process($process);
            return true;
        }
        if ($interactionlib = lib_manager::get_step_interactionlib($step->subpluginname)) {
            // Actually proceed to the next step.
            process_manager::proceed_process($process);
            $response = $interactionlib->handle_interaction($process, $step);
            switch ($response) {
                case step_interactive_response::still_processing():
                    return false;
                case step_interactive_response::no_action():
                    break;
                case step_interactive_response::proceed():
                    // In case of proceed, call recursively.
                    return $this->process_course_interactive($processid);
                case step_interactive_response::rollback():
                    delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, true, $process->workflowid);
                    process_manager::rollback_process($process);
                    break;
            }
            return true;
        }
        return true;
    }

    /**
     * Returns a record set with all relevant courses for a list of automatic triggers.
     * Relevant means that there is currently no lifecycle process running for this course.
     * @param trigger_subplugin[] $triggers List of triggers, which will be asked for additional where requirements.
     * @param int[] $exclude List of course id, which should be excluded from execution.
     * @param bool $forcounting
     * @return \moodle_recordset with relevant courses.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset($triggers, $exclude, $forcounting = false) {
        global $DB, $SESSION;

        $whereparams = [];
        $workflowid = false;
        foreach ($triggers as $trigger) {
            if (!$workflowid) {
                $workflowid = $trigger->workflowid;
                $workflow = workflow_manager::get_workflow($trigger->workflowid);
                $andor = ($workflow->andor ?? 0) == 0 ? 'AND' : 'OR';
                $where = $andor == 'AND' ? 'true ' : 'false ';
            }
            $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
            [$sql, $params] = $lib->get_course_recordset_where($trigger->id);
            $sql = preg_replace("/{course}/", "c", $sql, 1);
            if (!empty($sql)) {
                $where .= " $andor " . $sql;
                $whereparams = array_merge($whereparams, $params);
            }
        }

        if ($forcounting) {
            if ($exclude) {
                if (!$workflow->includesitecourse) {
                    $where = "($where) AND c.id <> 1 ";
                }
            }
            // Get course hasprocess and delay with the sql.
            $sql = "SELECT c.id,
                    COALESCE(p.courseid, pe.courseid, 0) as hasprocess,
                    CASE
                        WHEN COALESCE(p.workflowid, 0) > COALESCE(pe.workflowid, 0) THEN p.workflowid
                        WHEN COALESCE(p.workflowid, 0) < COALESCE(pe.workflowid, 0) THEN pe.workflowid
                        ELSE 0
                    END as workflowid,
                    CASE
                        WHEN COALESCE(d.delayeduntil, 0) > COALESCE(dw.delayeduntil, 0) THEN d.delayeduntil
                        WHEN COALESCE(d.delayeduntil, 0) < COALESCE(dw.delayeduntil, 0) THEN dw.delayeduntil
                        ELSE 0
                    END as delay
                    FROM {course} c
                    LEFT JOIN {tool_lifecycle_process} p ON c.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON c.id = pe.courseid
                    LEFT JOIN {tool_lifecycle_delayed} d ON c.id = d.courseid
                    LEFT JOIN {tool_lifecycle_delayed_workf} dw ON c.id = dw.courseid
                    WHERE " . $where;
        } else {
            if ($exclude) {
                if (!$workflow->includesitecourse) {
                    $where = "($where) AND c.id <> 1 ";
                }
                if (!$workflow->includedelayedcourses) {
                    $where = "($where) AND NOT c.id in (select courseid FROM {tool_lifecycle_delayed_workf}
                    WHERE delayeduntil > :time1 AND workflowid = :workflowid)
                    AND NOT c.id in (select courseid FROM {tool_lifecycle_delayed} WHERE delayeduntil > :time2) ";
                    $inparams = ['time1' => time(), 'time2' => time(), 'workflowid' => $workflowid];
                    $whereparams = array_merge($whereparams, $inparams);
                }
            }
            // Get only courses which are not part of an existing process.
            $sql = "SELECT c.id from {course} c
                    LEFT JOIN {tool_lifecycle_process} p ON c.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON c.id = pe.courseid
                    WHERE p.courseid is null AND pe.courseid IS NULL AND " . $where;
        }
        $debugsql = $sql;
        foreach ($whereparams as $key => $value) {
            $debugsql = str_replace(":".$key, $value, $debugsql);
        }
        $SESSION->debugtriggersql = $debugsql;
        return $DB->get_recordset_sql($sql, $whereparams);
    }

    /**
     * Returns the number of courses for a trigger for counting.
     * Relevant means that there is currently no lifecycle process running for this course.
     * @param trigger_subplugin $trigger trigger, which will be asked for additional where requirements.
     * @param int $excluded Are courses to exclude? 0 if not.
     * @return int[] $triggered, $new, $delayed Triggered courses, amount which courses of them would
     * be added, which courses are delayed.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_triggercourses_forcounting($trigger, $excluded) {
        global $DB;

        $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
        // Get SQL for this trigger.
        [$where, $whereparams] = $lib->get_course_recordset_where($trigger->id);
        $where = str_replace("{course}", "c", $where);

        // We just want the triggered courses here, no matter of including or excluding.
        $where = str_replace("<>", "=", str_replace(" NOT ", " ", $where));

        // Now get all the courses triggered by this trigger.
        $sql = 'SELECT c.id from {course} c WHERE '. $where;
        $triggercoursesall = $DB->get_fieldset_sql($sql, $whereparams);

        // Get amount of delayed courses which would be triggered by this trigger.
        $workflow = workflow_manager::get_workflow($trigger->workflowid);
        if ($workflow->includedelayedcourses) {
            $delayed = [];
        } else {
            $delayed = array_merge(delayed_courses_manager::get_delayed_courses_for_workflow($workflow->id),
                delayed_courses_manager::get_globally_delayed_courses());
        }
        $delayedcourses = count(array_intersect($triggercoursesall, $delayed));

        // Exclude delayed courses and sitecourse according to the workflow settings.
        if ($excluded) {
            if (!$workflow->includesitecourse) {
                $where .= " AND c.id <> 1 ";
            }
            if (!$workflow->includedelayedcourses) {
                $where .= " AND NOT c.id in (select courseid FROM {tool_lifecycle_delayed_workf}
                WHERE delayeduntil > :time1 AND workflowid = :workflowid)
                AND NOT c.id in (select courseid FROM {tool_lifecycle_delayed} WHERE delayeduntil > :time2) ";
                $inparams = ['time1' => time(), 'time2' => time(), 'workflowid' => $workflow->id];
                $whereparams = array_merge($whereparams, $inparams);
            }
        }
        $sql = 'SELECT count(c.id) from {course} c WHERE '. $where;
        $triggercourses = $DB->count_records_sql($sql, $whereparams);

        // Only get courses which are not part of this workflow yet. Exclude processes and proc_errors of this wf.
        $sql .= " AND c.id NOT IN (
                    SELECT {course}.id from {course}
                    LEFT JOIN {tool_lifecycle_process} p ON {course}.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
                    WHERE p.courseid IS NOT NULL OR pe.courseid IS NOT NULL
                )";
        $newcourses = $DB->count_records_sql($sql, $whereparams);

        return [$triggercourses, $newcourses, $delayedcourses];
    }


    /**
     * Also returns the number of courses for a trigger for counting. BUT the trigger lib function check_courses is used
     * to select a course for triggering/excluding.
     * Relevant means that there is currently no lifecycle process running for this course.
     * @param trigger_subplugin $trigger trigger which will be asked for additional where requirements.
     * @param int $excluded Are courses to exclude? 0 if not.
     * @return int[] $triggered, $new, $delayed Triggered courses, amount which courses of them would
     * be added, which courses are delayed.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_triggercourses_forcounting_check_course($trigger, $excluded) {
        global $DB;

        $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
        $excludedcourses = [];
        $delayedcourses = [];
        if ($excluded) {
            $workflow = workflow_manager::get_workflow($trigger->workflowid);
            // Exclude delayed courses and sitecourse according to the workflow settings.
            $sitecourse = $workflow->includesitecourse ? [] : [1];
            if (!$workflow->includedelayedcourses) {
                $delayedcourses = array_merge(delayed_courses_manager::get_delayed_courses_for_workflow($workflow->id),
                    delayed_courses_manager::get_globally_delayed_courses());
            }
            $excludedcourses = array_merge($sitecourse, $delayedcourses);
        }

        $triggercoursesall = [];
        $recordset = $this->get_course_recordset([$trigger], $excludedcourses, true);
        while ($recordset->valid()) {
            $course = $recordset->current();
            $response = $lib->check_course($course, $trigger->id);
            if ($response !== trigger_response::next()) {
                $triggercoursesall[] = $course->id;
            }
            $recordset->next();
        }

        // Get delayed courses which would be triggered by this trigger.
        $delayedcourses = array_intersect($triggercoursesall, $delayedcourses);

        $triggercourses = array_diff($triggercoursesall, $excludedcourses);

        // Only get courses which are not part of this workflow yet. Exclude processes and proc_errors of this wf.
        $sql = "SELECT {course}.id from {course}
                    LEFT JOIN {tool_lifecycle_process} p ON {course}.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
                    WHERE (p.courseid IS NOT NULL AND p.workflowid = $trigger->workflowid)
                    OR (pe.courseid IS NOT NULL AND pe.workflowid = $trigger->workflowid)";
        $stepcourses = $DB->get_fieldset_sql($sql, []);
        $newcourses = array_diff($triggercourses, $stepcourses);

        return [count($triggercourses), count($newcourses), count($delayedcourses)];
    }

    /**
     * Calculates triggered and excluded courses for every trigger of a workflow, and in total.
     * @param object $workflow
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_count_of_courses_to_trigger_for_workflow($workflow) {

        // Exclude delayed courses and sitecourse according to the workflow settings.
        $sitecourse = $workflow->includesitecourse ? [] : [1];
        if ($workflow->includedelayedcourses) {
            $delayedcourses = [];
        } else {
            $delayedcourses = array_merge(delayed_courses_manager::get_delayed_courses_for_workflow($workflow->id),
                delayed_courses_manager::get_globally_delayed_courses());
        }
        $excludedcourses = array_merge($sitecourse, $delayedcourses);

        $triggers = trigger_manager::get_triggers_for_workflow($workflow->id);
        $amounts = [];
        $autotriggers = [];
        $nextrun = 0;
        // If at least one trigger demands the function check_course it will be applied for every trigger.
        $checkcoursecode = false;
        foreach ($triggers as $trigger) {
            $trigger = (object)(array) $trigger; // Cast to normal object to be able to set dynamic properties.
            $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);
            $trigger->exclude = $settings['exclude'] ?? false;
            $obj = new \stdClass();
            $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
            if (!$checkcoursecode) {
                $checkcoursecode = $lib->check_course_code();
            }
            if ($lib->is_manual_trigger()) {
                $obj->automatic = false;
            } else {
                $obj->automatic = true;
                $obj->triggered = 0;
                $obj->excluded = false;
                // Only use triggers with true sql to display the real amounts for the others (instead of always 0).
                $obj->sql = trigger_manager::get_trigger_sqlresult($trigger);
                // We only need the trigger response here.
                $obj->response = $lib->check_course(null, null);
                if ($obj->sql != "false") {
                    // Get courses amounts.
                    // Triggercourses: Courses in current selection without defined excluded courses.
                    // Newcourses: Triggercourses which are not already in workflow (process or process error).
                    // Delayed: Courses in current selection, which are delayed.
                    if ($lib->check_course_code()) {
                        [$triggercourses, $newcourses, $delayed] = $this->get_triggercourses_forcounting_check_course(
                            $trigger, count($excludedcourses));
                    } else {
                        [$triggercourses, $newcourses, $delayed] = $this->get_triggercourses_forcounting(
                            $trigger, count($excludedcourses));
                    }
                    if ($obj->response == trigger_response::exclude()) {
                        $obj->excluded = $newcourses;
                        $obj->delayed = $delayed;
                        $obj->alreadyin = 0;
                    } else if ($obj->response == trigger_response::trigger()) {
                        if ($trigger->exclude) {
                            $obj->excluded = $newcourses;
                            $obj->delayed = $delayed;
                            $obj->alreadyin = 0;
                        } else {
                            $obj->triggered = $newcourses;
                            $obj->delayed = $delayed;
                            $obj->alreadyin = $triggercourses - $newcourses;
                        }
                    }
                    $autotriggers[] = $trigger;
                } else if ($obj->response == trigger_response::triggertime()) {
                    if ($nextrun = $lib->get_next_run_time($trigger->id)) {
                        if ($obj->lastrun = $settings['timelastrun'] ?? 0) {
                            $obj->additionalinfo = get_string('lastrun', 'tool_lifecycle',
                                userdate($settings['timelastrun'], get_string('strftimedatetimeshort', 'langconfig')));
                        } else {
                            $obj->additionalinfo = "--";
                        }
                    } else {
                        $obj->additionalinfo = "--";
                    }
                    $obj->sql = "---";
                    $autotriggers[] = $trigger;
                }
            }
            $amounts[$trigger->sortindex] = $obj;
        }

        $recordset = $this->get_course_recordset($autotriggers, $excludedcourses, true);

        $coursestriggered = 0;
        $usedcourses = 0;
        $coursesdelayed = 0; // Only delayed courses of selected courses are of interest here.
        while ($recordset->valid()) {
            $course = $recordset->current();
            if ($checkcoursecode) {
                $action = false;
                foreach ($autotriggers as $trigger) {
                    $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
                    $response = $lib->check_course($course, $trigger->id);
                    if ($response == trigger_response::next()) {
                        if (!$action) {
                            $action = true;
                        }
                        continue;
                    }
                    if ($response == trigger_response::exclude()) {
                        if (!$action) {
                            $action = true;
                        }
                        continue;
                    }
                    if ($response == trigger_response::trigger()) {
                        continue;
                    }
                }
                if (!$action) {
                    if ($course->hasprocess) {
                        if ($course->workflowid && ($course->workflowid != $workflow->id)) {
                            $usedcourses++;
                        }
                    } else if ($course->delay && $course->delay > time()) {
                        $coursesdelayed++;
                    } else {
                        $coursestriggered++;
                    }
                }
            } else {
                if ($course->hasprocess) {
                    if ($course->workflowid && ($course->workflowid != $workflow->id)) {
                        $usedcourses++;
                    }
                } else if ($course->delay && $course->delay > time()) {
                    $coursesdelayed++;
                } else {
                    $coursestriggered++;
                }
            }
            $recordset->next();
        }

        $all = new \stdClass();
        $all->coursestriggered = $coursestriggered;
        $all->delayedcourses = $coursesdelayed; // Delayed courses for workflow and globally. Excluded per default.
        $all->used = $usedcourses;
        $all->nextrun = $nextrun;
        $amounts['all'] = $all;
        return $amounts;
    }

}
