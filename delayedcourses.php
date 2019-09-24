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

use tool_lifecycle\table\delayed_courses_table;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$PAGE->set_context(context_system::instance());
require_login();
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('tool_lifecycle_delayed_courses');

$action = optional_param('action', null, PARAM_ALPHANUMEXT);
if ($action) {
    if ($action == 'delete') {
        global $DB;
        require_sesskey();
        $cid = required_param('cid', PARAM_INT);
        $workflow = optional_param('workflow', null, PARAM_ALPHANUM);
        if ($workflow) {
            if (is_int($workflow)) {
                $DB->delete_records('tool_lifecycle_delayed_workf', array('courseid' => $cid, 'workflowid' => $workflow));
            } else if ($workflow == 'global') {
                $DB->delete_records('tool_lifecycle_delayed', array('courseid' => $cid));
            }
        } else {
            $DB->delete_records('tool_lifecycle_delayed', array('courseid' => $cid));
            $DB->delete_records('tool_lifecycle_delayed_workf', array('courseid' => $cid));
        }
    }
    redirect($PAGE->url);
}

$PAGE->set_url(new \moodle_url('/admin/tool/lifecycle/delayedcourses.php'));

$PAGE->set_title(get_string('delayed_courses_header', 'tool_lifecycle'));
$PAGE->set_heading(get_string('delayed_courses_header', 'tool_lifecycle'));

$table = new delayed_courses_table();
$table->define_baseurl($PAGE->url);

echo $OUTPUT->header();
$table->out(100, false);
echo $OUTPUT->footer();
