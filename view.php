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

use tool_lifecycle\local\form\form_courses_filter;
use tool_lifecycle\view_controller;

require_once(__DIR__ . '/../../../config.php');

require_login(null, false);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new \moodle_url('/admin/tool/lifecycle/view.php'));
$PAGE->navbar->add(get_string('mycourses'))->add(get_string('managecourses_link', 'tool_lifecycle'),
        $PAGE->url);

// Interaction params.
$action = optional_param('action', null, PARAM_ALPHA);
$processid = optional_param('processid', null, PARAM_INT);
$stepid = optional_param('stepid', null, PARAM_INT);

// Manual trigger params.
$triggerid = optional_param('triggerid', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);

// Bulk edit params.
$bulkedit = optional_param('bulkedit', 0, PARAM_INT);
$bulkactions = optional_param_array('bulkactions', [], PARAM_TEXT);
if ($reportparam = optional_param('report', null, PARAM_TEXT)) {
    $reportparam = explode("__", urldecode($reportparam));
}

$controller = new view_controller();

$report = [];
if ($bulkactions) { // Only for interactions so far.
    require_sesskey();
    foreach ($bulkactions as $action) {
        $items = explode('_', $action);
        if (count($items) == 2) {
            $triggerid = $items[0];
            $courseid = $items[1];
            $course = get_course($courseid);
            $coursename = get_course_display_name_for_list($course);
            if ($rc = $controller->handle_trigger($triggerid, $courseid, $bulkactions)) {
                $report[] = \html_writer::div($coursename.": ".$rc, 'alert alert-danger');
            } else {
                $successmsg = get_string('manual_trigger_success', 'tool_lifecycle');
                $report[] = \html_writer::div($coursename.": ".$successmsg, 'alert alert-success');
            }
        }
    }
} else { // Single edit: interaction or manual workflow.
    if ($action !== null && $processid !== null && $stepid !== null) { // User interaction.
        require_sesskey();
        $controller->handle_interaction($action, $processid, $stepid);
        exit;
    } else if ($triggerid !== null && $courseid !== null) { // Manual workflow ("remaining courses").
        require_sesskey();
        $controller->handle_trigger($triggerid, $courseid);
        exit;
    }
}
if ($report) {
    $report = urlencode(implode("__", $report));
    $redirecturl = new moodle_url($PAGE->url, ['report' => $report, 'bulkedit' => $bulkedit]);
    redirect($redirecturl);
}

$PAGE->set_title(get_string('viewheading', 'tool_lifecycle'));
$PAGE->set_heading(get_string('viewheading', 'tool_lifecycle'));

$renderer = $PAGE->get_renderer('tool_lifecycle');

if ($bulkedit) {
    $PAGE->requires->js_call_amd('tool_lifecycle/tablebulkactions_view', 'init');
}

echo $renderer->header();

$filterform = new form_courses_filter('', ['bulkedit' => $bulkedit]);

// Cache handling.
$cache = cache::make('tool_lifecycle', 'mformdata');
if ($filterform->is_cancelled()) {
    $cache->delete('viewcourses_filter');
    redirect($PAGE->url);
} else if ($data = $filterform->get_data()) {
    $cache->set('viewcourses_filter', $data);
} else {
    $data = $cache->get('viewcourses_filter');
    if ($data) {
        $filterform->set_data($data);
    }
}

if ($reportparam) {
    foreach ($reportparam as $message) {
        echo $message;
    }
    echo '<br>';
}

$filterform->display();

echo '<br>';

$controller->handle_view($renderer, $data, $bulkedit);

echo $renderer->footer();
