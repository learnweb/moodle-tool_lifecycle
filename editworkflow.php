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
 * Displays form for creating a new or editing an existing workflow.
 *
 * @package tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\action;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\form\form_workflow_instance;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\table\workflow_definition_table;
use tool_lifecycle\urls;
use tool_lifecycle\tabs;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);

$workflowid = optional_param('wf', null, PARAM_INT);
if ($workflowid) {
    $workflow = workflow_manager::get_workflow($workflowid);
    $title = get_string('editworkflow', 'tool_lifecycle');
    $PAGE->set_url(new \moodle_url(urls::EDIT_WORKFLOW), ['wf' => $workflowid]);
} else {
    $title = get_string('add_workflow', 'tool_lifecycle');
    $PAGE->set_url(new \moodle_url(urls::EDIT_WORKFLOW));
    $workflow = null;
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".$title;

$form = new form_workflow_instance($PAGE->url, $workflow);
if ($form->is_cancelled()) {
    if ($workflowid) {
        // Aborted updating workflow, redirect back to workflow details.
        redirect(new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflow->id]));
    } else {
        // Aborted creating new workflow, redirect back to workflow drafts.
        redirect(new moodle_url(urls::WORKFLOW_DRAFTS));
    }
}
if ($data = $form->get_data()) {
    if ($data->id) {
        $workflow = workflow_manager::get_workflow($data->id);
        $workflow->title = $data->title;
        $workflow->displaytitle = $data->displaytitle;
        $workflow->rollbackdelay = $data->rollbackdelay;
        $workflow->finishdelay = $data->finishdelay;
        $workflow->delayforallworkflows = property_exists($data, 'delayforallworkflows') ? $data->delayforallworkflows : 0;
        $newworkflow = false;
    } else {
        $workflow = workflow::from_record($data);
        $newworkflow = true;
    }
    workflow_manager::insert_or_update($workflow);

    // New Workflow created, redirect to details page.
    redirect(new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflow->id]));
}
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$id = optional_param('id', 'settings', PARAM_TEXT);
$renderer->tabs($tabrow, $id);

$form->display();

echo $renderer->footer();
