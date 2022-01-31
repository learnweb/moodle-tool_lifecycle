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

use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\table\interaction_attention_table;

global $OUTPUT, $PAGE, $DB;
$PAGE->set_context(context_system::instance());
require_login(null, false);
require_capability('moodle/site:config', context_system::instance());
admin_externalpage_setup('tool_lifecycle_workflowoverview');

$workflowid = required_param('wf', PARAM_INT);
$stepid = optional_param('step', 0, PARAM_INT);
$triggerid = optional_param('trigger', 0, PARAM_INT);

$PAGE->set_pagelayout('standard');
$PAGE->set_url(new \moodle_url("/admin/tool/lifecycle/workflowoverview.php"));
$PAGE->set_title(get_string('workflowoverview_list_header', 'tool_lifecycle'));
$PAGE->set_heading(get_string('workflowoverview_list_header', 'tool_lifecycle'));

$renderer = $PAGE->get_renderer('tool_lifecycle');

echo $renderer->header();
$steps = $DB->get_records('tool_lifecycle_step', array('workflowid' => $workflowid));
$trigger = $DB->get_records('tool_lifecycle_trigger', array('workflowid' => $workflowid));

$arrayoftrigger = array();
foreach ($trigger as $key => $value) {
    // The array from the DB Function uses ids as keys.
    // Mustache cannot handle arrays which have other keys therefore a new array is build.
    // FUTURE: Nice to have Icon for each subplugin.
    // FUTURE: Nice to have How many courses will be caught by the trigger?
    $objectvar = (object) $trigger[$key];
    $arrayoftrigger[$objectvar->sortindex - 1] = $objectvar;
    asort($arrayoftrigger);
}

$arrayofsteps = array();
foreach ($steps as $key => $step) {
    $stepobject = (object) $steps[$key];
    $ncourses = $DB->count_records('tool_lifecycle_process',
        array('stepindex' => $stepobject->sortindex, 'workflowid' => $workflowid));
    $stepobject->numberofcourses = $ncourses;
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

if ($stepid) {

    $listofcourses = $DB->get_records_sql("SELECT p.id as processid, c.id as courseid, c.fullname as coursefullname, " .
        "c.shortname as courseshortname, c.startdate as startdate, cc.name as category, " .
        "s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname as subpluginname " .
        "FROM {tool_lifecycle_process} p join " .
        "{course} c on p.courseid = c.id join " .
        "{tool_lifecycle_step} s ".
        "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex join " .
        "{course_categories} cc on c.category = cc.id " .
        "WHERE p.stepindex = :stepindex AND p.workflowid = :wfid;", array('stepindex' => $stepid, 'wfid' => $workflowid));

    foreach ($listofcourses as $key => $value) {

        // Course name.
        $courselink = \html_writer::link(course_get_url($value->courseid), format_string($value->coursefullname));
        $value->courselink = $courselink . '<br><span class="secondary-info">' . $value->courseshortname . '</span>';

        // Status.
        if ($value->processid !== null) {
            $workflow = workflow_manager::get_workflow($workflowid);
            $value->status = interaction_manager::get_process_status_message($value->processid) .
            '<br><span class="workflow_displaytitle">' . $workflow->displaytitle . '</span>';
        }

        // Tools.
        $output = '';
        $step = step_manager::get_step_instance($value->stepinstanceid);
        $tools = interaction_manager::get_action_tools($step->subpluginname, $value->processid);
        $url = '/admin/tool/lifecycle/action.php';
        foreach ($tools as $tool) {
            $button = new \single_button(new \moodle_url($url,
                array(
                    'stepid' => $step->id,
                    'action' => $tool['action'],
                    'processid' => $value->processid,
                    'sesskey' => sesskey(),
                    'workflowid' => $workflowid,
                    'step' => $stepid
                )), $tool['alt']
            );
            $output .= $OUTPUT->render($button);
        }
        $value->tools = $output;

        // Due date.
        $lib = lib_manager::get_step_interactionlib($value->subpluginname);
        $date = $lib->get_due_date($value->processid, $value->stepinstanceid);
        $value->date = $date;
    }

    $data['courses'] = array_values($listofcourses);

}
echo $OUTPUT->render_from_template('tool_lifecycle/workflowoverview', $data);
echo $renderer->footer();
