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

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\event\process_triggered;
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

define("OTHERWORKFLOW", 2);

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
                    $exclude);
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
            $workflow = workflow_manager::get_workflow($process->workflowid);
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
                        delayed_courses_manager::set_course_delayed_for_workflow($course->id, false, $workflow);
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
                        delayed_courses_manager::set_course_delayed_for_workflow($course->id, false, $workflow);
                        break;
                    }
                } else if ($result == step_response::rollback()) {
                    delayed_courses_manager::set_course_delayed_for_workflow($course->id, true, $workflow);
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
        global $DB;

        $where = 'true';
        $whereparams = [];
        foreach ($triggers as $trigger) {
            $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
            [$sql, $params] = $lib->get_course_recordset_where($trigger->id);
            if (!empty($sql)) {
                $where .= ' AND ' . $sql;
                $whereparams = array_merge($whereparams, $params);
            }
        }

        if (!empty($exclude)) {
            [$insql, $inparams] = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED);
            $where .= " AND NOT {course}.id {$insql}";
            $whereparams = array_merge($whereparams, $inparams);
        }

        if ($forcounting) {
            // Get course hasprocess and delay with the sql.
            $sql = "SELECT {course}.id,
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
                    FROM {course}
                    LEFT JOIN {tool_lifecycle_process} p ON {course}.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
                    LEFT JOIN {tool_lifecycle_delayed} d ON {course}.id = d.courseid
                    LEFT JOIN {tool_lifecycle_delayed_workf} dw ON {course}.id = dw.courseid
                    WHERE " . $where;
        } else {
            // Get only courses which are not part of an existing process.
            $sql = "SELECT {course}.id from {course}
                    LEFT JOIN {tool_lifecycle_process} p ON {course}.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
                    WHERE p.courseid is null AND pe.courseid IS NULL AND " . $where;
        }
        return $DB->get_recordset_sql($sql, $whereparams);
    }

    /**
     * Returns the amount of courses for a trigger for counting.
     * Relevant means that there is currently no lifecycle process running for this course.
     * @param trigger_subplugin $trigger trigger, which will be asked for additional where requirements.
     * @param int[] $excluded List of course id, which should be excluded from counting.
     * @param int[] $delayed List of course ids of delayed courses (globally and for workflow).
     * @return int[] $triggered, $new, $delayed Triggered courses and amount which courses of them would
     * be added and which courses are delayed.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_triggercourses_forcounting($trigger, $excluded, $delayed) {
        global $DB;

        $triggercourses = 0;
        $delayedcourses = 0;
        $newcourses = 0;

        $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
        // Get SQL for this trigger.
        [$sql, $whereparams] = $lib->get_course_recordset_where($trigger->id);

        // We just want the triggered courses here, no matter of including or excluding.
        $where = str_replace(" NOT ", " ", $sql);

        // Now get the amount of courses triggered by this trigger.
        $sql = 'SELECT {course}.id from {course} WHERE '. $where;
        $triggercoursesall = $DB->get_fieldset_sql($sql, $whereparams);

        // Get delayed courses which would be triggered by this trigger.
        $delayedcourses = count(array_intersect($triggercoursesall, $delayed));

        // Exclude delayed courses and sitecourse according to the workflow settings.
        if (!empty($excluded)) {
            [$insql, $inparams] = $DB->get_in_or_equal($excluded, SQL_PARAMS_NAMED);
            $where .= " AND NOT {course}.id {$insql}";
            $whereparams = array_merge($whereparams, $inparams);
        }
        $sql = 'SELECT count({course}.id) from {course} WHERE '. $where;
        $triggercourses = $DB->count_records_sql($sql, $whereparams);

        // Only get courses which are not part of this workflow yet. Exclude processes and proc_errors of this wf.
        $sql .= " AND {course}.id NOT IN (
                    SELECT {course}.id from {course}
                    LEFT JOIN {tool_lifecycle_process} p ON {course}.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
                    WHERE (p.courseid IS NOT NULL AND p.workflowid = $trigger->workflowid)
                    OR (pe.courseid IS NOT NULL AND pe.workflowid = $trigger->workflowid)
                )";
        $newcourses = $DB->count_records_sql($sql, $whereparams);

        return [$triggercourses, $newcourses, $delayedcourses];
    }

    /**
     * Returns a record set with all relevant courses for a trigger for counting.
     * Relevant means that there is currently no lifecycle process running for this course.
     * @param trigger_subplugin $trigger trigger, which will be asked for additional where requirements.
     * @param object $workflow workflow instance.
     * @return array with relevant courses.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_triggercourses($trigger, $workflow) {
        global $DB;

        // Exclude delayed courses and sitecourse according to the workflow settings.
        $sitecourse = $workflow->includesitecourse ? [] : [1];
        if ($workflow->includedelayedcourses) {
            $delayedcourses = [];
        } else {
            $delayedcourses = array_merge(delayed_courses_manager::get_delayed_courses_for_workflow($workflow->id),
                delayed_courses_manager::get_globally_delayed_courses());
        }
        // Exclude courses in steps of this workflow.
        $sqlstepcourses = "SELECT {course}.id from {course}
            LEFT JOIN {tool_lifecycle_process}
            ON {course}.id = {tool_lifecycle_process}.courseid
            LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
            WHERE ({tool_lifecycle_process}.courseid IS NOT NULL AND {tool_lifecycle_process}.workflowid = $workflow->id)
            OR (pe.courseid IS NOT NULL AND pe.workflowid = $workflow->id)";
        $stepcourses = $DB->get_fieldset_sql($sqlstepcourses);
        $excludedcourses = array_merge($sitecourse, $delayedcourses, $stepcourses);

        $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
        // Get SQL for this trigger.
        [$sql, $whereparams] = $lib->get_course_recordset_where($trigger->id);
        // We just want the triggered courses here, no matter of including or excluding.
        $where = str_replace(" NOT ", " ", $sql);
        if (!empty($excludedcourses)) {
            [$insql, $inparams] = $DB->get_in_or_equal($excludedcourses, SQL_PARAMS_NAMED);
            $where .= " AND NOT {course}.id {$insql}";
            $whereparams = array_merge($whereparams, $inparams);
        }
        // Now get the list of course IDs triggered by this trigger.
        $sql = "SELECT {course}.id FROM {course} WHERE " . $where;
        return $DB->get_fieldset_sql($sql, $whereparams);
    }

    /**
     * Calculates triggered and excluded courses for every trigger of a workflow, and in total.
     * @param object $workflow
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_count_of_courses_to_trigger_for_workflow($workflow) {
        global $DB;

        $coursestriggered = [];
        $usedcourses = [];

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
        foreach ($triggers as $trigger) {
            $trigger = (object)(array) $trigger; // Cast to normal object to be able to set dynamic properties.
            $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);
            $trigger->exclude = $settings['exclude'] ?? false;
            $obj = new \stdClass();
            $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
            if ($lib->is_manual_trigger()) {
                $obj->automatic = false;
            } else {
                $obj->automatic = true;
                $obj->triggered = 0;
                $obj->excluded = false;
                // Only use triggers with true sql to display the real amounts for the others (instead of always 0).
                $obj->sql = trigger_manager::get_trigger_sqlresult($trigger);
                $obj->response = $lib->check_course(null, null);
                if ($obj->sql != "false") {
                    // Get courses amount.
                    // Triggercourses: Courses in current selection without defined excluded courses.
                    // Newcourses: Triggercourses which are not already in workflow (process or process error).
                    // Delayed: Courses in current selection, which are delayed.
                    [$triggercourses, $newcourses, $delayed] = $this->get_triggercourses_forcounting($trigger, $excludedcourses,
                        $delayedcourses);
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
                        $obj->lastrun = $settings['timelastrun'];
                        $obj->additionalinfo = get_string('lastrun', 'tool_lifecycle',
                            userdate($settings['timelastrun'], get_string('strftimedatetimeshort', 'langconfig')));
                    } else {
                        $obj->additionalinfo = '-';
                    }
                    $obj->sql = "---";
                    $autotriggers[] = $trigger;
                }
            }
            $amounts[$trigger->sortindex] = $obj;
        }

        // Exclude courses in steps of this workflow.
        $sqlstepcourses = "SELECT {course}.id from {course}
            LEFT JOIN {tool_lifecycle_process}
            ON {course}.id = {tool_lifecycle_process}.courseid
            LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
            WHERE ({tool_lifecycle_process}.courseid IS NOT NULL AND {tool_lifecycle_process}.workflowid = $workflow->id)
            OR (pe.courseid IS NOT NULL AND pe.workflowid = $workflow->id)";
        $excludedcourses = array_merge($DB->get_fieldset_sql($sqlstepcourses), $sitecourse);

        $delayedcourses = []; // Only delayed courses of selected courses are of interest here.
        $recordset = $this->get_course_recordset($autotriggers, $excludedcourses, true);
        while ($recordset->valid()) {
            $course = $recordset->current();
            if ($course->hasprocess) {
                if ($course->workflowid && ($course->workflowid != $workflow->id)) {
                    $usedcourses[] = $course->id;
                }
            } else if ($course->delay && $course->delay > time()) {
                $delayedcourses[] = $course->id;
            } else {
                $coursestriggered[] = $course->id;
            }
            $recordset->next();
        }

        $all = new \stdClass();
        $all->coursestriggered = $coursestriggered;
        $all->delayedcourses = $delayedcourses; // Delayed courses for workflow and globally. Excluded per default.
        $all->used = $usedcourses;
        $all->nextrun = $nextrun;
        $amounts['all'] = $all;
        return $amounts;
    }

}
