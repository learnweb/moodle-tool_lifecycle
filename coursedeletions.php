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
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

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

$download = optional_param('download', false, PARAM_TEXT);
if ($download == 'csv') {
    $table->is_downloading('csv', 'coursedeletions');
    $table->out(0, false);
}
/* Does not work yet.
if ($download == 'xlsx') {
    $table->is_downloading('xlsx', 'coursedeletions');
    $table->out(0, false);
}
*/

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');
$renderer = $PAGE->get_renderer('tool_lifecycle');
$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('course_deletions_list_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$renderer->tabs($tabrow, '');

$where = ['TRUE'];
$params = [];
if ($data) {
    if ($data->courseid) {
        $where[] = 'd.courseid = :courseid';
        $params['courseid'] = $data->courseid;
    }
}

$sql = "SELECT COUNT(id) AS records,
            COALESCE(SUM(modules), 0) AS modules,
            COALESCE(SUM(participants), 0) AS participants
          FROM {lifecyclestep_deletecourse} d ";
$sql .= " WHERE " . implode(' AND ', $where);

$records = 0;
if ($sums = $DB->get_record_sql($sql, $params)) {
    $records = (int)$sums->records;
    $modules = (int)$sums->modules;
    $participants = (int)$sums->participants;
}

$filterform->display();

if ($records) {

    $data = [
        'records' => $records,
        'modules' => $modules,
        'participants' => $participants,

        'numberofcoursedeletions' => get_string('numberofcoursedeletions', 'tool_lifecycle'),
        'numberofmodules' => get_string('numberofmodules', 'tool_lifecycle'),
        'numberofparticipants' => get_string('numberofparticipants', 'tool_lifecycle'),
        'downloadlinkcsv' => html_writer::link($PAGE->url->out(false, ['download' => 'csv']), get_string('downloadcsv', 'tool_lifecycle')),
        'downloadlinkxlsx' => html_writer::link($PAGE->url->out(false, ['download' => 'xlsx']), get_string('downloadxlsx', 'tool_lifecycle')),
    ];

    echo $OUTPUT->render_from_template(
        'tool_lifecycle/coursedeletions_summary',
        $data
    );

    $table->out(100, true);

} else {

    echo get_string('nocoursesdeleted', 'tool_lifecycle');

}

echo $renderer->footer();




