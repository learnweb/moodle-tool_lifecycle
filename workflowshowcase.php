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
 * Displays a list of all workflows.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\action;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_admin();

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::SHOWCASE));
$PAGE->set_context($syscontext);

$action = optional_param('action', null, PARAM_TEXT);
if ($action) {
    $wfid = required_param('workflowid', PARAM_INT);
    workflow_manager::handle_action($action, $wfid);
    redirect($PAGE->url);
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('workflow_showcase_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$renderer->tabs($tabrow, 'showcase');

$PAGE->requires->js_call_amd('tool_lifecycle/filtershowcase', 'init');

echo $OUTPUT->box_start();

$records = $DB->get_records_sql(
    'SELECT * FROM {tool_lifecycle_workflow} ORDER BY title ASC');

$data = new stdClass();
$data->workflows = [];
$usedtriggersubplugins = [];
$usedstepsubplugins = [];
foreach ($records as $record) {
    $workflow = new stdClass();
    $cardclasses = [];
    if ($record->timeactive) {
        $headerclass = "bg-primary text-white";
        $headercontent = get_string('workflow_active', 'tool_lifecycle');
    } else if ($record->timedeactive) {
        $headerclass = "bg-secondary";
        $headercontent = get_string('deactivated', 'tool_lifecycle');
    } else {
        $headerclass = "bg-light";
        $headercontent = get_string('draft', 'tool_lifecycle');
    }
    $triggerstr = "";
    $triggers = trigger_manager::get_triggers_for_workflow($record->id);
    if ($triggers) {
        foreach ($triggers as $key => $trigger) {
            $triggertitle = "[".$trigger->subpluginname."] ".$trigger->instancename;
            $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
            $triggericon = $lib->get_icon();
            $triggerstr .= $OUTPUT->pix_icon($triggericon, $triggertitle);
            $usedtriggersubplugins["lifecycle-".$trigger->subpluginname] = $trigger->subpluginname;
            $cardclasses[] = 'lifecycle-'.$trigger->subpluginname;
        }
    } else {
        $triggerstr = "--";
    }

    $stepsstr = '';
    $steps = step_manager::get_step_instances($record->id);
    if ($steps) {
        foreach ($steps as $key => $step) {
            $steptitle = "[".$step->subpluginname."] ".$step->instancename;
            $lib = lib_manager::get_step_lib($step->subpluginname);
            $stepicon = $lib->get_icon();
            $stepsstr .= $OUTPUT->pix_icon($stepicon, $steptitle);
            $usedstepsubplugins["lifecycle-".$step->subpluginname] = $step->subpluginname;
            $cardclasses[] = "lifecycle-".$step->subpluginname;
        }
    } else {
        $stepsstr = "--";
    }

    $downloadlink = new \moodle_url($PAGE->url,
        ['action' => action::WORKFLOW_BACKUP,
            'workflowid' => $record->id,
            'sesskey' => sesskey()]);
    $workflow = [
        'id' => $record->id,
        'headerclass' => $headerclass,
        'headercontent' => $headercontent,
        'body' => [
            'title' => $record->title,
            'text' => $record->description,
        ],
        'triggers' => $triggerstr,
        'steps' => $stepsstr,
        'downloadlink' => $downloadlink,
        'cardclasses' => implode(" ", $cardclasses),
    ];
    $data->workflows[] = $workflow;
}

$triggersubplugins = array_unique($usedtriggersubplugins);
asort($triggersubplugins);
$stepsubplugins = array_unique($usedstepsubplugins);
asort($stepsubplugins);
$headerdata = new stdClass();
$headerdata->uploadlink = new \moodle_url($CFG->wwwroot . '/admin/tool/lifecycle/uploadworkflow.php', ['sesskey' => sesskey()]);
$headerdata->filtertriggerselect = \html_writer::select($triggersubplugins, 'filtertrigger_select', '',
    get_string('choosedots'), ['class' => 'd-inline-block showcasefilterselect']);
$headerdata->filterstepselect = \html_writer::select($stepsubplugins, 'filterstep_select', '',
    get_string('choosedots'), ['class' => 'd-inline-block showcasefilterselect']);
echo $OUTPUT->render_from_template('tool_lifecycle/workflowshowcase_header', $headerdata);

echo $OUTPUT->render_from_template('tool_lifecycle/workflowshowcase_card', $data);

echo $OUTPUT->box_end();

echo $renderer->footer();
