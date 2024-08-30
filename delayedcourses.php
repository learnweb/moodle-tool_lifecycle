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
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\form\form_delays_filter;
use tool_lifecycle\local\table\delayed_courses_table;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$PAGE->set_context(context_system::instance());
require_login();
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('tool_lifecycle_delayed_courses');

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
        $deleteseperate = true;
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
            $sql = 'DELETE FROM {tool_lifecycle_delayed} d ';
            if ($whereforcourse) {
                $sql .= 'WHERE d.courseid IN ( ' .
                            'SELECT c.id FROM {course} c ' .
                            'JOIN {course_categories} cat ON c.category = cat.id ' .
                            'WHERE ' . $whereforcourse .
                        ')';
            }
            $DB->execute($sql, $params);
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

$PAGE->set_url(new \moodle_url('/admin/tool/lifecycle/delayedcourses.php'));

$PAGE->set_title(get_string('delayed_courses_header', 'tool_lifecycle'));
$PAGE->set_heading(get_string('delayed_courses_header', 'tool_lifecycle'));

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

$table = new delayed_courses_table($data);
$table->define_baseurl($PAGE->url);

echo $OUTPUT->header();
$mform->display();
$table->out(100, false);

$params = ['sesskey' => sesskey(), 'action' => 'bulk-delete'];
if ($data) {
    $params = array_merge($params, (array) $data);
}

$button = new single_button(new moodle_url($PAGE->url, $params),
        get_string('delete_all_delays', 'tool_lifecycle'));

echo "<br>";
echo $OUTPUT->render($button);
echo $OUTPUT->footer();
