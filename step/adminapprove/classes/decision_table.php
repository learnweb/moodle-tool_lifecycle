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

use core\exception\coding_exception;
use core\exception\moodle_exception;
use core\output\single_button;
use core_date;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Table to show to be processed courses.
 */
class decision_table extends \table_sql {

    /**
     * @var array "cached" lang strings
     */
    private $strings;

    /**
     * @var int stepid ID of step instance
     */
    private $stepid;

    /**
     * @var int wfid ID of workflow of step instance
     */
    private $wfid;

    /**
     * @var int stepindex step's position in workflow
     */
    private $stepindex;

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

        $this->stepid = $stepid;

        $rollbackcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['rollbackbuttonlabel'] ?? null;
        $this->strings['rollbackbuttonlabel'] = !empty($rollbackcustlabel) ?
            $rollbackcustlabel : get_string('rollback', 'lifecyclestep_adminapprove');

        $proceedcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['proceedbuttonlabel'] ?? null;
        $this->strings['proceedbuttonlabel'] = !empty($proceedcustlabel) ?
            $proceedcustlabel : get_string('proceed', 'lifecyclestep_adminapprove');

        $rollbackselectedcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['rollbackselectedbuttonlabel'] ?? null;
        $this->strings['rollbackselectedbuttonlabel'] = !empty($rollbackselectedcustlabel) ?
            $rollbackselectedcustlabel : get_string('rollbackselected', 'lifecyclestep_adminapprove');

        $proceedselectedcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['proceedselectedbuttonlabel'] ?? null;
        $this->strings['proceedselectedbuttonlabel'] = !empty($proceedselectedcustlabel) ?
            $proceedselectedcustlabel : get_string('proceedselected', 'lifecyclestep_adminapprove');

        $rollbackallcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['rollbackallbuttonlabel'] ?? null;
        $this->strings['rollbackallbuttonlabel'] = !empty($rollbackallcustlabel) ?
            $rollbackallcustlabel : get_string('rollbackall', 'lifecyclestep_adminapprove');

        $proceedallcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['proceedallbuttonlabel'] ?? null;
        $this->strings['proceedallbuttonlabel'] = !empty($proceedallcustlabel) ?
            $proceedallcustlabel : get_string('proceedall', 'lifecyclestep_adminapprove');

        $this->define_baseurl("/admin/tool/lifecycle/step/adminapprove/approvestep.php?stepid=$stepid");
        $this->define_columns(['checkbox', 'courseid', 'course', 'category', 'startdate', 'tools']);
        $this->column_class('tools', 'text-nowrap');
        $this->define_headers(
                [\html_writer::checkbox('checkall', null, false),
                        get_string('courseid', 'lifecyclestep_adminapprove'),
                        get_string('course'),
                        get_string('category'),
                        get_string('startdate'),
                        get_string('tools', 'lifecyclestep_adminapprove')]);
        $this->column_nosort = ['checkbox', 'tools'];
        $fields = 'm.id, w.displaytitle as workflow, c.id as courseid, c.fullname as course, cc.name as category, s.id as sid,
            c.startdate, m.status, p.workflowid as wfid, p.stepindex';
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
        if (!($this->wfid ?? false)) {
            $this->wfid = $row->wfid;
        }
        if (!($this->stepindex ?? false)) {
            $this->stepindex = $row->stepindex;
        }
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
     * @return string human-readable date
     * @throws \coding_exception
     */
    public function col_startdate($row) {
        global $USER;

        if ($row->startdate) {
            $dateformat = get_string('strftimedate', 'langconfig');
            return userdate($row->startdate, $dateformat,
                core_date::get_user_timezone($USER));
        } else {
            return "-";
        }
    }

    /**
     * Show the available tool/actions for a column.
     * @param object $row
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function col_tools($row) {
        global $OUTPUT;

        $button = new \single_button(
            new \moodle_url('', [
                'action' => 'rollback',
                'c[]' => $row->id,
                'stepid' => $row->sid,
                'sesskey' => sesskey(),
            ]),
            $this->strings['rollbackbuttonlabel'],
            'post',
            single_button::BUTTON_SECONDARY
        );
        $output = $OUTPUT->render($button);

        $button = new \single_button(
            new \moodle_url('', [
                'action' => 'proceed',
                'c[]' => $row->id,
                'stepid' => $row->sid,
                'sesskey' => sesskey(),
            ]),
            $this->strings['proceedbuttonlabel'],
            'post',
            single_button::BUTTON_PRIMARY
        );
        $output .= $OUTPUT->render($button);

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

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_start() {
        global $OUTPUT;

        parent::wrap_html_start();

        $output = \html_writer::empty_tag('input',
            [
                'type' => 'button',
                'action' => 'rollback',
                'sesskey' => sesskey(),
                'stepid' => $this->stepid,
                'name' => 'button_rollback_selected',
                'value' => $this->strings['rollbackselectedbuttonlabel'],
                'class' => 'selectedbutton btn btn-secondary mb-1',
            ]
        );
        $output .= \html_writer::empty_tag('input',
            [
                'type' => 'button',
                'action' => 'proceed',
                'sesskey' => sesskey(),
                'stepid' => $this->stepid,
                'name' => 'button_proceed_selected',
                'value' => $this->strings['proceedselectedbuttonlabel'],
                'class' => 'selectedbutton btn btn-primary ml-2 mb-1 mr-2',
            ]
        );

        $button = new \single_button(
            new \moodle_url('', [
                'action' => 'rollback_all',
                'stepid' => $this->stepid,
                'stepindex' => $this->stepindex,
                'wfid' => $this->wfid,
                'sesskey' => sesskey(),
            ]),
            $this->strings['rollbackallbuttonlabel'],
            'post',
            single_button::BUTTON_SECONDARY
        );
        $output .= $OUTPUT->render($button);

        $button = new \single_button(
            new \moodle_url('', [
                'action' => 'proceed_all',
                'stepid' => $this->stepid,
                'stepindex' => $this->stepindex,
                'wfid' => $this->wfid,
                'sesskey' => sesskey(),
            ]),
            $this->strings['proceedallbuttonlabel'],
            'post',
            single_button::BUTTON_PRIMARY
        );
        $output .= $OUTPUT->render($button);

        echo $output;

    }
}
