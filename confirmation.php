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
 * Provides a confirmation form.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\form\form_delete_delays;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');

require_admin();

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::CONFIRMATION));
$PAGE->set_context($syscontext);

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('delayed_courses_header', 'tool_lifecycle');

$formurl = new \moodle_url(urls::CONFIRMATION, ['sesskey' => sesskey()]);
$mform = new form_delete_delays($formurl);

if ($mform->is_cancelled()) {
    redirect(new \moodle_url(urls::DELAYED_COURSES));
} else if ($data = $mform->get_data()) {
    $url = new moodle_url(urls::DELAYED_COURSES, ['action' => $data->action, 'sesskey' => sesskey()]);
    redirect($url);
} else {
    echo $renderer->header($heading);
    $tabrow = tabs::get_tabrow();
    $renderer->tabs($tabrow, 'delayedcourses');

    $mform->display();

    echo $renderer->footer();
}

