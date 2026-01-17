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
 * Display the list of all course backups
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\form\form_courses_filter;
use tool_lifecycle\local\manager\backup_manager;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_admin();

$action = optional_param('action', null, PARAM_ALPHA);
if ($action) {
    $deletedate = optional_param('deletedate', null, PARAM_INT);
} else {
    $deletedate = optional_param_array('deletedate', [], PARAM_INT);
    if ($deletedate) {
        $deletedate = make_timestamp($deletedate['year'], $deletedate['month'], $deletedate['day'],
            $deletedate['hour'], $deletedate['minute']);
    }
}
$ids = optional_param_array('c', [], PARAM_INT);

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::COURSE_BACKUPS));
$PAGE->set_context($syscontext);

/**
 * Constant to delete selected backups.
 */
const DELETE_SELECTED = 'deleteselected';
/**
 * Constant to delete all backups.
 */
const DELETE_ALL = 'deleteall';

$filterform = new form_courses_filter('', ['backups' => 1]);

// Cache handling.
$cache = cache::make('tool_lifecycle', 'mformdata');
if ($filterform->is_cancelled()) {
    $cache->delete('coursebackups_filter');
    redirect($PAGE->url);
} else if ($data = $filterform->get_data()) {
    $cache->set('coursebackups_filter', $data);
} else {
    $data = $cache->get('coursebackups_filter');
    if ($data) {
        $filterform->set_data($data);
    }
}

if ($action) {

    require_sesskey();

    $message = get_string('backupsnotdeleted', 'tool_lifecycle');

    if ($action == DELETE_ALL && $deletedate) {
        $params = ['deletedate' => $deletedate];
        $sql = "select b.id FROM {tool_lifecycle_backups} b where b.backupcreated < :deletedate";
        $records = $DB->get_recordset_sql($sql, $params);
        $ids = [];
        foreach ($records as $record) {
            $ids[] = $record->id;
        }
    }

    if (is_array($ids) && count($ids) > 0) {
        $a = 0;
        foreach ($ids as $id) {
            backup_manager::delete_course_backup($id);
            $a++;
        }
        $message = get_string('backupsdeleted', 'tool_lifecycle', $a);
    }

    redirect($PAGE->url, $message);
}

$table = new tool_lifecycle\local\table\course_backups_table('tool_lifecycle_course_backups', $data);

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');
$renderer = $PAGE->get_renderer('tool_lifecycle');
$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('course_backups_list_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$renderer->tabs($tabrow, 'coursebackups');

$where = ['TRUE'];
$params = [];
if ($data) {
    if ($data->shortname) {
        $where[] = $DB->sql_like('b.shortname', ':shortname', false, false);
        $params['shortname'] = '%' . $DB->sql_like_escape($data->shortname) . '%';
    }
    if ($data->fullname) {
        $where[] = $DB->sql_like('b.fullname', ':fullname', false, false);
        $params['fullname'] = '%' . $DB->sql_like_escape($data->fullname) . '%';
    }
    if ($data->courseid) {
        $where[] = 'b.courseid = :courseid';
        $params['courseid'] = $data->courseid;
    }
    if ($data->deletedate) {
        $where[] = 'b.backupcreated < :deletedate';
        $params['deletedate'] = $data->deletedate;
    }
}

$sql = 'SELECT count(b.id) FROM {tool_lifecycle_backups} b WHERE ' . implode(' AND ', $where);
$records = $DB->count_records_sql($sql, $params);

$filterform->display();

if ($records) {

    echo '<div class="mt-2">';
    echo \html_writer::span('0', 'totalrows badge badge-primary badge-pill mr-1 mb-1',
        ['id' => 'createbackup_totalrows']);
    echo \html_writer::span(get_string('coursebackups', 'lifecyclestep_createbackup'));
    echo '</div>';

    $table->out(100, false);

    $PAGE->requires->js_call_amd('lifecyclestep_createbackup/init', 'init', [$records]);

} else {

    echo get_string('nobackups', 'lifecyclestep_createbackup');

}

echo $renderer->footer();


