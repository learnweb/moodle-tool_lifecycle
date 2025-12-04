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
 * Life Cycle Admin Approve Step. Page for specific step.
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use lifecyclestep_adminapprove\course_filter_form;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login(null, false);

require_capability('moodle/site:config', context_system::instance());

$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$ids = optional_param_array('c', [], PARAM_INT);
$stepid = required_param('stepid', PARAM_INT);

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url("/admin/tool/lifecycle/step/adminapprove/approvestep.php?stepid=$stepid"));
$PAGE->set_context($syscontext);
$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');


$step = step_manager::get_step_instance($stepid);
if (!$step) {
    throw new moodle_exception('Stepid does not correspond to any step.');
}
if ($step->subpluginname !== 'adminapprove') {
    throw new moodle_exception('The given step is not a Admin Approve Step.');
}

/**
 * Constant to roll back selected.
 */
const ROLLBACK = 'rollback';
/**
 * Constant to roll back all courses.
 */
const ROLLBACK_ALL = 'rollbackall';
/**
 * Constant to proceed selected.
 */
const PROCEED = 'proceed';
/**
 * Constant to proceed all courses.
 */
const PROCEED_ALL = 'proceedall';

$workflow = workflow_manager::get_workflow($step->workflowid);

$mformdata = cache::make('lifecyclestep_adminapprove', 'mformdata');

$mform = new course_filter_form($PAGE->url->out());
if ($mform->is_cancelled()) {
    $mformdata->delete('data');
    redirect($PAGE->url);
}

$courseid = null;
$category = null;
$coursename = null;

if ($mformdata->has('data')) {
    $data = $mformdata->get('data');
    $courseid = $data->courseid;
    $category = $data->category;
    $coursename = $data->coursename;
    $mform->set_data($data);
}

if ($mform->is_validated()) {
    $data = $mform->get_data();
    $courseid = $data->courseid;
    $category = $data->category;
    $coursename = $data->coursename;
    $mformdata->set('data', $data);
}

if ($action) {
    require_sesskey();

    if (is_array($ids) && count($ids) > 0 && ($action == PROCEED || $action == ROLLBACK)) {
        [$insql, $inparams] = $DB->get_in_or_equal($ids);
        $sql = 'UPDATE {lifecyclestep_adminapprove} ' .
            'SET status = ' . ($action == PROCEED ? 1 : 2) . ' ' .
            'WHERE id ' . $insql . ' ' .
            'AND status = 0';
        $DB->execute($sql, $inparams);

        redirect($PAGE->url);
    }
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('adminapprovals',
        'lifecyclestep_adminapprove') . ': ' . get_string('step', 'tool_lifecycle') . ' ' . $step->instancename;
echo $renderer->header($heading);
$tabparams = new stdClass();
$tabparams->approvelink = true;
$tabrow = tabs::get_tabrow($tabparams);
$renderer->tabs($tabrow, 'adminapprove');

$hasrecords = $DB->record_exists_sql('SELECT a.id FROM {lifecyclestep_adminapprove} a ' .
        'JOIN {tool_lifecycle_process} p ON p.id = a.processid ' .
        'JOIN {tool_lifecycle_step} s ON s.workflowid = p.workflowid AND s.sortindex = p.stepindex ' .
        'WHERE s.id = :sid AND a.status = 0', ['sid' => $stepid]);

if ($hasrecords) {
    $mform->display();

    echo get_string('courses_waiting', 'lifecyclestep_adminapprove',
            ['step' => $step->instancename, 'workflow' => $workflow->title]);

    echo '<div class="mt-2">';
    echo \html_writer::span('0', 'totalrows badge badge-primary badge-pill mr-1 mb-1',
        ['id' => 'adminapprove_totalrows']);
    echo \html_writer::span(get_string('courses'));
    echo '</div>';

    $table = new lifecyclestep_adminapprove\decision_table($stepid, $courseid, $category, $coursename);
    $table->out(100, false);

    $PAGE->requires->js_call_amd('lifecyclestep_adminapprove/init', 'init', [$table->totalrows]);
} else {
    echo get_string('no_courses_waiting', 'lifecyclestep_adminapprove',
            ['step' => $step->instancename, 'workflow' => $workflow->title]);
}

echo $OUTPUT->footer();
