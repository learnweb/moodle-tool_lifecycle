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
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$PAGE->set_context(context_system::instance());
require_login(null, false);
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('tool_cleanupcourses_coursebackups');

$PAGE->set_url(new \moodle_url('/admin/tool/cleanupcourses/coursebackups.php'));

$table = new tool_cleanupcourses\table\course_backups_table('tool_cleanupcourses_course_backups');

$PAGE->set_title(get_string('course_backups_list_header', 'tool_cleanupcourses'));
$PAGE->set_heading(get_string('course_backups_list_header', 'tool_cleanupcourses'));

$renderer = $PAGE->get_renderer('tool_cleanupcourses');

echo $renderer->header();
$table->out(50, false);
echo $renderer->footer();


