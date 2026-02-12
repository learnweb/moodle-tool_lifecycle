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

use tool_lifecycle\local\form\form_errors_filter;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\table\process_errors_table;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_admin();

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::PROCESS_ERRORS));
$PAGE->set_context($syscontext);

// Action handling (delete, bulk-delete).
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
if ($reportparam = optional_param('report', null, PARAM_TEXT)) {
    $reportparam = explode("__", urldecode($reportparam));
}
if ($action) {
    $report = [];
    require_sesskey();
    $ids = required_param_array('id', PARAM_INT);
    if ($action == 'proceed') {
        foreach ($ids as $id) {
            if ($courseid = $DB->get_field('tool_lifecycle_proc_error', 'courseid', ['id' => $id])) {
                $course = get_course($courseid);
                $coursename = get_course_display_name_for_list($course);
            } else {
                $coursename = get_string('coursenotfound', 'tool_lifecycle');
            }
            $rc = process_manager::proceed_process_after_error($id);
            if ($rc) {
                $successmsg = get_string('proceed_process_after_error_success', 'tool_lifecycle');
                $report[] = \html_writer::div($coursename.": ".$successmsg, 'alert alert-success');
            } else {
                $failedsmsg = get_string('proceed_process_after_error_fail', 'tool_lifecycle', $rc);
                $report[] = \html_writer::div($coursename.": ".$failedsmsg, 'alert alert-danger');
            }
        }
    } else if ($action == 'rollback') {
        foreach ($ids as $id) {
            if ($courseid = $DB->get_field('tool_lifecycle_proc_error', 'courseid', ['id' => $id])) {
                $course = get_course($courseid);
                $coursename = get_course_display_name_for_list($course);
            } else {
                $coursename = get_string('coursenotfound', 'tool_lifecycle');
            }
            $rc = process_manager::rollback_process_after_error($id);
            if ($rc) {
                $successmsg = get_string('rollback_process_after_error_success', 'tool_lifecycle');
                $report[] = \html_writer::div($coursename.": ".$successmsg, 'alert alert-success');
            } else {
                $failedsmsg = get_string('rollback_process_after_error_fail', 'tool_lifecycle', $rc);
                $report[] = \html_writer::div($coursename.": ".$failedsmsg, 'alert alert-danger');
            }
        }
    } else if ($action == 'delete') {
        $deleted = 0;
        $notdeleted = 0;
        foreach ($ids as $id) {
            if ($DB->delete_records('tool_lifecycle_proc_error', ['id' => $id])) {
                $deleted++;
            } else {
                $notdeleted++;
            }
        }
        if ($deleted) {
            $successmsg = get_string('errors_deleted_success', 'tool_lifecycle', $deleted);
            $report[] = \html_writer::div($successmsg, 'alert alert-success');
        }
        if ($notdeleted) {
            $failedsmsg = get_string('errors_deleted_fail', 'tool_lifecycle', $notdeleted);
            $report[] = \html_writer::div($failedsmsg, 'alert alert-danger');
        }
    }
    if ($report) {
        $report = urlencode(implode("__", $report));
        $redirecturl = new moodle_url($PAGE->url, ['report' => $report]);
        redirect($redirecturl);
    }
    redirect($PAGE->url, get_string('deleteprocesserrormsg', 'tool_lifecycle'), 3);
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

// Get selected filter form options if there are any.
$workflow = optional_param('workflow', 0, PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$step = optional_param('step', 0, PARAM_INT);
// Load filter form.
$mform = new form_errors_filter($PAGE->url, ['workflow' => $workflow, 'course' => $course, 'step' => $step]);

// Cache handling.
$cache = cache::make('tool_lifecycle', 'mformdata');
if ($mform->is_cancelled()) {
    $cache->delete('errors_filter');
    redirect($PAGE->url);
} else if ($data = $mform->get_data()) {
    $cache->set('errors_filter', $data);
} else {
    $data = $cache->get('errors_filter');
    if ($data) {
        $mform->set_data($data);
    }
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('process_errors_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$renderer->tabs($tabrow, 'errors');

if ($reportparam) {
    foreach ($reportparam as $message) {
        echo $message;
    }
    echo '<br>';
}

// Get number of process errors.
$sql = "select count(c.id) from {tool_lifecycle_proc_error} pe
    LEFT JOIN {tool_lifecycle_workflow} w ON pe.workflowid = w.id
    LEFT JOIN {tool_lifecycle_step} s ON pe.workflowid = s.workflowid AND pe.stepindex = s.sortindex
    LEFT JOIN {course} c ON pe.courseid = c.id";
$errors = $DB->count_records_sql($sql);

$table = new process_errors_table($data);
$table->define_baseurl($PAGE->url);

$PAGE->requires->js_call_amd('tool_lifecycle/tablebulkactions', 'init');

if ($errors > 0) {
    $mform->display();
}

$table->out(100, false);

echo $OUTPUT->footer();
