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
/*
array(1) { [3]=> object(stdClass)#6974 (5) { ["id"]=> string(1) "3" ["instancename"]=> string(8) "Category" ["subpluginname"]=> string(10) "categories" ["workflowid"]=> string(1) "3" ["sortindex"]=> string(1) "1" } }
Category
array(1) { [1]=> object(stdClass)#6968 (5) { ["id"]=> string(1) "1" ["instancename"]=> string(9) "A backups" ["subpluginname"]=> string(12) "createbackup" ["workflowid"]=> string(1) "3" ["sortindex"]=> string(1) "1" } }
*/
$arrayoftrigger = array();
foreach ($trigger as $key => $value) {
    // The Array from the DB Function uses ids as keys.
    // Mustache cannot handle arrays which have other keys therefore a new
    // array is build.
    // Nice to have Icon for each subplugin.
    // TODO right order? --> sortindex?
    // TODO Nice to have How many courses will be caught by the trigger?
    $objectvar = (object) $trigger[$key];
    $arrayoftrigger[$objectvar->sortindex -1] = $objectvar;
    asort($arrayoftrigger);
}

$arrayofsteps = array();
foreach ($steps as $key => $step) {
    $stepobject = (object) $steps[$key];
    $ncourses = $DB->count_records('tool_lifecycle_process',
        array('stepindex' => $stepobject->sortindex, 'workflowid' => $workflowid));
    $stepobject->numberofcourses = $ncourses;
    $arrayofsteps[$stepobject->sortindex -1] = $stepobject;
}
asort($arrayofsteps);

$arrayofcourses = array();
if ($stepid) {

    $listofcourses = $DB->get_records_sql("SELECT p.id as processid, c.id as courseid, c.fullname as coursefullname, " .
        "c.shortname as courseshortname, s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname " .
        "FROM {tool_lifecycle_process} p join " .
        "{course} c on p.courseid = c.id join " .
        "{tool_lifecycle_step} s ".
        "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex " .
        "WHERE p.stepindex = :stepindex AND p.workflowid = :wfid;", array('stepindex' => $stepid, 'wfid' => $workflowid));
    // WHERE p.stepindex = :stepindex p.workflowid = wfid
/*    $sql = "SELECT c.shortname, c.fullname FROM {course} c INNER JOIN {tool_lifecycle_process} lp
                     ON c.id = lp.courseid WHERE lp.stepindex = :stepindex AND lp.workflowid = :wfid";
    $listofcourses = $DB->get_records_sql($sql, array('stepindex' => $stepid, 'wfid' => $workflowid));*/
    $listofids = array();
    foreach ($listofcourses as $key => $value) {
        $listofids = $value->courseid;
        $objectvar = (object) $listofcourses[$key];
        array_push($arrayofcourses, $objectvar);
    }
    asort($arrayofcourses);
    $table = new interaction_attention_table('tool_lifecycle_interaction', $listofids);
}
$url = new moodle_url("/admin/tool/lifecycle/workflowoverview.php", array('wf' => $workflowid));
//$table->finish_output();
$data = [
    'trigger' => $arrayoftrigger,
    'steps' => $arrayofsteps,
    'listofcourses' => $arrayofcourses,
    'steplink' => $url
    //'table' => $table->finish_output()
];
// array(1) { [4]=> object(stdClass)#7131 (6) { ["id"]=> string(1) "4" ["courseid"]=> string(1) "3" ["workflowid"]=> string(1) "5" ["stepindex"]=> string(1) "1" ["waiting"]=> string(1) "1" ["timestepchanged"]=> string(10) "1634642650" } }
echo $OUTPUT->render_from_template('tool_lifecycle/workflowoverview', $data);

echo $renderer->footer();
