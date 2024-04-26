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

$PAGE->set_title(get_string('viewheading', 'tool_lifecycle'));
$PAGE->set_heading(get_string('viewheading', 'tool_lifecycle'));

$controller = new \tool_lifecycle\view_controller();

if ($action !== null && $processid !== null && $stepid !== null) {
    require_sesskey();
    $controller->handle_interaction($action, $processid, $stepid);
    exit;
} else if ($triggerid !== null && $courseid !== null) {
    require_sesskey();
    $controller->handle_trigger($triggerid, $courseid);
    exit;
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

echo $renderer->header();

$mform = new \tool_lifecycle\local\form\form_courses_filter();

// Cache handling.
$cache = cache::make('tool_lifecycle', 'mformdata');
if ($mform->is_cancelled()) {
    $cache->delete('viewcourses_filter');
    redirect($PAGE->url);
} else if ($data = $mform->get_data()) {
    $cache->set('viewcourses_filter', $data);
} else {
    $data = $cache->get('viewcourses_filter');
    if ($data) {
        $mform->set_data($data);
    }
}

echo '<br>';

$mform->display();

echo '<br>';

$controller->handle_view($renderer, $data);

echo $renderer->footer();
