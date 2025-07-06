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
 * Displays a workflow in a nice visual form.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2021 Nina Herrmann and Justus Dieckmann, WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

define('PAGESIZE', 20);

use core\output\notification;
use core\output\single_button;
use core\task\manager;
use tool_lifecycle\action;
use tool_lifecycle\event\process_triggered;
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\local\table\courses_in_step_table;
use tool_lifecycle\local\table\process_courses_table;
use tool_lifecycle\local\table\triggered_courses_table;
use tool_lifecycle\processor;
use tool_lifecycle\settings_type;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_login();

$workflowid = required_param('wf', PARAM_INT);
$stepid = optional_param('step', null, PARAM_INT);
$triggerid = optional_param('trigger', null, PARAM_INT);
$triggered = optional_param('triggered', null, PARAM_INT);
$excluded = optional_param('excluded', null, PARAM_INT);
$delayed = optional_param('delayed', null, PARAM_INT);
$processes = optional_param('processes', null, PARAM_INT);
$used = optional_param('used', null, PARAM_INT);
$search = optional_param('search', null, PARAM_RAW);
$showdetails = optional_param('showdetails', 0, PARAM_INT);
if ($showdetails == 0) {
    if (isset($SESSION->showdetails)) {
        if ($SESSION->showdetails == $workflowid) {
            $showdetails = $workflowid;
        }
    }
} else if ($showdetails == -1) {
    $SESSION->showdetails = $showdetails = 0;
} else {
    $SESSION->showdetails = $workflowid;
}

$workflow = workflow_manager::get_workflow($workflowid);
$iseditable = workflow_manager::is_editable($workflow->id);
$isactive = workflow_manager::is_active($workflow->id);
$isdeactivated = workflow_manager::is_deactivated($workflow->id);

$params = ['wf' => $workflow->id];
if ($stepid) {
    $params['step'] = $stepid;
} else if ($triggerid) {
    $params['trigger'] = $triggerid;
} else if ($triggered) {
    $params['triggered'] = $triggered;
} else if ($delayed) {
    $params['delayed'] = $delayed;
} else if ($excluded) {
    $params['excluded'] = $excluded;
} else if ($processes) {
    $params['processes'] = $processes;
}
if ($search) {
    $params['search'] = $search;
}

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::WORKFLOW_DETAILS, $params));
$PAGE->set_context($syscontext);
$PAGE->set_title($workflow->title);

// Link to open the popup with the course list.
$popuplink = new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflow->id]);
// Link for loading the page with no popupwindow.
$nosteplink = new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflowid]);
// Link for changing the extended details view mode.
$showdetailslink = new moodle_url(urls::WORKFLOW_DETAILS, $params);

$action = optional_param('action', null, PARAM_TEXT);
$msg = "";
if ($action) {
    if ($action == 'select') {
        $cid = required_param('cid', PARAM_INT);
        $process = process_manager::create_process($cid, $workflow->id);
        process_triggered::event_from_process($process)->trigger();
        process_manager::proceed_process($process);
        delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, false, $workflow);
        $msg = get_string('courseselected', 'tool_lifecycle');
    } else if ($action == 'deletedelay') {
        $cid = required_param('cid', PARAM_INT);
        $DB->delete_records('tool_lifecycle_delayed_workf', ['courseid' => $cid]);
        delayed_courses_manager::remove_delay_entry($cid);
        $msg = get_string('delaydeleted', 'tool_lifecycle');
    } else {
        if ($actionstep = optional_param('actionstep', null, PARAM_INT)) {
            step_manager::handle_action($action, $actionstep, $workflow->id);
        }
        if ($actiontrigger = optional_param('actiontrigger', null, PARAM_INT)) {
            trigger_manager::handle_action($action, $actiontrigger, $workflow->id);
        }
        $processid = optional_param('processid', null, PARAM_INT);
        if ($processid) {
            $process = process_manager::get_process_by_id($processid);
            if ($action === 'rollback') {
                process_manager::rollback_process($process);
                delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, true, $workflow);
                $msg = get_string('courserolledback', 'tool_lifecycle');
            } else if ($action === 'proceed') {
                process_manager::proceed_process($process);
                delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, false, $workflow);
                $msg = get_string('courseproceeded', 'tool_lifecycle');
            } else {
                throw new coding_exception('processid was specified but action was neither "rollback" nor "proceed"!');
            }
        }
    }
    redirect($PAGE->url, $msg, null, notification::NOTIFY_SUCCESS);
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".
    get_string('workflowoverview', 'tool_lifecycle').": ". $workflow->title;
echo $renderer->header($heading);

$tabparams = new stdClass();
if ($isactive) {  // Active workflow.
    $id = 'activeworkflows';
    $tabparams->activelink = true;
    $classdetails = "bg-primary text-white";
} else {
    if ($isdeactivated) { // Deactivated workflow.
        $id = 'deactivatedworkflows';
        $tabparams->deactivatedlink = true;
        $classdetails = "bg-dark text-white";
    } else { // Draft.
        $id = 'workflowdrafts';
        $tabparams->draftlink = true;
        $classdetails = "bg-light";
    }
}
$tabrow = tabs::get_tabrow($tabparams);
$renderer->tabs($tabrow, $id);

$steps = step_manager::get_step_instances($workflow->id);
$triggers = trigger_manager::get_triggers_for_workflow($workflow->id);

$str = [
    'edit' => get_string('edit'),
    'delete' => get_string('delete'),
    'move_up' => get_string('move_up', 'tool_lifecycle'),
    'move_down' => get_string('move_down', 'tool_lifecycle'),
];

$nextrun = false;
$coursestriggered = [];
$coursesdelayed = [];
$displaytotaltriggered = false;
if ($showdetails) {
    /*
     * Preview of what courses would be triggered if the course selection would run now.
     * For each trigger the amount of the select statement without the courses already in this process will be count.
     * The amount of courses already in the process is shown as well.
    */
    $amounts = (new processor())->get_count_of_courses_to_trigger_for_workflow($workflow);
    $coursestriggered = $amounts['all']->coursestriggered;
    $coursesdelayed = $amounts['all']->delayedcourses;
    $nextrun = $amounts['all']->nextrun == 0 ? false : $amounts['all']->nextrun;
    $displaytotaltriggered = !empty($triggers);
}

$task = manager::get_scheduled_task('tool_lifecycle\task\lifecycle_task');
$lastrun = $task->get_last_run_time();
$nextrunt = $task->get_next_run_time();
$nextrunout = "";
if (!$task->is_component_enabled() && !$task->get_run_if_component_disabled()) {
    $nextrunt = get_string('plugindisabled', 'tool_task');
} else if ($task->get_disabled()) {
    $nextrunt = get_string('taskdisabled', 'tool_task');
} else if (is_numeric($nextrunt) && $nextrunt < time()) {
    $nextrunt = get_string('asap', 'tool_task');
}
if (is_numeric($nextrunt) && is_numeric($nextrun)) { // Task nextrun and trigger nextrun are valid times: take the minimum.
    $nextrunout = min($nextrunt, $nextrun);
} else if (!is_numeric($nextrunt) && is_numeric($nextrun)) { // Only trigger nextrun is valid time.
    $nextrun = $nextrun;
} else if (is_numeric($nextrunt)) { // Only task next run is valid time.
    $nextrunout = $nextrunt;
} else { // There is no valid next run time. Print the task message.
    $nextrunout = $nextrunt;
}
if (is_numeric($nextrunout)) {
    if ($nextrunout) {
        $nextrunout = userdate($nextrunout, get_string('strftimedatetimeshort', 'langconfig'));
    } else {
        $nextrunout = get_string('statusunknown');
    }
}

$nomanualtriggerinvolved = true;
$displaytriggers = [];
$displaytimetriggers = [];
foreach ($triggers as $trigger) {
    // The array from the DB Function uses ids as keys.
    // Mustache cannot handle arrays which have other keys therefore a new array is build.
    // FUTURE: Nice to have Icon for each subplugin.
    $trigger = (object)(array) $trigger; // Cast to normal object to be able to set dynamic properties.
    $actionmenu = new action_menu([
        new action_menu_link_secondary(
            new moodle_url(urls::EDIT_ELEMENT, ['type' => settings_type::TRIGGER, 'elementid' => $trigger->id]),
            new pix_icon('i/edit', $str['edit']), $str['edit']),
    ]);
    if ($iseditable) {
        $actionmenu->add(new action_menu_link_secondary(
            new moodle_url($PAGE->url,
                ['action' => action::TRIGGER_INSTANCE_DELETE, 'sesskey' => sesskey(), 'actiontrigger' => $trigger->id]),
            new pix_icon('t/delete', $str['delete']), $str['delete'])
        );
    }
    $trigger->actionmenu = $OUTPUT->render($actionmenu);
    $response = null;
    $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
    if ($trigger->automatic = !$lib->is_manual_trigger()) {
        $response = $lib->check_course(null, null);
    }
    $nomanualtriggerinvolved &= $trigger->automatic;
    if ($showdetails) {
        if ($trigger->automatic) {
            $sqlresult = trigger_manager::get_trigger_sqlresult($trigger);
            if ($sqlresult == "false") {
                $trigger->classfires = "border-danger";
                $trigger->additionalinfo = $amounts[$trigger->sortindex]->additionalinfo ?? "-";
            } else {
                if ($response != trigger_response::triggertime()) {
                    if ($amounts[$trigger->sortindex]->triggered) {
                        $trigger->classfires = "border-success";
                    } else if ($amounts[$trigger->sortindex]->excluded) {
                        $trigger->classfires = "border-danger";
                    }
                    $trigger->tooltip = "";
                    if ($trigger->excludedcourses = $amounts[$trigger->sortindex]->excluded) {
                        $trigger->tooltip = get_string('courses_will_be_excluded',
                            'tool_lifecycle', $trigger->excludedcourses);
                    } else {
                        $trigger->triggeredcourses = $amounts[$trigger->sortindex]->triggered;
                        $trigger->tooltip = get_string('courses_will_be_triggered',
                            'tool_lifecycle', $trigger->triggeredcourses);
                        if ($trigger->delayedcourses = $amounts[$trigger->sortindex]->delayed) {
                            $trigger->tooltip .= get_string('courses_candidates_delayed',
                                'tool_lifecycle', $trigger->delayedcourses);
                        }
                        if ($trigger->alreadyin = $amounts[$trigger->sortindex]->alreadyin) {
                            $trigger->tooltip .= get_string('courses_candidates_alreadyin',
                                'tool_lifecycle', $trigger->alreadyin);
                        }
                    }
                }
            }
        }
        $displaytotaltriggered &= $trigger->automatic;
    }
    if ($response == trigger_response::triggertime()) {
        $displaytimetriggers[] = $trigger;
        if (isset($amounts[$trigger->sortindex]->lastrun)) {
            $lastrun = $amounts[$trigger->sortindex]->lastrun;
        }
    } else {
        $displaytriggers[] = $trigger;
    }
}

$displaysteps = [];
foreach ($steps as $step) {
    $step = (object)(array) $step; // Cast to normal object to be able to set dynamic properties.
    $ncourses = $DB->count_records('tool_lifecycle_process',
        ['stepindex' => $step->sortindex, 'workflowid' => $workflowid]);
    $step->numberofcourses = $ncourses;
    if ($step->id == $stepid) {
        $step->selected = true;
    }
    $actionmenu = new action_menu([
        new action_menu_link_secondary(
            new moodle_url(urls::EDIT_ELEMENT, ['type' => settings_type::STEP, 'elementid' => $step->id]),
            new pix_icon('i/edit', $str['edit']), $str['edit']),
    ]);
    if ($iseditable) {
        $actionmenu->add(new action_menu_link_secondary(
            new moodle_url($PAGE->url,
                ['action' => action::STEP_INSTANCE_DELETE, 'sesskey' => sesskey(), 'actionstep' => $step->id]),
            new pix_icon('t/delete', $str['delete']), $str['delete'])
        );
        if ($step->sortindex > 1) {
            $actionmenu->add(new action_menu_link_secondary(
                new moodle_url($PAGE->url,
                    ['action' => action::UP_STEP, 'sesskey' => sesskey(), 'actionstep' => $step->id]),
                new pix_icon('t/up', $str['move_up']), $str['move_up'])
            );
        }
        if ($step->sortindex < count($steps)) {
            $actionmenu->add(new action_menu_link_secondary(
                    new moodle_url($PAGE->url,
                        ['action' => action::DOWN_STEP, 'sesskey' => sesskey(), 'actionstep' => $step->id]),
                    new pix_icon('t/down', $str['move_down']), $str['move_down'])
            );
        }
    }
    $step->actionmenu = $OUTPUT->render($actionmenu);
    $displaysteps[] = $step;
}

// Popup courses list.
$out = null;
$tablecoursesamount = 0;
$hiddenfieldssearch = [];
$hiddenfieldssearch[] = ['name' => 'wf', 'value' => $workflowid];
$hiddenfieldssearch[] = ['name' => 'showdetails', 'value' => $showdetails];
if ($stepid) { // Display courses table with courses of this step.
    $step = step_manager::get_step_instance($stepid);
    $courseids = $DB->count_records('tool_lifecycle_process',
        ['stepindex' => $step->sortindex, 'workflowid' => $workflowid]);
    $table = new courses_in_step_table($step,
        optional_param('courseid', null, PARAM_INT), $courseids, $search);
    ob_start();
    $table->out(PAGESIZE, false);
    $out = ob_get_contents();
    ob_end_clean();
    $hiddenfieldssearch[] = ['name' => 'step', 'value' => $stepid];
    $tablecoursesamount = $courseids;
} else if ($triggerid) { // Display courses table with triggered courses of this trigger.
    $trigger = trigger_manager::get_instance($triggerid);
    if ($courseids = (new processor())->get_triggercourses($trigger, $workflow)) {
        $table = new triggered_courses_table($courseids, 'triggered', $trigger->instancename,
            null, $workflow->id, $search);
        ob_start();
        $table->out(PAGESIZE, false);
        $out = ob_get_contents();
        ob_end_clean();
        $hiddenfieldssearch[] = ['name' => 'trigger', 'value' => $triggerid];
        $tablecoursesamount = count($courseids);
    }
} else if ($triggered) { // Display courses table with triggered courses of this workflow.
    $table = new triggered_courses_table($coursestriggered, 'triggeredworkflow', null,
        $workflow->title, $workflow->id, $search);
    ob_start();
    $table->out(PAGESIZE, false);
    $out = ob_get_contents();
    ob_end_clean();
    $hiddenfieldssearch[] = ['name' => 'triggered', 'value' => $triggered];
    $tablecoursesamount = count($coursestriggered);
} else if ($excluded) { // Display courses table with excluded courses of this trigger.
    $trigger = trigger_manager::get_instance($excluded);
    if ($courseids = (new processor())->get_triggercourses($trigger, $workflow)) {
        $table = new triggered_courses_table($courseids, 'exclude', $trigger->instancename, null, null, $search);
        ob_start();
        $table->out(PAGESIZE, false);
        $out = ob_get_contents();
        ob_end_clean();
        $hiddenfieldssearch[] = ['name' => 'excluded', 'value' => $excluded];
        $tablecoursesamount = count($courseids);
    }
} else if ($delayed) { // Display courses table with courses delayed for this workflow.
    $table = new triggered_courses_table( $coursesdelayed, 'delayed',
        null, $workflow->title, $workflowid, $search);
    ob_start();
    $table->out(PAGESIZE, false);
    $out = ob_get_contents();
    ob_end_clean();
    $hiddenfieldssearch[] = ['name' => 'delayed', 'value' => $delayed];
    $tablecoursesamount = count($coursesdelayed);
} else if ($processes) { // Display courses table with courses in a process or in state process error for this workflow.
    $coursesinprocess = $DB->get_fieldset('tool_lifecycle_process', 'courseid', ['workflowid' => $workflow->id]);
    $coursesprocesserrors = $DB->get_fieldset('tool_lifecycle_proc_error', 'courseid', ['workflowid' => $workflow->id]);
    $coursesprocess = array_merge($coursesinprocess, $coursesprocesserrors);
    $table = new process_courses_table($coursesprocess, $workflow->title, $workflow->id, $search);
    ob_start();
    $table->out(PAGESIZE, false);
    $out = ob_get_contents();
    ob_end_clean();
    $hiddenfieldssearch[] = ['name' => 'processes', 'value' => $processes];
    $tablecoursesamount = count($coursesprocess);
} else if ($used) { // Display courses triggered by this workflow but involved in other processes already.
    if ($courseids = $amounts['all']->used ?? null) {
        $table = new triggered_courses_table( $courseids, 'used',
            null, $workflow->title, $workflowid, $search);
        ob_start();
        $table->out(PAGESIZE, false);
        $out = ob_get_contents();
        ob_end_clean();
        $hiddenfieldssearch[] = ['name' => 'used', 'value' => $used];
        $tablecoursesamount = count($courseids);
    }
}
// Search box for courses list.
$searchhtml = '';
if ($tablecoursesamount > PAGESIZE ) {
    $searchhtml = $renderer->render_from_template('tool_lifecycle/search_input', [
        'action' => (new moodle_url(urls::WORKFLOW_DETAILS))->out(false),
        'uniqid' => 'tool_lifecycle-search-courses',
        'inputname' => 'search',
        'extraclasses' => 'ml-3 mt-3',
        'inform' => false,
        'searchstring' => get_string('searchcourses', 'tool_lifecycle'),
        'query' => $search,
        'hiddenfields' => $hiddenfieldssearch,
    ]);
}
$disableworkflowlink = "";
$abortdisableworkflowlink = "";
$workflowprocesseslink = "";
if ($isactive) {
    // Disable workflow link.
    $alt = get_string('disableworkflow', 'tool_lifecycle');
    $icon = 't/disable';
    $url = new \moodle_url(urls::DEACTIVATED_WORKFLOWS,
        ['workflowid' => $workflow->id, 'action' => action::WORKFLOW_DISABLE, 'sesskey' => sesskey()]);
    $confirmaction = new \confirm_action(get_string('disableworkflow_confirm', 'tool_lifecycle'));
    $disableworkflowlink = $OUTPUT->action_icon($url,
        new \pix_icon($icon, $alt, 'tool_lifecycle', ['title' => $alt, 'class' => 'text-white']),
        $confirmaction,
        ['title' => $alt]
    );
    $disableworkflowlink = "<br>".$disableworkflowlink;
    // Abort workflow link.
    $alt = get_string('abortdisableworkflow', 'tool_lifecycle');
    $icon = 't/stop';
    $url = new \moodle_url(urls::DEACTIVATED_WORKFLOWS,
        ['workflowid' => $workflow->id, 'action' => action::WORKFLOW_ABORTDISABLE, 'sesskey' => sesskey()]);
    $confirmaction = new \confirm_action(get_string('abortdisableworkflow_confirm', 'tool_lifecycle'));
    $abortdisableworkflowlink = $OUTPUT->action_icon($url,
        new \pix_icon($icon, $alt, 'moodle', ['title' => $alt, 'class' => 'text-white']),
        $confirmaction,
        ['title' => $alt]
    );
    $abortdisableworkflowlink = "<br>".$abortdisableworkflowlink;
    // Workflow processes and process errors link.
    $ldata = new \stdClass();
    $ldata->alt = get_string('workflow_processesanderrors', 'tool_lifecycle');
    $ldata->url = new moodle_url($popuplink, ['processes' => $workflowid, 'showdetails' => $showdetails]);
    $ldata->processes = process_manager::count_processes_by_workflow($workflow->id) +
        process_manager::count_process_errors_by_workflow($workflow->id);
    $workflowprocesseslink = $OUTPUT->render_from_template('tool_lifecycle/overview_processeslink', $ldata);;
    $workflowprocesseslink = "<br>".$workflowprocesseslink;
}

$data = [
    'editsettingslink' => (new moodle_url(urls::EDIT_WORKFLOW, ['wf' => $workflow->id]))->out(false),
    'title' => $workflow->title,
    'rollbackdelay' => format_time($workflow->rollbackdelay),
    'finishdelay' => format_time($workflow->finishdelay),
    'delayglobally' => $workflow->delayforallworkflows,
    'trigger' => $displaytriggers,
    'timetrigger' => $displaytimetriggers,
    'counttriggers' => count($displaytriggers),
    'counttimetriggers' => count($displaytimetriggers),
    'showcoursecounts' => $showdetails,
    'steps' => $displaysteps,
    'popuplink' => $popuplink,
    'nosteplink' => $nosteplink,
    'table' => $out,
    'workflowid' => $workflowid,
    'search' => $searchhtml,
    'classdetails' => $classdetails,
    'includedelayedcourses' => $workflow->includedelayedcourses,
    'includesitecourse' => $workflow->includesitecourse,
    'showdetails' => $showdetails,
    'showdetailslink' => $showdetailslink,
    'showdetailsicon' => $showdetails == 0,
    'isactive' => $isactive || $isdeactivated,
    'nextrun' => $nextrunout,
    'lastrun' => userdate($lastrun, get_string('strftimedatetimeshort', 'langconfig')),
    'nomanualtriggerinvolved' => $nomanualtriggerinvolved,
    'disableworkflowlink' => $disableworkflowlink,
    'abortdisableworkflowlink' => $abortdisableworkflowlink,
    'workflowprocesseslink'  => $workflowprocesseslink,
];
if ($showdetails) {
    // The triggers total box.
    $data['displaytotaltriggered'] = $displaytotaltriggered;
    $triggered = count($amounts['all']->coursestriggered) ?? 0;
    $triggeredhtml = $triggered > 0 ? html_writer::span($triggered, 'text-success font-weight-bold') : 0;
    $data['coursestriggered'] = $triggeredhtml;
    $data['coursestriggeredcount'] = $triggered;
    // Count delayed total, displayed in mustache only if there are any.
    $delayed = count($amounts['all']->delayedcourses);  // Matters only if delayed courses are not included in workflow.
    $delayedlink = new moodle_url($popuplink, ['delayed' => $workflowid]);
    $delayedhtml = $delayed > 0 ? html_writer::link($delayedlink, $delayed,
        ['class' => 'btn btn-outline-secondary mt-1']) : 0;
    $data['coursesdelayed'] = $delayedhtml;
    // Count in other processes used courses total, displayed in mustache only if there are any.
    $used = count($amounts['all']->used) ?? 0;
    $usedlink = new moodle_url($popuplink, ['used' => "1"]);
    $usedhtml = $used > 0 ? html_writer::link($usedlink, $used,
        ['class' => 'btn btn-outline-secondary mt-1']) : 0;
    $data['coursesused'] = $usedhtml;
}

$addtriggerselect = "";
$addstepselect = "";
$activate = "";
$newworkflow = false;
// Box to add triggers or steps to workflow by use of select fields.
if (workflow_manager::is_editable($workflow->id)) {
    // Add trigger select field.
    $triggertypes = trigger_manager::get_chooseable_trigger_types();
    $selectabletriggers = [];
    foreach ($triggertypes as $triggertype => $triggername) {
        if ($triggers) {
            foreach ($triggers as $workflowtrigger) {
                if ($triggertype == $workflowtrigger->subpluginname) {
                    continue 2;
                }
            }
        } else {
            // After workflow creation only provide course selection (and manual) triggers for the adding a trigger selection field.
            $lib = lib_manager::get_trigger_lib($triggertype);
            if (!$lib->is_manual_trigger()) {
                if ($lib->check_course(null, null) == trigger_response::triggertime()) {
                    continue;
                }
            }
            $newworkflow = true;
        }
        $selectabletriggers[$triggertype] = $triggername;
    }
    $addtriggerselect = $OUTPUT->single_select(new \moodle_url(urls::EDIT_ELEMENT,
        ['type' => settings_type::TRIGGER, 'wf' => $workflow->id]),
        'subplugin', $selectabletriggers, '', ['' => get_string('add_new_trigger_instance', 'tool_lifecycle')],
        null, ['id' => 'tool_lifecycle-choose-trigger']);
    // Add step selection field.
    if (!$newworkflow) { // At first select a course selection trigger, than you can select the first step.
        if ($steptypes = step_manager::get_step_types()) {
            $addstepselect = $OUTPUT->single_select(new \moodle_url(urls::EDIT_ELEMENT,
                ['type' => settings_type::STEP, 'wf' => $workflow->id]),
                'subplugin', $steptypes, '', ['' => get_string('add_new_step_instance', 'tool_lifecycle')],
                null, ['id' => 'tool_lifecycle-choose-step']);
        }
        if ($id == 'workflowdrafts') {
            // At least one trigger and one step is necessary to activate the draft workflow.
            if (workflow_manager::is_valid($workflow->id)) {
                $activate = $OUTPUT->single_button(new \moodle_url(urls::ACTIVE_WORKFLOWS,
                    [
                        'action' => action::WORKFLOW_ACTIVATE,
                        'sesskey' => sesskey(),
                        'workflowid' => $workflow->id,
                        'backtooverview' => '1',
                    ]),
                    get_string('activateworkflow', 'tool_lifecycle'));
            } else {
                $activate = get_string('invalid_workflow', 'tool_lifecycle').
                    $OUTPUT->pix_icon('i/circleinfo', get_string('invalid_workflow_details', 'tool_lifecycle'));
            }
        }
    }
    $data['addtriggerselect'] = $addtriggerselect;
    $data['addstepselect'] = $addstepselect;
    $data['activate'] = $activate;
    $data['newworkflow'] = $newworkflow;
} else if ($isdeactivated) {
    $activate = $OUTPUT->single_button(new \moodle_url(urls::ACTIVE_WORKFLOWS,
        [
            'action' => action::WORKFLOW_ACTIVATE,
            'sesskey' => sesskey(),
            'workflowid' => $workflow->id,
            'backtooverview' => '1',
        ]),
        get_string('activateworkflow', 'tool_lifecycle'));
    $data['activatebutton'] = $activate;
}

echo $OUTPUT->render_from_template('tool_lifecycle/workflowoverview', $data);

echo $renderer->footer();
