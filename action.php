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
 * Display the list of courses relevant for a specific user in a specific step instance.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');

use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\table\interaction_attention_table;

require_login(null, false);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new \moodle_url('/admin/tool/lifecycle/action.php'));

// Interaction params.
$action = optional_param('action', null, PARAM_ALPHA);
$processid = optional_param('processid', null, PARAM_INT);
$stepid = optional_param('stepid', null, PARAM_INT);

// Manual trigger params.
$triggerid = optional_param('triggerid', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);

// Workflowoverview params.
$workflowid = optional_param('workflowid', null, PARAM_INT);
$sortindex = optional_param('step', null, PARAM_INT);

$controller = new \tool_lifecycle\view_controller();

if ($action !== null && $processid !== null && $stepid !== null) {
    require_sesskey();
    $controller->handle_interaction($action, $processid, $stepid);
    exit;
} else if ($triggerid !== null && $courseid !== null) {
    require_sesskey();
    $controller->handle_trigger($triggerid, $courseid);
    exit;
}

// TODO: Fix redirect. issue: handle_interaction does another redirect, which does not pass the needed params.
$url = new \moodle_url('/admin/tool/lifecycle/workflowoverview.php',
    array(
        'wf' => $workflowid,
        'step' => $sortindex
    ));
redirect($url);
