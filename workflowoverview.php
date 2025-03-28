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
 * @copyright  2021 Nina Herrmann and Justus Dieckmann, WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

use tool_lifecycle\action;
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;
use tool_lifecycle\urls;
use tool_lifecycle\tabs;

global $OUTPUT, $PAGE, $DB;

require_login();

$workflowid = required_param('wf', PARAM_INT);
$stepid = optional_param('step', null, PARAM_INT);
$triggerid = optional_param('trigger', null, PARAM_INT);
$delayed = optional_param('delayed', null, PARAM_INT);
$excluded = optional_param('excluded', null, PARAM_INT);

$workflow = \tool_lifecycle\local\manager\workflow_manager::get_workflow($workflowid);
$iseditable = workflow_manager::is_editable($workflow->id);
$isactive = workflow_manager::is_active($workflow->id);
$isdeactivated = workflow_manager::is_deactivated($workflow->id);

$params = ['wf' => $workflow->id];
if ($stepid) {
    $params['step'] = $stepid;
} else if ($triggerid) {
    $params['trigger'] = $triggerid;
} else if ($delayed) {
    $params['delayed'] = $delayed;
} else if ($excluded) {
    $params['excluded'] = $excluded;
}

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::WORKFLOW_DETAILS, $params));
$PAGE->set_context($syscontext);
$PAGE->set_title($workflow->title);

$popuplink = new moodle_url(urls::WORKFLOW_DETAILS, $params);

$action = optional_param('action', null, PARAM_TEXT);
if ($action) {
    step_manager::handle_action($action, optional_param('actionstep', null, PARAM_INT), $workflow->id);
    trigger_manager::handle_action($action, optional_param('actiontrigger', null, PARAM_INT), $workflow->id);
    $processid = optional_param('processid', null, PARAM_INT);
    if ($processid) {
        $process = \tool_lifecycle\local\manager\process_manager::get_process_by_id($processid);
        if ($action === 'rollback') {
            \tool_lifecycle\local\manager\process_manager::rollback_process($process);
            delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, true, $workflow);
        } else if ($action === 'proceed') {
            \tool_lifecycle\local\manager\process_manager::proceed_process($process);
            delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, false, $workflow);
        } else {
            throw new coding_exception('processid was specified but action was neither "rollback" nor "proceed"!');
        }
    }
    redirect($PAGE->url);
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".
    get_string('workflowoverview', 'tool_lifecycle').": ". $workflow->title;
echo $renderer->header($heading);
$activelink = false;
$deactivatedlink = false;
$draftlink = false;
if ($isactive) {  // Active workflow.
    $id = 'activeworkflows';
    $activelink = true;
} else {
    if ($isdeactivated) { // Deactivated workflow.
        $id = 'deactivatedworkflows';
        $deactivatedlink = true;
    } else { // Draft.
        $id = 'workflowdrafts';
        $draftlink = true;
    }
}
$tabrow = tabs::get_tabrow($activelink, $deactivatedlink, $draftlink);
$renderer->tabs($tabrow, $id);

$steps = \tool_lifecycle\local\manager\step_manager::get_step_instances($workflow->id);
$triggers = \tool_lifecycle\local\manager\trigger_manager::get_triggers_for_workflow($workflow->id);

$str = [
    'edit' => get_string('edit'),
    'delete' => get_string('delete'),
    'move_up' => get_string('move_up', 'tool_lifecycle'),
    'move_down' => get_string('move_down', 'tool_lifecycle'),
];

$showcoursecounts = get_config('tool_lifecycle', 'showcoursecounts');
if ($showcoursecounts) {
    /*
        On moodle instances with many courses the following call can be fatal, because each trigger
        check function will be called for every single course of the instance to determine how many
        courses will be triggered by the workflow/the specific trigger. This count is only being
        used to show the admin how many courses will be triggered, it has no functional aspect.
    */
    $amounts = (new \tool_lifecycle\processor())->get_count_of_courses_to_trigger_for_workflow($workflow->id);
    $displaytotaltriggered = !empty($triggers);
}

$displaytriggers = [];
$displaysteps = [];

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
    if ($showcoursecounts) {
        if ($trigger->automatic = $amounts[$trigger->sortindex]->automatic) {
            $sqlresult = trigger_manager::get_trigger_sqlresult($trigger);
            if ($sqlresult == "false") {
                $trigger->classfires = "border-danger";
            } else {
                $sumtrigger = $amounts[$trigger->sortindex]->triggered - $amounts[$trigger->sortindex]->excluded -
                    $amounts[$trigger->sortindex]->delayed;
                if ($sumtrigger > 0) {
                    $trigger->classfires = "border-success";
                } else if ($sumtrigger == 0) {
                    $trigger->classfires = "border-secondary";
                } else {
                    $trigger->classfires = "border-danger";
                }
            }
            $trigger->excludedcourses = $amounts[$trigger->sortindex]->excluded;
            $trigger->triggeredcourses = $amounts[$trigger->sortindex]->triggered;
            $trigger->delayedcourses = $amounts[$trigger->sortindex]->delayed;
        }
        $displaytotaltriggered &= $trigger->automatic;
    }
    $displaytriggers[] = $trigger;
}

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

$arrayofcourses = [];

$out = null;
if ($stepid) {
    $step = step_manager::get_step_instance($stepid);
    $table = new \tool_lifecycle\local\table\courses_in_step_table($step,
        optional_param('courseid', null, PARAM_INT));
    ob_start();
    $table->out(20, false);
    $out = ob_get_contents();
    ob_end_clean();
} else if ($triggerid) {
    $trigger = trigger_manager::get_instance($triggerid);
    if ($courseids = (new \tool_lifecycle\processor())->get_courses_to_trigger_for_trigger($trigger, $workflowid)) {
        $table = new \tool_lifecycle\local\table\triggered_courses_table($trigger, 'triggered', $courseids);
        ob_start();
        $table->out(20, false);
        $out = ob_get_contents();
        ob_end_clean();
    }
} else if ($delayed) {
    $trigger = trigger_manager::get_instance($delayed);
    if ($courseids = (new \tool_lifecycle\processor())->get_courses_delayed_for_trigger($trigger, $workflowid)) {
        $table = new \tool_lifecycle\local\table\triggered_courses_table($trigger, 'delayed', $courseids);
        ob_start();
        $table->out(20, false);
        $out = ob_get_contents();
        ob_end_clean();
    }
} else if ($excluded) {
    $trigger = trigger_manager::get_instance($excluded);
    if ($courseids = (new \tool_lifecycle\processor())->get_courses_to_exclude_for_trigger($trigger, $workflowid)) {
        $table = new \tool_lifecycle\local\table\triggered_courses_table($trigger, 'exclude', $courseids);
        ob_start();
        $table->out(20, false);
        $out = ob_get_contents();
        ob_end_clean();
    }
}

$nosteplink = new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflowid]);

$data = [
    'rollbackhelp' => $OUTPUT->help_icon('details:rollbackdelay', 'tool_lifecycle', null),
    'finishhelp' => $OUTPUT->help_icon('details:finishdelay', 'tool_lifecycle', null),
    'triggerhelp' => $OUTPUT->help_icon('overview:trigger', 'tool_lifecycle', null),
    'manualtriggerenvolvedhelp' => $OUTPUT->help_icon('manualtriggerenvolved', 'tool_lifecycle', null),
    'editsettingslink' => (new moodle_url(urls::EDIT_WORKFLOW, ['wf' => $workflow->id]))->out(false),
    'title' => $workflow->title,
    'rollbackdelay' => format_time($workflow->rollbackdelay),
    'finishdelay' => format_time($workflow->finishdelay),
    'delayglobally' => $workflow->delayforallworkflows,
    'trigger' => $displaytriggers,
    'counttriggers' => count($displaytriggers),
    'showcoursecounts' => $showcoursecounts,
    'steps' => $displaysteps,
    'listofcourses' => $arrayofcourses,
    'popuplink' => $popuplink,
    'nosteplink' => $nosteplink,
    'table' => $out,
];
if ($showcoursecounts) {
    $data['automatic'] = $displaytotaltriggered;
    $data['coursestriggered'] = $amounts['all']->triggered;
    $data['coursesexcluded'] = $amounts['all']->excluded;
    $data['coursesdelayed'] = $amounts['all']->delayed;
    $data['coursesetsize'] = $amounts['all']->coursesetsize;
}

if (workflow_manager::is_editable($workflow->id)) {
    $addinstance = '';
    $triggertypes = trigger_manager::get_chooseable_trigger_types();
    $selectabletriggers = [];
    foreach ($triggertypes as $triggertype => $triggername) {
        foreach ($triggers as $workflowtrigger) {
            if ($triggertype == $workflowtrigger->subpluginname) {
                continue 2;
            }
        }
        $selectabletriggers[$triggertype] = $triggername;
    }

    $addinstance .= $OUTPUT->single_select(new \moodle_url(urls::EDIT_ELEMENT,
        ['type' => settings_type::TRIGGER, 'wf' => $workflow->id]),
        'subplugin', $selectabletriggers, '', ['' => get_string('add_new_trigger_instance', 'tool_lifecycle')],
        null, ['id' => 'tool_lifecycle-choose-trigger']);

    $steps = step_manager::get_step_types();
    $addinstance .= '<span class="ml-1"></span>';
    $addinstance .= $OUTPUT->single_select(new \moodle_url(urls::EDIT_ELEMENT,
        ['type' => settings_type::STEP, 'wf' => $workflow->id]),
        'subplugin', $steps, '', ['' => get_string('add_new_step_instance', 'tool_lifecycle')],
        null, ['id' => 'tool_lifecycle-choose-step']);

    if ($id == 'workflowdrafts') {
        $addinstance .= '<span class="ml-2"></span>';
        if (workflow_manager::is_valid($workflow->id)) {
            $addinstance .= $OUTPUT->single_button(new \moodle_url(urls::ACTIVE_WORKFLOWS,
                ['action' => action::WORKFLOW_ACTIVATE,
                    'sesskey' => sesskey(),
                    'workflowid' => $workflow->id, ]),
                get_string('activateworkflow', 'tool_lifecycle'));
        } else {
            $addinstance .= $OUTPUT->pix_icon('i/circleinfo', get_string('invalid_workflow_details', 'tool_lifecycle')) .
                get_string('invalid_workflow', 'tool_lifecycle');
        }
    }

    $data['addinstance'] = $addinstance;
}

echo $OUTPUT->render_from_template('tool_lifecycle/workflowoverview', $data);

echo $renderer->footer();
