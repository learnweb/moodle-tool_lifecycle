<?php

/**
 * Display the list of courses relevant for a specific user in a specific step instance.
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../../config.php');

use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\interaction_manager;
use tool_cleanupcourses\table\interaction_table;

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new \moodle_url('/admin/tool/cleanupcourses/view.php'));

$stepid = required_param('stepid', PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$processid = optional_param('processid', null, PARAM_INT);

$stepinstance = step_manager::get_step_instance($stepid);

$PAGE->set_title("Title");
$PAGE->set_heading("Heading");

$renderer = $PAGE->get_renderer('tool_cleanupcourses');

echo $renderer->header();

if (interaction_manager::interaction_available($stepinstance->subpluginname)) {
    if ($action && $processid) {
        interaction_manager::handle_interaction($action, $processid);
    }

    $table = new interaction_table('tool_cleanupcourses_interaction', $stepinstance->id);

    $table->out(5000, false);

} else {
    echo get_string('nointeractioninterface', 'tool_cleanupcourses');
}

echo $renderer->footer();
