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
 * Display a protocol of course deletions
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\form\form_coursedeletions_filter;
use tool_lifecycle\local\form\form_courses_filter;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_admin();

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::COURSEDELETIONS));
$PAGE->set_context($syscontext);

$filterform = new form_coursedeletions_filter();

// Cache handling.
$cache = cache::make('tool_lifecycle', 'mformdata');
if ($filterform->is_cancelled()) {
    $cache->delete('coursedeletions_filter');
    redirect($PAGE->url);
} else if ($data = $filterform->get_data()) {
    $cache->set('coursedeletions_filter', $data);
} else {
    $data = $cache->get('coursedeletions_filter');
    if ($data) {
        $filterform->set_data($data);
    }
}

$table = new tool_lifecycle\local\table\course_deletions_table('tool_lifecycle_course_deletions', $data);

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');
$renderer = $PAGE->get_renderer('tool_lifecycle');
$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('course_deletions_list_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$renderer->tabs($tabrow, 'activeworkflows');

$where = ['TRUE'];
$params = [];
if ($data) {
    if ($data->courseid) {
        $where[] = 'd.courseid = :courseid';
        $params['courseid'] = $data->courseid;
    }
}

$sql = 'SELECT count(d.id) FROM {lifecyclestep_deletecourse} d WHERE ' . implode(' AND ', $where);
$records = $DB->count_records_sql($sql, $params);

$filterform->display();

if ($records) {

    echo '<div class="mt-2">';
    echo \html_writer::span($records, 'totalrows badge badge-primary badge-pill mr-1 mb-1',
        ['id' => 'coursedeletions_totalrows']);
    echo \html_writer::span(get_string('numberofcoursedeletions', 'tool_lifecycle'));
    echo '</div>';

    $table->out(100, false);

} else {

    echo get_string('nocoursesdeleted', 'tool_lifecycle');

}

echo $renderer->footer();


