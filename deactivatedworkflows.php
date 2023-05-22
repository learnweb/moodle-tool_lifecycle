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
require_once($CFG->libdir . '/adminlib.php');
require_login();

use tool_lifecycle\local\table\deactivated_workflows_table;
use tool_lifecycle\urls;

global $PAGE, $OUTPUT;

\tool_lifecycle\permission_and_navigation::setup_deactived();

$PAGE->set_url(new \moodle_url(urls::DEACTIVATED_WORKFLOWS));
$PAGE->set_title(get_string('deactivated_workflows_list_header', 'tool_lifecycle'));
$PAGE->set_heading(get_string('deactivated_workflows_list_header', 'tool_lifecycle'));

$workflowid = optional_param('workflowid', null, PARAM_INT);
$action = optional_param('action', null, PARAM_TEXT);
if ($workflowid && $action) {
    \tool_lifecycle\local\manager\workflow_manager::handle_action($action, $workflowid);
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

$table = new deactivated_workflows_table('tool_lifecycle_deactivated_workflows');

echo $renderer->header();

echo $OUTPUT->box_start("lifecycle-enable-overflow lifecycle-table");

$table->out(50, false);

echo $OUTPUT->box_end();

echo \html_writer::link(new \moodle_url(urls::WORKFLOW_DRAFTS),
    get_string('workflow_drafts_list', 'tool_lifecycle'));
echo '<br>';
echo \html_writer::link(new \moodle_url(urls::ACTIVE_WORKFLOWS),
    get_string('active_workflows_list', 'tool_lifecycle'));

echo $renderer->footer();
