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
 * Displays the process errors
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2021 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\table\process_errors_table;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::PROCESS_ERRORS));
$PAGE->set_context($syscontext);

// Action handling (delete, bulk-delete).
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
if ($action) {
    global $DB;
    require_sesskey();
    $ids = required_param_array('id', PARAM_INT);
    if ($action == 'proceed') {
        foreach ($ids as $id) {
            process_manager::proceed_process_after_error($id);
        }
    } else if ($action == 'rollback') {
        foreach ($ids as $id) {
            process_manager::rollback_process_after_error($id);
        }
    } else {
        throw new coding_exception("action must be either 'proceed' or 'rollback'");
    }
    redirect($PAGE->url);
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('process_errors_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$id = optional_param('id', 'settings', PARAM_TEXT);
$renderer->tabs($tabrow, $id);

$table = new process_errors_table();
$table->define_baseurl($PAGE->url);

$PAGE->requires->js_call_amd('tool_lifecycle/tablebulkactions', 'init');

$table->out(100, false);

echo $OUTPUT->footer();
