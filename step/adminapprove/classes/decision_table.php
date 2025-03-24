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
 * Life Cycle Admin Approve Step
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_adminapprove;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Table to show to be processed courses.
 */
class decision_table extends \table_sql {

    /**
     * @var coursecategory
     */
    private $category;

    /**
     * @var string pattern for the coursename.
     */
    private $coursename;


    /**
     * Constructs the table.
     * @param int $stepid
     * @param int $courseid
     * @param int $category
     * @param String $coursename
     * @throws \coding_exception
     */
    public function __construct($stepid, $courseid, $category, $coursename) {
        parent::__construct('lifecyclestep_adminapprove-decisiontable');
        $this->category = $category;
        $this->coursename = $coursename;
        $this->define_baseurl("/admin/tool/lifecycle/step/adminapprove/approvestep.php?stepid=$stepid");
        $this->define_columns(['checkbox', 'courseid', 'course', 'category', 'startdate', 'tools']);
        $this->define_headers(
                [\html_writer::checkbox('checkall', null, false),
                        get_string('courseid', 'lifecyclestep_adminapprove'),
                        get_string('course'),
                        get_string('category'),
                        get_string('startdate'),
                        get_string('tools', 'lifecyclestep_adminapprove')]);
        $this->column_nosort = ['checkbox', 'tools'];
        $fields = 'm.id, w.displaytitle as workflow, c.id as courseid, c.fullname as course, cc.name as category,
            c.startdate, m.status';
        $from = '{lifecyclestep_adminapprove} m ' .
                'LEFT JOIN {tool_lifecycle_process} p ON p.id = m.processid ' .
                'LEFT JOIN {course} c ON c.id = p.courseid ' .
                'LEFT JOIN {course_categories} cc ON c.category = cc.id ' .
                'LEFT JOIN {tool_lifecycle_workflow} w ON w.id = p.workflowid ' .
                'LEFT JOIN {tool_lifecycle_step} s ON s.workflowid = p.workflowid AND s.sortindex = p.stepindex';
        $where = 'm.status = 0 AND s.id = :sid ';
        $params = ['sid' => $stepid];
        if ($courseid) {
            $where .= 'AND c.id = :cid ';
            $params['cid'] = $courseid;
        }
        if ($category) {
            $where .= 'AND cc.id = :catid ';
            $params['catid'] = $category;
        }
        if ($coursename) {
            global $DB;
            $where .= "AND c.fullname LIKE :cname ";
            $params['cname'] = '%' . $DB->sql_like_escape($coursename) . '%';
        }
        $this->set_sql($fields, $from, $where, $params);
    }


    /**
     * Column of checkboxes.
     * @param object $row
     * @return string
     */
    public function col_checkbox($row) {
        return \html_writer::checkbox('c[]', $row->id, false);
    }

    /**
     * Column for the course id.
     * Render courseid column.
     * @param object $row
     * @return string course link
     */
    public function col_courseid($row) {
        return \html_writer::link(course_get_url($row->courseid), $row->courseid);
    }

    /**
     * Render coursefullname column.
     * @param object $row
     * @return string course link
     */
    public function col_course($row) {
        return \html_writer::link(course_get_url($row->courseid), format_string($row->course));
    }

    /**
     * Render coursecategory column.
     * @param object $row
     * @return string course category
     */
    public function col_category($row) {
        return format_string($row->category);
    }

    /**
     * Render startdate column.
     * @param object $row
     * @return string human readable date
     */
    public function col_startdate($row) {
        if ($row->startdate) {
            $dateformat = get_string('strftimedate', 'langconfig');
            return userdate($row->startdate, $dateformat);
        } else {
            return "-";
        }
    }

    /**
     * Show the availble tool/actions for a column.
     * @param object $row
     * @return string
     * @throws \coding_exception
     */
    public function col_tools($row) {
        $output = \html_writer::start_div('singlebutton mr-1');
        $output .= \html_writer::tag('button', get_string('proceed', 'lifecyclestep_adminapprove'),
                ['class' => 'btn btn-secondary adminapprove-action', 'data-action' => 'proceed', 'data-content' => $row->id,
                        'type' => 'button']);
        $output .= \html_writer::end_div();
        $output .= \html_writer::start_div('singlebutton mr-1 ml-0 mt-1');
        $output .= \html_writer::tag('button', get_string('rollback', 'lifecyclestep_adminapprove'),
                ['class' => 'btn btn-secondary adminapprove-action', 'data-action' => 'rollback', 'data-content' => $row->id,
                        'type' => 'button']);
        $output .= \html_writer::end_div();
        return $output;
    }

    /**
     * Print statement if the table is empty.
     * @return void
     * @throws \coding_exception
     */
    public function print_nothing_to_display() {
        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();
        $this->print_initials_bar();
        echo get_string('nothingtodisplay', 'lifecyclestep_adminapprove');
    }
}
