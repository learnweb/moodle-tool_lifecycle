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
        $exclude = [];
        $globallydelayedcourses = delayed_courses_manager::get_globally_delayed_courses();

        foreach ($activeworkflows as $workflow) {
            $countcourses = 0;
            $counttriggered = 0;
            $countexcluded = 0;
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
            $recordset = $this->get_course_recordset($triggers, array_merge($exclude, $delayedcourses, $sitecourse));
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
            mtrace("   $countcourses courses processed.");
            mtrace("   $counttriggered courses triggered.");
            mtrace("   $countexcluded courses excluded.");
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
     * @param bool $includecourseswithprocess
     * @return \moodle_recordset with relevant courses.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset($triggers, $exclude, $includecourseswithprocess = false) {
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
            list($insql, $inparams) = $DB->get_in_or_equal($exclude, SQL_PARAMS_NAMED);
            $where .= " AND NOT {course}.id {$insql}";
            $whereparams = array_merge($whereparams, $inparams);
        }

        if ($includecourseswithprocess) {
            // Get also courses which are part of an existing process.
            $sql = 'SELECT {course}.* from {course} WHERE ' . $where;
        } else {
            // Get only courses which are not part of an existing process.
            $sql = 'SELECT {course}.* from {course} '.
                'left join {tool_lifecycle_process} '.
                'ON {course}.id = {tool_lifecycle_process}.courseid '.
                'LEFT JOIN {tool_lifecycle_proc_error} pe ON {course}.id = pe.courseid ' .
                'WHERE {tool_lifecycle_process}.courseid is null AND ' .
                'pe.courseid IS NULL AND '. $where;
        }
        return $DB->get_recordset_sql($sql, $whereparams);
    }

    /**
     * Calculates triggered and excluded courses for every trigger of a workflow, and in total.
     * @param object $workflow
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_count_of_courses_to_trigger_for_workflow($workflow) {
        $countcourses = 0;
        $counttriggered = 0;
        $countexcluded = 0;
        $countdelayed = 0;
        $usedcourses = [];

        $triggers = trigger_manager::get_triggers_for_workflow($workflow->id);

        $amounts = [];
        $autotriggers = [];
        foreach ($triggers as $trigger) {
            $trigger = (object)(array) $trigger; // Cast to normal object to be able to set dynamic properties.
            $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);
            $trigger->exclude = $settings['exclude'] ?? false;
            $obj = new \stdClass();
            if (lib_manager::get_trigger_lib($trigger->subpluginname)->is_manual_trigger()) {
                $obj->automatic = false;
            } else {
                $obj->automatic = true;
                $obj->triggered = 0;
                $obj->excluded = 0;
                // Only use triggers with true sql to display the real amounts for the others instead of 0.
                if (trigger_manager::get_trigger_sqlresult($trigger) != "false") {
                    $autotriggers[] = $trigger;
                } else {
                    if (is_int($settings['timelastrun'])) {
                        $obj->additionalinfo = get_string('lastrun', 'tool_lifecycle',
                            userdate($settings['timelastrun'], get_string('strftimedatetimeshort', 'langconfig')));
                    } else {
                        $obj->additionalinfo = '-';
                    }
                }
            }
            $amounts[$trigger->sortindex] = $obj;
        }

        $globallydelayedcourses = delayed_courses_manager::get_globally_delayed_courses();
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
        $excludedcourses = array_merge($sitecourse, $delayedcourses);

        $recordset = $this->get_course_recordset($autotriggers, $excludedcourses, true);

        while ($recordset->valid()) {
            $course = $recordset->current();
            $delaytime = max(delayed_courses_manager::get_course_delayed($course->id),
                delayed_courses_manager::get_course_delayed_workflow($course->id, $workflow->id));
            $coursedelayed = $delaytime > time();
            if ($hasother = process_manager::has_other_process($course->id, $workflow->id)) {
                if ($hasother == OTHERWORKFLOW) {
                    $usedcourses[] = $course->id;
                }
                $recordset->next();
            } else {
                $countcourses++;
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
                            $countexcluded++;
                        }
                        $amounts[$trigger->sortindex]->excluded++;
                        continue;
                    }
                    if ($response == trigger_response::trigger()) {
                        if ($trigger->exclude) {
                            if (!$action) {
                                $action = true;
                                $countexcluded++;
                            }
                            $amounts[$trigger->sortindex]->excluded++;
                        } else {
                            $amounts[$trigger->sortindex]->triggered++;
                        }
                    }
                }
                if (!$action) {
                    $counttriggered++;
                    if ($coursedelayed) {
                        $countdelayed++;
                    }
                }
                $recordset->next();
            }
        }

        $all = new \stdClass();
        $all->excluded = $countexcluded;
        $all->triggered = $counttriggered;
        $all->delayed = $countdelayed;
        $all->used = $usedcourses;
        $all->coursesetsize = $countcourses;

        $amounts['all'] = $all;
        return $amounts;
    }

    /**
     * Returns a list of triggered courses for a trigger of a workflow but delays and course 1 are not excluded.
     * @param trigger_subplugin $triggerin the trigger who triggers the courses
     * @param int $workflowid id of the current workflow
     * @return int[] $courseids
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_courses_to_trigger_for_trigger($triggerin, $workflowid) {

        $courseids = [];

        $triggers = trigger_manager::get_triggers_for_workflow($workflowid);
        $autotriggers = [];
        foreach ($triggers as $trigger) {
            // Only use triggers with true sql to display the real amounts for the others instead of 0.
            if (trigger_manager::get_trigger_sqlresult($trigger)) {
                $trigger = (object)(array)$trigger; // Cast to normal object to be able to set dynamic properties.
                $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);
                $trigger->exclude = $settings['exclude'] ?? false;
                if (!lib_manager::get_trigger_lib($trigger->subpluginname)->is_manual_trigger()) {
                    $autotriggers[] = $trigger;
                }
            }
        }

        $recordset = $this->get_course_recordset($autotriggers, [], false);
        while ($recordset->valid()) {
            $course = $recordset->current();
            $action = false;
            foreach ($autotriggers as $trigger) {
                $thistrigger = $trigger->id == $triggerin->id;
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
                    if ($trigger->exclude) {
                        if (!$action) {
                            $action = true;
                        }
                    } else {
                        if ($thistrigger) {
                            $courseids[] = $course->id;
                        }
                    }
                }
            }
            $recordset->next();
        }

        return $courseids;
    }

    /**
     * Returns a list of excluded courses for a trigger of a workflow.
     * @param trigger_subplugin $triggerex
     * @param int $workflowid
     * @return int[] $courseids
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_courses_to_exclude_for_trigger($triggerex, $workflowid) {

        $courseids = [];

        $triggers = trigger_manager::get_triggers_for_workflow($workflowid);
        $autotriggers = [];
        foreach ($triggers as $trigger) {
            // Only use triggers with true sql to display the real amounts for the others instead of 0.
            if (trigger_manager::get_trigger_sqlresult($trigger)) {
                $trigger = (object)(array)$trigger; // Cast to normal object to be able to set dynamic properties.
                $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);
                $trigger->exclude = $settings['exclude'] ?? false;
                if (!lib_manager::get_trigger_lib($trigger->subpluginname)->is_manual_trigger()) {
                    $autotriggers[] = $trigger;
                }
            }
        }

        $recordset = $this->get_course_recordset($autotriggers, [], false);
        while ($recordset->valid()) {
            $course = $recordset->current();
            $action = false;
            foreach ($autotriggers as $trigger) {
                $thistrigger = $trigger->id == $triggerex->id;
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
                    if ($thistrigger) {
                        $courseids[] = $course->id;
                    }
                    continue;
                }
                if ($response == trigger_response::trigger()) {
                    if ($trigger->exclude) {
                        if (!$action) {
                            $action = true;
                        }
                        if ($thistrigger) {
                            $courseids[] = $course->id;
                        }
                    }
                }
            }
            $recordset->next();
        }

        return $courseids;
    }

    /**
     * Returns a list of delayed courses for a workflow.
     * @param int $workflowid
     * @return int[] $courseids
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_courses_delayed_for_workflow($workflowid) {

        // Get delayed courses for this workflow.
        $courseids = delayed_courses_manager::get_delayed_courses_for_workflow($workflowid);

        return $courseids;
    }
}
