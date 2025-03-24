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
 * Life Cycle Admin Approve Step. Page for specific step.
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login(null, false);

require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('lifecyclestep_adminapprove_manage');

$action = optional_param('act', null, PARAM_ALPHA);
$ids = optional_param_array('c', [], PARAM_INT);
$stepid = required_param('stepid', PARAM_INT);

$step = \tool_lifecycle\local\manager\step_manager::get_step_instance($stepid);
if (!$step) {
    throw new moodle_exception('Stepid does not correspond to any step.');
}
if ($step->subpluginname !== 'adminapprove') {
    throw new moodle_exception('The given step is not a Admin Approve Step.');
}

/**
 * Constant to roll back selected.
 */
/**
 * Constant to roll back all courses.
 */
/**
 * Constant to proceed selected.
 */
/**
 * Constant to proceed all courses.
 */
const ROLLBACK = 'rollback', ROLLBACK_ALL = 'rollbackall', PROCEED = 'proceed', PROCEED_ALL = 'proceedall';

$workflow = \tool_lifecycle\local\manager\workflow_manager::get_workflow($step->workflowid);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new \moodle_url("/admin/tool/lifecycle/step/adminapprove/approvestep.php?stepid=$stepid"));
$PAGE->navbar->add($step->instancename, $PAGE->url);

$mformdata = cache::make('lifecyclestep_adminapprove', 'mformdata');

$mform = new \lifecyclestep_adminapprove\course_filter_form($PAGE->url->out());
if ($mform->is_cancelled()) {
    $mformdata->delete('data');
    redirect($PAGE->url);
}

$courseid = null;
$category = null;
$coursename = null;

if ($mformdata->has('data')) {
    $data = $mformdata->get('data');
    $courseid = $data->courseid;
    $category = $data->category;
    $coursename = $data->coursename;
    $mform->set_data($data);
}

if ($mform->is_validated()) {
    $data = $mform->get_data();
    $courseid = $data->courseid;
    $category = $data->category;
    $coursename = $data->coursename;
    $mformdata->set('data', $data);
}

if ($action) {
    require_sesskey();

    if (is_array($ids) && count($ids) > 0 && ($action == PROCEED || $action == ROLLBACK)) {
        list($insql, $inparams) = $DB->get_in_or_equal($ids);
        $sql = 'UPDATE {lifecyclestep_adminapprove} ' .
                'SET status = ' . ($action == PROCEED ? 1 : 2) . ' ' .
                'WHERE id ' . $insql .
                'AND status = 0';
        $DB->execute($sql, $inparams);
    } else if ($action == PROCEED_ALL || $action == ROLLBACK_ALL) {
        $sql = 'SELECT p.id FROM {lifecyclestep_adminapprove} a ' .
                'JOIN {tool_lifecycle_process} p ON p.id = a.processid ' .
                'JOIN {tool_lifecycle_step} s ON s.workflowid = p.workflowid AND s.sortindex = p.stepindex ' .
                'JOIN {course} c ON p.courseid = c.id ' .
                'WHERE s.id = :stepid ';
        $params = ['stepid' => $stepid];

        if ($courseid) {
            $sql .= 'AND c.id = :cid ';
            $params['cid'] = $courseid;
        }
        if ($coursename) {
            $sql .= "AND c.fullname LIKE :cname ";
            $params['cname'] = '%' . $DB->sql_like_escape($coursename) . '%';
        }

        $ids = array_keys($DB->get_records_sql_menu($sql, $params));
        if (!empty($ids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($ids);
            $sql = 'UPDATE {lifecyclestep_adminapprove} ' .
                'SET status = ' . ($action == PROCEED_ALL ? 1 : 2) . ' ' .
                'WHERE status = 0 ' .
                'AND processid ' . $insql;
            $DB->execute($sql, $inparams);
        }
    }
    redirect($PAGE->url);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage-adminapprove', 'lifecyclestep_adminapprove') . ': ' .
        $step->instancename);
echo "<br>";

$hasrecords = $DB->record_exists_sql('SELECT a.id FROM {lifecyclestep_adminapprove} a ' .
        'JOIN {tool_lifecycle_process} p ON p.id = a.processid ' .
        'JOIN {tool_lifecycle_step} s ON s.workflowid = p.workflowid AND s.sortindex = p.stepindex ' .
        'WHERE s.id = :sid AND a.status = 0', ['sid' => $stepid]);

if ($hasrecords) {
    $mform->display();

    echo get_string('courses_waiting', 'lifecyclestep_adminapprove',
            ['step' => $step->instancename, 'workflow' => $workflow->title]);
    echo "<br><br>";
    echo '<form action="" method="post"><input type="hidden" name="sesskey" value="' . sesskey() . '">';

    $table = new lifecyclestep_adminapprove\decision_table($stepid, $courseid, $category, $coursename);
    $table->out(100, false);
    if ($table->totalrows) {
        echo get_string('bulkactions') . ':<br>';
        echo html_writer::start_div('singlebutton');
        echo html_writer::tag('button', get_string('proceedselected', 'lifecyclestep_adminapprove'),
                ['type' => 'submit', 'name' => 'act', 'value' => PROCEED, 'class' => 'btn btn-secondary']);
        echo html_writer::end_div() . html_writer::start_div('singlebutton');
        echo html_writer::tag('button', get_string('rollbackselected', 'lifecyclestep_adminapprove'),
                ['type' => 'submit', 'name' => 'act', 'value' => ROLLBACK, 'class' => 'btn btn-secondary']);
        echo html_writer::end_div();
    }
    echo '</form>';

    echo '<div class="mt-2">';
    $button = new \single_button(new moodle_url($PAGE->url, ['act' => PROCEED_ALL]),
            get_string(PROCEED_ALL, 'lifecyclestep_adminapprove'));
    echo $OUTPUT->render($button);

    $button = new \single_button(new moodle_url($PAGE->url, ['act' => ROLLBACK_ALL]),
            get_string(ROLLBACK_ALL, 'lifecyclestep_adminapprove'));
    echo $OUTPUT->render($button);
    echo '</div>';
    $PAGE->requires->js_call_amd('lifecyclestep_adminapprove/init', 'init', [sesskey(), $PAGE->url->out()]);
} else {
    echo get_string('no_courses_waiting', 'lifecyclestep_adminapprove',
            ['step' => $step->instancename, 'workflow' => $workflow->title]);
}

echo $OUTPUT->footer();
