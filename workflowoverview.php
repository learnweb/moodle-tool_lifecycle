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
 * Displays the tables of active and inactive workflow definitions and handles all action associated with it.
 *
 * @package tool_lifecycle
 * @copyright  2021 Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/adminlib.php');

use tool_lifecycle\local\table\interaction_attention_table;

global $OUTPUT, $PAGE, $DB;

admin_externalpage_setup('tool_lifecycle_active_workflows');
$PAGE->set_context(context_system::instance());

$workflowid = required_param('wf', PARAM_INT);
$stepid = optional_param('step', 0, PARAM_INT);
$triggerid = optional_param('trigger', 0, PARAM_INT);

$PAGE->set_pagelayout('standard');
$PAGE->set_url(new \moodle_url("/admin/tool/lifecycle/workflowoverview.php", ['wf' => $workflowid]));
$PAGE->set_title(get_string('workflowoverview_list_header', 'tool_lifecycle'));
$PAGE->set_heading(get_string('workflowoverview_list_header', 'tool_lifecycle'));

$renderer = $PAGE->get_renderer('tool_lifecycle');

echo $renderer->header();
$steps = $DB->get_records('tool_lifecycle_step', array('workflowid' => $workflowid));
$trigger = $DB->get_records('tool_lifecycle_trigger', array('workflowid' => $workflowid));

$str = [
    'edit' => get_string('edit'),
];

$arrayoftrigger = array();
foreach ($trigger as $key => $value) {
    // The array from the DB Function uses ids as keys.
    // Mustache cannot handle arrays which have other keys therefore a new array is build.
    // FUTURE: Nice to have Icon for each subplugin.
    // FUTURE: Nice to have How many courses will be caught by the trigger?
    $objectvar = (object) $trigger[$key];
    $actionmenu = new action_menu([
        new action_menu_link_secondary(new moodle_url('/asdf'), new pix_icon('i/edit', $str['edit']), $str['edit'])
    ]);
    $objectvar->actionmenu = $OUTPUT->render($actionmenu);

    $arrayoftrigger[$objectvar->sortindex - 1] = $objectvar;
    asort($arrayoftrigger);
}

$arrayofsteps = array();
foreach ($steps as $key => $step) {
    $stepobject = (object) $steps[$key];
    $ncourses = $DB->count_records('tool_lifecycle_process',
        array('stepindex' => $stepobject->sortindex, 'workflowid' => $workflowid));
    $stepobject->numberofcourses = $ncourses;
    $actionmenu = new action_menu([
        new action_menu_link_secondary(new moodle_url('/asdf'), new pix_icon('i/edit', $str['edit']), $str['edit'])
    ]);
    $stepobject->actionmenu = $OUTPUT->render($actionmenu);
    $arrayofsteps[$stepobject->sortindex - 1] = $stepobject;
}
asort($arrayofsteps);

$arrayofcourses = array();

$url = new moodle_url("/admin/tool/lifecycle/workflowoverview.php", array('wf' => $workflowid));

$data = [
    'trigger' => $arrayoftrigger,
    'steps' => $arrayofsteps,
    'listofcourses' => $arrayofcourses,
    'steplink' => $url
];
echo $OUTPUT->render_from_template('tool_lifecycle/workflowoverview', $data);
if ($stepid) {

    $listofcourses = $DB->get_records_sql("SELECT p.id as processid, c.id as courseid, c.fullname as coursefullname, " .
        "c.shortname as courseshortname, s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname " .
        "FROM {tool_lifecycle_process} p join " .
        "{course} c on p.courseid = c.id join " .
        "{tool_lifecycle_step} s ".
        "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex " .
        "WHERE p.stepindex = :stepindex AND p.workflowid = :wfid;", array('stepindex' => $stepid, 'wfid' => $workflowid));
    $listofids = array();
    foreach ($listofcourses as $key => $value) {
        $listofids = $value->courseid;
        $objectvar = (object) $listofcourses[$key];
        array_push($arrayofcourses, $objectvar);
    }
    asort($arrayofcourses);
    $table = new interaction_attention_table('tool_lifecycle_interaction', $listofids);
    $table->out(50, false);
}
echo $renderer->footer();
