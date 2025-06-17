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
 * A moodle form for filtering the process errors table
 *
 * @package    tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\form;

use tool_lifecycle\local\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * A moodle form for filtering the process errors table
 *
 * @package    tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_errors_filter extends \moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $workflow = $this->_customdata['workflow'];
        $course = $this->_customdata['course'];
        $step = $this->_customdata['step'];

        // Get distinct workflows with process errors and populate workflow filter with them.
        $sql = "select DISTINCT(wf.id), wf.title from {tool_lifecycle_workflow} wf where wf.id in
            (select w.id from {tool_lifecycle_proc_error} pe
                JOIN {tool_lifecycle_workflow} w ON pe.workflowid = w.id
                JOIN {tool_lifecycle_step} s ON pe.workflowid = s.workflowid AND pe.stepindex = s.sortindex
                LEFT JOIN {course} c ON pe.courseid = c.id)";
        $workflows = $DB->get_records_sql($sql);
        $workflowoptions[''] = get_string('choose').'...';
        foreach ($workflows as $wf) {
            $workflowoptions[$wf->id] = $wf->title;
        }
        $mform->addElement('select', 'workflow', get_string('workflow', 'tool_lifecycle'), $workflowoptions);

        // Get distinct workflow steps with process errors and populate step filter with them.
        // If workflow filter is active use it here as well.
        $sql = "SELECT DISTINCT s.id, s.instancename from {tool_lifecycle_proc_error} pe
            JOIN {tool_lifecycle_workflow} w ON pe.workflowid = w.id
            JOIN {tool_lifecycle_step} s ON pe.workflowid = s.workflowid AND pe.stepindex = s.sortindex
            JOIN {course} c ON pe.courseid = c.id";
        $params = [];
        if ($workflow) {
            $sql .= " and pe.workflowid = :workflow";
            $params["workflow"] = $workflow;
        }
        $steps = $DB->get_records_sql($sql, $params);
        $stepsoptions[''] = get_string('choose').'...';
        foreach ($steps as $step) {
            $stepsoptions[$step->id] = $step->instancename;
        }
        $mform->addElement('select', 'step', get_string('step', 'tool_lifecycle'), $stepsoptions);

        // Get distinct courses with process errors and populate courses filter with them.
        // If workflow filter is active use it here as well.
        $sql = "SELECT DISTINCT c.id, c.fullname from {tool_lifecycle_proc_error} pe
            JOIN {tool_lifecycle_workflow} w ON pe.workflowid = w.id
            JOIN {tool_lifecycle_step} s ON pe.workflowid = s.workflowid AND pe.stepindex = s.sortindex
            JOIN {course} c ON pe.courseid = c.id";
        $params = [];
        if ($workflow) {
            $sql .= " and pe.workflowid = :workflow";
            $params["workflow"] = $workflow;
        }
        $courses = $DB->get_records_sql($sql, $params);
        $coursesoptions[''] = get_string('choose').'...';
        foreach ($courses as $course) {
            $coursesoptions[$course->id] = $course->fullname;
        }
        $mform->addElement('select', 'course', get_string('course'), $coursesoptions);

        $this->add_action_buttons(true, get_string('apply', 'tool_lifecycle'));
    }

}
