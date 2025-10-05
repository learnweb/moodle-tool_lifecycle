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
 * Displays the tables of active workflow definitions.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\table\active_automatic_workflows_table;
use tool_lifecycle\local\table\active_manual_workflows_table;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_admin();

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::ACTIVE_WORKFLOWS));
$PAGE->set_context($syscontext);

$action = optional_param('action', null, PARAM_TEXT);
if ($action) {
    $wfid = required_param('workflowid', PARAM_INT);
    workflow_manager::handle_action($action, $wfid);
    if (optional_param('backtooverview', 0, PARAM_BOOL)) {
        redirect(new \moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $wfid]));
    } else {
        redirect($PAGE->url);
    }
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('active_workflows_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$id = optional_param('id', 'settings', PARAM_TEXT);
$renderer->tabs($tabrow, $id);

echo $renderer->render_from_template('tool_lifecycle/search_input', [
    'action' => (new moodle_url(urls::ACTIVE_PROCESSES))->out(false),
    'uniqid' => 'tool_lifecycle-search-courses',
    'inputname' => 'search',
    'extraclasses' => 'mb-3',
    'inform' => false,
    'searchstring' => get_string('searchcourses', 'tool_lifecycle'),
]);

echo $OUTPUT->heading(get_string('active_automatic_workflows_heading', 'tool_lifecycle'));

$table = new active_automatic_workflows_table('tool_lifecycle_active_automatic_workflows');
echo $OUTPUT->box_start("lifecycle-enable-overflow lifecycle-table");
$table->out(10, false);
echo $OUTPUT->box_end();

echo $OUTPUT->heading(get_string('active_manual_workflows_heading', 'tool_lifecycle'));

$table = new active_manual_workflows_table('tool_lifecycle_manual_workflows');
echo $OUTPUT->box_start("lifecycle-enable-overflow lifecycle-table");
$table->out(10, false);
echo $OUTPUT->box_end();

echo $renderer->footer();
