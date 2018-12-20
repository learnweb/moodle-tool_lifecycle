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
 * Displays all deactivated workflows
 *
 * @package tool_lifecycle
 * @copyright  2018 Yorick Reum, JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/adminlib.php');

use tool_lifecycle\table\deactivated_workflows_table;

$PAGE->set_context(context_system::instance());
require_login(null, false);
require_capability('moodle/site:config', context_system::instance());

// admin_externalpage_setup('tool_lifecycle_deactivatedworkflows');

$PAGE->set_url(new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php'));

$PAGE->set_title(get_string('deactivated_workflows_list_header', 'tool_lifecycle'));
$PAGE->set_heading(get_string('deactivated_workflows_list_header', 'tool_lifecycle'));

$workflowid = optional_param('workflowid', null, PARAM_INT);
$action = optional_param('action', null, PARAM_TEXT);
if ($workflowid && $action) {
    \tool_lifecycle\manager\workflow_manager::handle_action($action, $workflowid);
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

$table = new deactivated_workflows_table('tool_lifecycle_deactivated_workflows');

echo $renderer->header();

$table->out(50, false);

echo $renderer->footer();