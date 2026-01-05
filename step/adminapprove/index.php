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
 * Life Cycle Admin Approve Step
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_admin();
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$wfid = optional_param('wfid', null, PARAM_INT);
$stepindex = optional_param('stepindex', null, PARAM_INT);

/**
 * Constant to roll back selected.
 */
const ROLLBACK = 'rollback';
/**
 * Constant to proceed selected.
 */
const PROCEED = 'proceed';

$PAGE->set_context(context_system::instance());
$PAGE->set_url("/admin/tool/lifecycle/step/adminapprove/index.php");

if ($action) {
    require_sesskey();

    $subselect = 'SELECT id FROM {tool_lifecycle_process} WHERE workflowid = :wfid AND stepindex = :stepindex';
    $params = ['wfid' => $wfid, 'stepindex' => $stepindex];
    if ($action == PROCEED || $action == ROLLBACK) {
        $sql = 'UPDATE {lifecyclestep_adminapprove} ' .
            'SET status = ' . ($action == PROCEED ? 1 : 2) . ' ' .
            'WHERE processid IN (' . $subselect . ') ' .
            'AND status = 0';
        try {
            $DB->execute($sql, $params);
        } catch (dml_exception $e) {
            throw $e;
        }

        $a = new stdClass();
        $step = step_manager::get_step_instance_by_workflow_index($wfid, $stepindex);
        $workflow = workflow_manager::get_workflow($wfid);
        $a->step = $step->instancename;
        $a->workflow = $workflow->title;

        if ($action == PROCEED) {
            $message = get_string('allstepapprovalsproceed', 'lifecyclestep_adminapprove', $a);
        } else if ($action == ROLLBACK) {
            $message = get_string('allstepapprovalsrollback', 'lifecyclestep_adminapprove', $a);
        }

        redirect($PAGE->url, $message);
    }
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('pluginname',
        'lifecyclestep_adminapprove');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$renderer->tabs($tabrow, 'adminapprove');

$table = new lifecyclestep_adminapprove\step_table();
$table->out(100, false);

$PAGE->requires->js_call_amd('lifecyclestep_adminapprove/link-steps', 'init');

echo $OUTPUT->footer();

