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
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;
use tool_lifecycle\urls;

global $OUTPUT, $PAGE, $DB;

$workflowid = required_param('wf', PARAM_INT);

$workflow = \tool_lifecycle\local\manager\workflow_manager::get_workflow($workflowid);
\tool_lifecycle\permission_and_navigation::setup_workflow($workflow);

$iseditable = workflow_manager::is_editable($workflow->id);

$stepid = optional_param('step', null, PARAM_INT);

$params = ['wf' => $workflow->id];
$nosteplink = new moodle_url(urls::WORKFLOW_DETAILS, $params);

if ($stepid) {
    $params['step'] = $stepid;
}
$PAGE->set_url(new \moodle_url(urls::WORKFLOW_DETAILS, $params));
$PAGE->set_title($workflow->title);
$PAGE->set_heading($workflow->title);

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

$renderer = $PAGE->get_renderer('tool_lifecycle');

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
    // On moodle instances with many courses the following call can be fatal, because each trigger
    // check function will be called for every single course of the instance to determine how many
    // courses will be triggered by the workflow/the specific trigger. This count is only being
    // used to show the admin how many courses will be triggered, it has no functional aspect.
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
        $trigger->automatic = $amounts[$trigger->sortindex]->automatic;
        $displaytotaltriggered &= $trigger->automatic;
        if ($trigger->automatic) {
            $trigger->triggeredcourses = $amounts[$trigger->sortindex]->triggered;
            $trigger->excludedcourses = $amounts[$trigger->sortindex]->excluded;
        }
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

$url = new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflowid]);

$out = null;
if ($stepid) {
    $step = step_manager::get_step_instance($stepid);
    $table = new \tool_lifecycle\local\table\courses_in_step_table($step,
        optional_param('courseid', null, PARAM_INT));
    ob_start();
    $table->out(20, false);
    $out = ob_get_contents();
    ob_end_clean();
}

$data = [
    'triggerhelp' => $OUTPUT->help_icon('overview:trigger', 'tool_lifecycle', null),
    'editsettingslink' => (new moodle_url(urls::EDIT_WORKFLOW, ['wf' => $workflow->id]))->out(false),
    'title' => $workflow->title,
    'displaytitle' => $workflow->displaytitle,
    'rollbackdelay' => format_time($workflow->rollbackdelay),
    'finishdelay' => format_time($workflow->finishdelay),
    'delayglobally' => $workflow->delayforallworkflows,
    'trigger' => $displaytriggers,
    'showcoursecounts' => $showcoursecounts,
    'steps' => $displaysteps,
    'listofcourses' => $arrayofcourses,
    'nosteplink' => $nosteplink,
    'table' => $out,
];
if ($showcoursecounts) {
    $data['automatic'] = $displaytotaltriggered;
    $data['coursestriggered'] = $amounts['all']->triggered;
    $data['coursesexcluded'] = $amounts['all']->excluded;
    $data['coursesetsize'] = $amounts['all']->coursesetsize;
}

echo $renderer->header();

if (workflow_manager::is_editable($workflow->id)) {
    $addinstance = '';
    $triggertypes = trigger_manager::get_chooseable_trigger_types();
    $workflowtriggers = trigger_manager::get_triggers_for_workflow($workflow->id);
    $selectabletriggers = [];
    foreach ($triggertypes as $triggertype => $triggername) {
        foreach ($workflowtriggers as $workflowtrigger) {
            if ($triggertype == $workflowtrigger->subpluginname) {
                continue 2;
            }
        }
        $selectabletriggers[$triggertype] = $triggername;
    }
    $icondata = (new help_icon('overview:add_trigger', 'tool_lifecycle'))->export_for_template($OUTPUT);
    $addinstance .= $OUTPUT->render_from_template('tool_lifecycle/warn_icon', $icondata);

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
    $data['addinstance'] = $addinstance;
}

echo $OUTPUT->render_from_template('tool_lifecycle/workflowoverview', $data);

echo $renderer->footer();
