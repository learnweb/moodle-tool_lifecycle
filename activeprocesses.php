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
 * Display the list of active processes
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_login();
\tool_lifecycle\permission_and_navigation::setup_active();

$PAGE->set_url(new \moodle_url(\tool_lifecycle\urls::ACTIVE_PROCESSES));

$title = get_string('find_course_list_header', 'tool_lifecycle');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $PAGE->url);

$renderer = $PAGE->get_renderer('tool_lifecycle');
$mform = new \tool_lifecycle\local\form\form_courses_filter();

// Cache handling.
$cachekey = 'activeprocesses_filter';
$cache = cache::make('tool_lifecycle', 'mformdata');
if ($search = optional_param('search', null, PARAM_RAW)) {
    $obj = new stdClass();
    $obj->fullname = $search;
    $obj->courseid = null;
    $obj->shortname = null;
    $cache->set($cachekey, $obj);
    redirect($PAGE->url);
}

if ($mform->is_cancelled()) {
    $cache->delete($cachekey);
    redirect($PAGE->url);
} else if ($data = $mform->get_data()) {
    $cache->set($cachekey, $data);
} else {
    $data = $cache->get($cachekey);
    if ($data) {
        $mform->set_data($data);
    }
}

$table = new tool_lifecycle\local\table\active_processes_table('tool_lifecycle_active_processes', $data);

echo $renderer->header();

$mform->display();

$table->out(50, false);

echo $renderer->footer();


