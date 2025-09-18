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
namespace tool_lifecycle;

use core\task\manager;
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
        $activeworkflows = workflow_manager::get_active_automatic_workflows();
        $globallydelayedcourses = delayed_courses_manager::get_globally_delayed_courses();

        foreach ($activeworkflows as $workflow) {
            $countcourses = 0;
            mtrace('Calling triggers for workflow "' . $workflow->title . '"');
            $triggers = trigger_manager::get_triggers_for_workflow($workflow->id);
            if ($workflow->includesitecourse) {
                $sitecourse = [];
            } else {
                $sitecourse = [1];
            }
            if ($workflow->includedelayedcourses) {
                $delayedcourses = [];
            } else {
                $delayedcourses = array_merge(delayed_courses_manager::get_delayed_courses_for_workflow($workflow->id),
                    $globallydelayedcourses);
            }
            $recordset = $this->get_course_recordset($triggers, array_merge($delayedcourses, $sitecourse));
            while ($recordset->valid()) {
                $course = $recordset->current();
                $countcourses++;
                $process = process_manager::create_process($course->id, $workflow->id);
                process_triggered::event_from_process($process)->trigger();
                $recordset->next();
            }
            mtrace("   $countcourses courses processed.");
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
                    mtrace("The course with id $process->courseid no longer exists. New stdClass with id property is created.");
                    $course = new \stdClass();
                    $course->id = $process->courseid;
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
                    unset($process->context);
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
    
        $where = [];
        $whereparams = [];
        $recordsets = [];
        foreach ($triggers as $trigger) {
            $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
            [$sql, $params] = $lib->get_course_recordset_where($trigger->id);
            if (!empty($sql)) {
                $where[] = 'true AND ' . $sql;
                $whereparams[] = $params;
            }
        }
    
        if (!empty($exclude)) {
            [$insql, $inparams] = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED);
            $where[] = "true AND NOT {course}.id {$insql}";
            $whereparams[] = $inparams;
        }
    
        $maxparams = 65535;
        //$maxparams = 20000;
        mtrace('');
        //mtrace('Start - MAX params: '.$maxparams.', trigger where parts: '.count($where)/*.' '.print_r($where, true)*/.' & params: '.count($whereparams)/*.' '.print_r($whereparams, true)*/);
        foreach($whereparams as $key => $whereparam) {
            if(count($whereparam) > $maxparams) {
                //mtrace('More than '.$maxparams.' params with key '.$key.': '.count($whereparam));
                // Get where part of params array
                $wherepart = $where[$key];
                // Get first & last param
                $first = ':'.array_key_first($whereparam);
                $last = ':'.array_key_last($whereparam);
                //mtrace('   1. Get first param '.$first.' & last param '.$last);
                // Get where part before first param & after last param (to re-create where part)
                $position = strpos($wherepart, $first);
                $before = substr($wherepart, 0, $position);
                $position = strpos($wherepart, $last);
                $after = substr($wherepart, $position + strlen($last));
                //mtrace('   2. Re-create where part: '.$before.' <enter-params> '.$after);
                // Remove original where part & params
                //unset($where[$key]);
                //unset($whereparams[$key]);
                $where[$key] = [];
                $whereparams[$key] = [];
                //mtrace('   3. Remove where part & params with key: '.$key);
                // Chunk params
                $whereparam_chunks = array_chunk($whereparam, $maxparams, true);
                //mtrace('   4. Chunk params: '.count($whereparam_chunks)/*.print_r($whereparam_chunks, true))*/.' ('.count($whereparam).'/'.$maxparams.')');
                // For each chunk of params
                $counter = 0;
                foreach($whereparam_chunks as $whereparam_chunk) {
                    $counter++;
                    // Create param string of chunk params
                    $whereparam_chunk_string = implode(',', array_map(function($value) { return ':' . $value; }, array_keys($whereparam_chunk)));
                    // Re-create where part for chunk
                    $where_chunk = $before.$whereparam_chunk_string.$after;
                    //if(count($whereparam_chunks) > 10 && $counter == 10 ) { mtrace('   ...'); }
                    //if($counter < 5 || $counter >= (count($whereparam_chunks) - 5)) { mtrace('   5.'.$counter.' Add chunk query: '.(strlen($where_chunk) > 150 ? substr($where_chunk, 0, 150) . '...' : $where_chunk).' & params: '.count($whereparam_chunk)/*.print_r($whereparam_chunk, true)*/); }
                    // Add where part & params of chunk
                    if(count($where) && count($whereparams)) {
                        $where[$key][] = $where_chunk;
                        $whereparams[$key][] = $whereparam_chunk;
                    } else { mtrace('ERROR: Amount of where parts & params are not the same!'); }
                }
            }
        }
        //mtrace('End - MAX params: '.$maxparams.', trigger where parts: '.count($where)/*.' '.print_r($where, true)*/.' & params '.count($whereparams)/*.' '.print_r($whereparams, true)*/);
        //mtrace('');
        //die();
    
        if ($forcounting) {
            foreach ($where as $key => $where_tmp) {
                $whereparams_tmp = $whereparams[$key];
                // Get course hasotherprocess and delay with the sql.
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
                    LEFT JOIN {tool_lifecycle_process} p
                    ON {course}.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
                    LEFT JOIN {tool_lifecycle_delayed} d ON {course}.id = d.courseid
                    LEFT JOIN {tool_lifecycle_delayed_workf} dw ON {course}.id = dw.courseid
                    WHERE ";
    
                if(is_array($where_tmp)) {
                    //mtrace('Chunked recordset: '.count($where_tmp)/*.' '.print_r($where_tmp, true)*/.', params: '.count($whereparams_tmp)/*.' '.print_r($whereparams_tmp, true)*/);
                    $tmp = [];
                    foreach($where_tmp as $chunk_key => $chunk_where_tmp) {
                        $sql_tmp = $sql.$chunk_where_tmp;
                        $tmp[] = $DB->get_recordset_sql($sql_tmp, $whereparams_tmp[$chunk_key]);
                    }
                    $recordsets[] = $tmp;
                } else {
                    //mtrace('Nomrmal recordset: '.$where_tmp.', params: '.count($whereparams_tmp)/*.' '.print_r($whereparams_tmp, true)*/);
                    $sql_tmp = $sql.$where_tmp;
                    $recordsets[] = $DB->get_recordset_sql($sql_tmp, $whereparams_tmp);
                }
            }
    
            //use tool_lifecycle\local\intersectedRecordset;
            $recordsets = new \tool_lifecycle\local\intersectedRecordset($recordsets);
            //mtrace('Intersected record sets (for counting): '.count($recordsets));
        } else {
            foreach ($where as $key => $where_tmp) {
                $whereparams_tmp = $whereparams[$key];
                // Get only courses which are not part of an existing process.
                $sql = 'SELECT {course}.id from {course} '.
                        'LEFT JOIN {tool_lifecycle_process} '.
                        'ON {course}.id = {tool_lifecycle_process}.courseid '.
                        'LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid ' .
                        'WHERE {tool_lifecycle_process}.courseid is null AND ' .
                        'pe.courseid IS NULL AND ';
    
                if(is_array($where_tmp)) {
                    //mtrace('Chunked recordset: '.count($where_tmp)/*.' '.print_r($where_tmp, true)*/.', params: '.count($whereparams_tmp)/*.' '.print_r($whereparams_tmp, true)*/);
                    $tmp = [];
                    foreach($where_tmp as $chunk_key => $chunk_where_tmp) {
                        $sql_tmp = $sql.$chunk_where_tmp;
                        $tmp[] = $DB->get_recordset_sql($sql_tmp, $whereparams_tmp[$chunk_key]);
                    }
                    $recordsets[] = $tmp;
                } else {
                    //mtrace('Nomrmal recordset: '.$where_tmp.', params: '.count($whereparams_tmp)/*.' '.print_r($whereparams_tmp, true)*/);
                    $sql_tmp = $sql.$where_tmp;
                    $recordsets[] = $DB->get_recordset_sql($sql_tmp, $whereparams_tmp);
                }
            }
    
            //use tool_lifecycle\local\intersectedRecordset;
            $recordsets = new \tool_lifecycle\local\intersectedRecordset($recordsets);
            //mtrace('Intersected record sets: '.count($recordsets));
        }
    
        //mtrace('');
        //mtrace('FINAL recordsets: '.$recordsets->count()/*.' '.print_r($recordsets, true)*/);
        //mtrace('');
        //die();
    
        return $recordsets;
    }

    /**
     * Returns the amount of courses for a trigger for counting.
     * Relevant means that there is currently no lifecycle process running for this course.
     * @param trigger_subplugin $trigger trigger, which will be asked for additional where requirements.
     * @param int[] $exclude List of course id, which should be excluded from execution.
     * @return int $amount of triggered courses.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_triggercourses_forcounting($trigger, $exclude) {
        global $DB;

        $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
        // Get SQL for this trigger.
        [$sql, $whereparams] = $lib->get_course_recordset_where($trigger->id);
        // We just want the triggered courses here, no matter of including or excluding.
        $where = str_replace(" NOT ", " ", $sql);
        // Exclude courses in steps of this wf, delayed courses and sitecourse according to the workflow settings.
        if (!empty($exclude)) {
            [$insql, $inparams] = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED);
            $where .= " AND NOT {course}.id {$insql}";
            $whereparams = array_merge($whereparams, $inparams);
        }
        // Now get the amount of courses triggered by this trigger.
        $sql = 'SELECT {course}.id from {course} WHERE '. $where;
        $triggercourses = $DB->get_records_sql($sql, $whereparams);
        $sql .= " AND {course}.id NOT IN (".
            "SELECT {course}.id from {course}
                LEFT JOIN {tool_lifecycle_process}
                ON {course}.id = {tool_lifecycle_process}.courseid
                LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid
                WHERE ({tool_lifecycle_process}.courseid IS NOT NULL AND {tool_lifecycle_process}.workflowid = $trigger->workflowid)
                OR (pe.courseid IS NOT NULL AND pe.workflowid = $trigger->workflowid))";
        $newcourses = $DB->get_records_sql($sql, $whereparams);

        return [count($triggercourses), count($newcourses)];
    }

    /**
     * Returns a record set with all relevant courses for a trigger for counting.
     * Relevant means that there is currently no lifecycle process running for this course.
     * @param trigger_subplugin $trigger trigger, which will be asked for additional where requirements.
     * @param object $workflow workflow instance.
     * @return \moodle_recordset with relevant courses.
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

        $counttriggered = 0;
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
                $obj->excluded = 0;
                // Only use triggers with true sql to display the real amounts for the others (instead of always 0).
                $obj->sql = trigger_manager::get_trigger_sqlresult($trigger);
                $obj->response = $lib->check_course(null, null);
                if ($obj->sql != "false") {
                    if ($obj->response == trigger_response::exclude()) {
                        // Get courses excluded amount.
                        [$triggercourses, $newcourses] = $this->get_triggercourses_forcounting($trigger, $excludedcourses);
                        $obj->excluded = $triggercourses;
                        $obj->alreadyin = $triggercourses - $newcourses;
                    } else if ($obj->response == trigger_response::trigger()) {
                        // Get courses triggered amount.
                        [$triggercourses, $newcourses] = $this->get_triggercourses_forcounting($trigger, $excludedcourses);
                        if ($trigger->exclude) {
                            $obj->excluded = $triggercourses;
                        } else {
                            $obj->triggered = $triggercourses;
                        }
                        $obj->alreadyin = $triggercourses - $newcourses;
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
            } else if ($course->delay) {
                $delayedcourses[] = $course->id;
            } else {
                $counttriggered++;
                $coursestriggered[] = $course->id;
            }
            $recordset->next();
        }

        $all = new \stdClass();
        $all->triggered = $counttriggered;
        $all->coursestriggered = $coursestriggered;
        $all->delayedcourses = $delayedcourses; // Delayed courses for workflow and globally. Excluded per default.
        $all->used = $usedcourses;
        $all->nextrun = $nextrun;
        $amounts['all'] = $all;
        return $amounts;
    }

}
