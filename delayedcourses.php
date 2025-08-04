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
 * Display the delays of courses
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\form\form_delays_filter;
use tool_lifecycle\local\table\delayed_courses_table;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_admin();

$wfid = optional_param('wfid', 0, PARAM_INT);

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::DELAYED_COURSES, ['wfid' => $wfid]));
$PAGE->set_context($syscontext);

// Action handling (delete, bulk-delete).
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
if ($action) {
    global $DB;
    require_sesskey();
    if ($action == 'delete') {
        $cid = required_param('cid', PARAM_INT);
        $workflow = optional_param('workflow', null, PARAM_ALPHANUM);
        if ($workflow) {
            if (is_number($workflow)) {
                $DB->delete_records('tool_lifecycle_delayed_workf', ['courseid' => $cid, 'workflowid' => $workflow]);
            } else if ($workflow == 'global') {
                $DB->delete_records('tool_lifecycle_delayed', ['courseid' => $cid]);
            } else {
                throw new \coding_exception('workflow has to be "global" or a int value');
            }
        } else {
            $DB->delete_records('tool_lifecycle_delayed', ['courseid' => $cid]);
            $DB->delete_records('tool_lifecycle_delayed_workf', ['courseid' => $cid]);
        }
    } else if ($action == 'bulk-delete') {
        $workflow = optional_param('workflow', null, PARAM_ALPHANUM);
        $deleteglobal = true;
        $deleteseperate = false;
        $workflowfilterid = null;
        if ($workflow) {
            if ($workflow == 'global') {
                $deleteseperate = false;
            } else if (is_number($workflow)) {
                $deleteglobal = false;
                $workflowfilterid = $workflow;
            } else {
                throw new \coding_exception('workflow has to be "global" or a int value');
            }
        }

        $coursename = optional_param('coursename', null, PARAM_TEXT);
        $categoryid = optional_param('catid', null, PARAM_INT);

        $params = [];
        $whereforcourse = [];

        if ($coursename) {
            $whereforcourse[] = 'c.fullname LIKE :cname';
            $params['cname'] = '%' . $DB->sql_like_escape($coursename) . '%';
        }
        if ($categoryid) {
            $whereforcourse[] = 'cat.id = :catid';
            $params['catid'] = $categoryid;
        }

        $whereforcourse = implode(' AND ', $whereforcourse);
        if ($deleteglobal) {
            if ($whereforcourse) {
                $sql = 'DELETE FROM {tool_lifecycle_delayed} d WHERE d.courseid IN ( ' .
                            'SELECT c.id FROM {course} c ' .
                            'JOIN {course_categories} cat ON c.category = cat.id ' .
                            'WHERE ' . $whereforcourse .
                        ')';
                $DB->execute($sql, $params);
            } else {
                $sql = 'DELETE FROM {tool_lifecycle_delayed}';
                $DB->execute($sql, []);
                $sql = 'DELETE FROM {tool_lifecycle_delayed_workf}';
                $DB->execute($sql, []);
            }
        }

        if ($deleteseperate) {
            $sql = 'DELETE FROM {tool_lifecycle_delayed_workf} dw ' .
                    'WHERE TRUE ';
            if ($whereforcourse) {
                $sql .= 'AND dw.courseid IN ( ' .
                            'SELECT c.id FROM {course} c ' .
                            'JOIN {course_categories} cat ON c.category = cat.id ' .
                            'WHERE ' . $whereforcourse .
                        ')';
            }
            if ($workflowfilterid) {
                $sql .= 'AND dw.workflowid = :workflowid';
                $params['workflowid'] = $workflowfilterid;
            }
            $DB->execute($sql, $params);
        }
    }
    redirect($PAGE->url);
}

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('delayed_courses_header', 'tool_lifecycle');
echo $renderer->header($heading);
$tabparams = new stdClass();
$tabparams->wfid = $wfid;
$tabrow = tabs::get_tabrow($tabparams);
$id = optional_param('id', 'settings', PARAM_TEXT);
$renderer->tabs($tabrow, $id);

$mform = new form_delays_filter($PAGE->url);

// Cache handling.
$cache = cache::make('tool_lifecycle', 'mformdata');
if ($mform->is_cancelled()) {
    $cache->delete('delays_filter');
    redirect($PAGE->url);
} else if ($data = $mform->get_data()) {
    $cache->set('delays_filter', $data);
} else {
    $data = $cache->get('delays_filter');
    if ($data) {
        $mform->set_data($data);
    }
}

// Get number of delayed courses.
$time = time();
$sql = "select count(c.id) from {course} c LEFT JOIN
        (SELECT dw.courseid, dw.workflowid, w.title as workflow, dw.delayeduntil as workflowdelay,maxtable.wfcount as workflowcount
         FROM ( SELECT courseid, MAX(dw.id) AS maxid, COUNT(*) AS wfcount FROM {tool_lifecycle_delayed_workf} dw
            JOIN {tool_lifecycle_workflow} w ON dw.workflowid = w.id
            WHERE dw.delayeduntil >= $time AND w.timeactive IS NOT NULL GROUP BY courseid ) maxtable JOIN
             {tool_lifecycle_delayed_workf} dw ON maxtable.maxid = dw.id JOIN
             {tool_lifecycle_workflow} w ON dw.workflowid = w.id ) wfdelay ON wfdelay.courseid = c.id LEFT JOIN
            (SELECT * FROM {tool_lifecycle_delayed} d WHERE d.delayeduntil > $time ) d ON c.id = d.courseid JOIN
            {course_categories} cat ON c.category = cat.id
        where COALESCE(wfdelay.courseid, d.courseid) IS NOT NULL";
$delayedcourses = $DB->count_records_sql($sql);

$table = new delayed_courses_table($data);
$table->define_baseurl($PAGE->url);

if ($delayedcourses > 0) {
    $mform->display();
    $params = ['sesskey' => sesskey(), 'action' => 'bulk-delete'];
    if ($data) {
        $params = array_merge($params, (array) $data);
    }
    $button = new single_button(new moodle_url('confirmation.php'),
        get_string('delete_all_delays', 'tool_lifecycle'));
    echo $OUTPUT->render($button);
    $classnotnull = 'badge badge-primary badge-pill ml-1';
    $classnull = 'badge badge-secondary badge-pill ml-1';
    echo \html_writer::span($delayedcourses, $delayedcourses > 0 ? $classnotnull : $classnull);
    echo html_writer::div('', 'mb-2');
}

$table->out(100, false);

echo $OUTPUT->footer();
