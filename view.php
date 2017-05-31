<?php

/**
 * Display the list of courses relevant for a specific user in a specific step instance.
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../../config.php');

use tool_cleanupcourses\table\interaction_table;

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new \moodle_url('/admin/tool/cleanupcourses/view.php'));

$subpluginid = required_param('subpluginid', PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);

$table = new interaction_table('tool_cleanupcourses_interaction');

$PAGE->set_title("Title");
$PAGE->set_heading("Heading");

$renderer = $PAGE->get_renderer('tool_cleanupcourses');

echo $renderer->header();

$table->out(50000, false);

echo $renderer->footer();