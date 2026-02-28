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
     * @var int wfid ID of workflow of step instance
     */
    private $wfid;

    /**
     * @var int stepindex step's position in workflow
     */
    private $stepindex;

    /** @var object master checkbox object */
    private $_mastercheckbox;

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

        $rollbackcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['rollbackbuttonlabel'] ?? null;
        $this->strings['rollbackbuttonlabel'] = !empty($rollbackcustlabel) ?
            $rollbackcustlabel : get_string('rollback', 'lifecyclestep_adminapprove');

        $proceedcustlabel =
            settings_manager::get_settings($stepid, settings_type::STEP)['proceedbuttonlabel'] ?? null;
        $this->strings['proceedbuttonlabel'] = !empty($proceedcustlabel) ?
            $proceedcustlabel : get_string('proceed', 'lifecyclestep_adminapprove');

        $this->_mastercheckbox = new \core\output\checkbox_toggleall('lifecycle-adminapprove-table', true, [
            'id' => 'select-all-ids',
            'name' => 'select-all-ids',
            'label' => get_string('selectall'),
            'labelclasses' => 'sr-only',
            'classes' => 'm-1',
            'checked' => false,
        ]);

        $this->define_columns(['checkbox', 'courseid', 'course', 'category', 'startdate', 'tools']);
        // Set sort column to course id!
        $this->sortable(true, 'courseid');
        $this->column_class('tools', 'text-nowrap');
        global $OUTPUT;
        $this->define_headers(
            [$OUTPUT->render($this->_mastercheckbox),
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

        $where .= '';
        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * Column of checkboxes.
     * @param object $row
     * @return string
     */
    public function col_checkbox($row) {
        global $OUTPUT;

        if (!($this->wfid ?? false)) {
            $this->wfid = $row->wfid;
        }
        if (!($this->stepindex ?? false)) {
            $this->stepindex = $row->stepindex;
        }

        $name = $row->id;

        $checkbox = new \core\output\checkbox_toggleall('lifecycle-adminapprove-table', false, [
            'id' => 'adminapprove_check_' . $name,
            'name' => 'c[]',
            'label' => get_string('selectitem', 'moodle', $row->id),
            'labelclasses' => 'accesshide',
            'classes' => 'm-1',
            'checked' => false,
            'value' => $name,
            'labelfor' => 'adminapprove_check_' . $name,
        ]);

        return $OUTPUT->render($checkbox);
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
        // We use links instead of actual button elements here in order to
        // avoid creating nested forms. Nested forms do not work properly.
        // Note that the whole table is also included in a form element.
        // Anchors result in get requests which is also not a proper approach
        // but it is commonly used throughout moodle for actions in tables.
        $output = \html_writer::tag('a', $this->strings['rollbackbuttonlabel'], [
            'class' => 'btn btn-secondary',
            'href' => (new \moodle_url('', [
                'action' => 'rollback',
                'c[]' => $row->id,
                'stepid' => $row->sid,
                'sesskey' => sesskey(),
            ]))]);
        $output .= ' ' . \html_writer::tag('a', $this->strings['proceedbuttonlabel'], [
            'class' => 'btn btn-primary',
            'href' => (new \moodle_url('', [
                'action' => 'proceed',
                'c[]' => $row->id,
                'stepid' => $row->sid,
                'sesskey' => sesskey(),
            ]))]);
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
     * add link for showing complete table
     * @param int $pagesize
     * @param boolean $useinitialsbar
     * @param string $downloadhelpbutton
     * @return void
     * @throws \coding_exception
     * @throws moodle_exception
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        if ($pagesize < 1) {
            $pagesize = $this->get_default_per_page();
        }
        parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);

        // Generate "Show all/Show per page" link.
        if ($this->pagesize == TABLE_SHOW_ALL_PAGE_SIZE && $this->totalrows > $this->get_default_per_page()) {
            $perpagesize = $this->get_default_per_page();
            $perpagestring = get_string('showperpage', '', $this->get_default_per_page());
        } else if ($this->pagesize < $this->totalrows) {
            $perpagesize = TABLE_SHOW_ALL_PAGE_SIZE;
            $perpagestring = get_string('showall', '', $this->totalrows);
        }
        if (isset($perpagesize) && isset($perpagestring)) {
            global $PAGE;
            $perpageurl = new \moodle_url($PAGE->url);
            $perpageurl->remove_params('page'); // Reset page parameter.
            $perpageurl->param('page', 0); // Reset page parameter.
            $perpageurl->param('perpage', $perpagesize);
            echo \html_writer::link(
                $perpageurl,
                $perpagestring);
        }
    }

    /**
     * Get the default per page.
     *
     * @return int
     */
    public function get_default_per_page(): int {
        return 100;
    }
}
