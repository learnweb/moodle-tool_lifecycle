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
 * @copyright  2021 Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use tool_lifecycle\action;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\table\interaction_attention_table;
use tool_lifecycle\settings_type;
use tool_lifecycle\urls;

global $OUTPUT, $PAGE, $DB;

$workflowid = required_param('wf', PARAM_INT);

$workflow = \tool_lifecycle\local\manager\workflow_manager::get_workflow($workflowid);
\tool_lifecycle\permission_and_navigation::setup_workflow($workflow);

$iseditable = workflow_manager::is_editable($workflow->id);

$PAGE->set_url(new \moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflow->id]));
$PAGE->set_title($workflow->title);
$PAGE->set_heading($workflow->title);

$stepid = optional_param('step', null, PARAM_INT);
$triggerid = optional_param('trigger', null, PARAM_INT);
$action = optional_param('action', null, PARAM_TEXT);

if ($action) {
    step_manager::handle_action($action, $stepid, $workflow->id);
    trigger_manager::handle_action($action, $triggerid, $workflow->id);
    redirect($PAGE->url);
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

$steps = \tool_lifecycle\local\manager\step_manager::get_step_instances($workflow->id);
$triggers = \tool_lifecycle\local\manager\trigger_manager::get_triggers_for_workflow($workflow->id);

$str = [
    'edit' => get_string('edit'),
    'delete' => get_string('delete'),
    'move_up' => get_string('move_up', 'tool_lifecycle'),
    'move_down' => get_string('move_down', 'tool_lifecycle')
];

foreach ($triggers as $trigger) {
    // The array from the DB Function uses ids as keys.
    // Mustache cannot handle arrays which have other keys therefore a new array is build.
    // FUTURE: Nice to have Icon for each subplugin.
    // FUTURE: Nice to have How many courses will be caught by the trigger?

    $actionmenu = new action_menu([
        new action_menu_link_secondary(
            new moodle_url(urls::EDIT_ELEMENT, ['type' => settings_type::TRIGGER, 'elementid' => $trigger->id]),
            new pix_icon('i/edit', $str['edit']), $str['edit'])
    ]);
    if ($iseditable) {
        $actionmenu->add(new action_menu_link_secondary(
            new moodle_url($PAGE->url,
                ['action' => action::TRIGGER_INSTANCE_DELETE, 'sesskey' => sesskey(), 'trigger' => $trigger->id]),
            new pix_icon('t/delete', $str['delete']), $str['delete'])
        );
    }
    $trigger->actionmenu = $OUTPUT->render($actionmenu);
}

foreach ($steps as $step) {
    $ncourses = $DB->count_records('tool_lifecycle_process',
        array('stepindex' => $step->sortindex, 'workflowid' => $workflowid));
    $step->numberofcourses = $ncourses;
    $actionmenu = new action_menu([
        new action_menu_link_secondary(
            new moodle_url(urls::EDIT_ELEMENT, ['type' => settings_type::STEP, 'elementid' => $step->id]),
            new pix_icon('i/edit', $str['edit']), $str['edit'])
    ]);
    if ($iseditable) {
        $actionmenu->add(new action_menu_link_secondary(
            new moodle_url($PAGE->url,
                ['action' => action::STEP_INSTANCE_DELETE, 'sesskey' => sesskey(), 'step' => $step->id]),
            new pix_icon('t/delete', $str['delete']), $str['delete'])
        );
        if ($step->sortindex > 1) {
            $actionmenu->add(new action_menu_link_secondary(
                new moodle_url($PAGE->url,
                    ['action' => action::UP_STEP, 'sesskey' => sesskey(), 'step' => $step->id]),
                new pix_icon('t/up', $str['move_up']), $str['move_up'])
            );
        }
        if ($step->sortindex < count($steps)) {
            $actionmenu->add(new action_menu_link_secondary(
                    new moodle_url($PAGE->url,
                        ['action' => action::DOWN_STEP, 'sesskey' => sesskey(), 'step' => $step->id]),
                    new pix_icon('t/down', $str['move_down']), $str['move_down'])
            );
        }
    }
    $step->actionmenu = $OUTPUT->render($actionmenu);
}

$arrayofcourses = array();

$url = new moodle_url(urls::WORKFLOW_DETAILS, array('wf' => $workflowid));

$data = [
    'trigger' => array_values($triggers),
    'steps' => array_values($steps),
    'listofcourses' => $arrayofcourses,
    'steplink' => $url
];

echo $renderer->header();

if (workflow_manager::is_editable($workflow->id)) {
    $triggers = trigger_manager::get_chooseable_trigger_types();
    echo $OUTPUT->single_select(new \moodle_url(urls::EDIT_ELEMENT,
        array('type' => settings_type::TRIGGER, 'wf' => $workflow->id)),
        'subplugin', $triggers, '', array('' => get_string('add_new_trigger_instance', 'tool_lifecycle')));

    $steps = step_manager::get_step_types();
    echo '<span class="ml-1"></span>';
    echo $OUTPUT->single_select(new \moodle_url(urls::EDIT_ELEMENT,
        array('type' => settings_type::STEP, 'wf' => $workflow->id)),
        'subplugin', $steps, '', array('' => get_string('add_new_step_instance', 'tool_lifecycle')));
}

echo $OUTPUT->render_from_template('tool_lifecycle/workflowoverview', $data);
if ($stepid) {

    $listofcourses = $DB->get_records_sql("SELECT p.id as processid, c.id as courseid, c.fullname as coursefullname, " .
        "c.shortname as courseshortname, s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname " .
        "FROM {tool_lifecycle_process} p join " .
        "{course} c on p.courseid = c.id join " .
        "{tool_lifecycle_step} s ".
        "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex " .
        "WHERE p.stepindex = :stepindex AND p.workflowid = :wfid;", array('stepindex' => $stepid, 'wfid' => $workflowid));
    $listofids = array();
    foreach ($listofcourses as $key => $value) {
        $listofids = $value->courseid;
        $objectvar = (object) $listofcourses[$key];
        array_push($arrayofcourses, $objectvar);
    }
    asort($arrayofcourses);
    $table = new interaction_attention_table('tool_lifecycle_interaction', $listofids);
    $table->out(50, false);
}
echo $renderer->footer();
