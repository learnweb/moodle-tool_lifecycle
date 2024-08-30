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
 * Table listing active processes
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use tool_lifecycle\urls;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing active processes
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class active_processes_table extends \table_sql {

    /**
     * Constructor for active_processes_table.
     * @param int $uniqueid Unique id of this table.
     * @param \stdClass|null $filterdata
     */
    public function __construct($uniqueid, $filterdata) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;
        $this->set_attribute('class', $this->attributes['class'] . ' lifecycle-table ' . $uniqueid);

        $where = ['TRUE'];
        $params = [];

        if ($filterdata) {
            if ($filterdata->shortname) {
                $where[] = $DB->sql_like('c.shortname', ':shortname', false, false);
                $params['shortname'] = '%' . $DB->sql_like_escape($filterdata->shortname) . '%';
            }

            if ($filterdata->fullname) {
                $where[] = $DB->sql_like('c.fullname', ':fullname', false, false);
                $params['fullname'] = '%' . $DB->sql_like_escape($filterdata->fullname) . '%';
            }

            if ($filterdata->courseid) {
                $where[] = 'c.courseid = :courseid';
                $params['courseid'] = $filterdata->courseid;
            }
        }

        $this->set_sql('c.id as courseid, ' .
            'c.fullname as coursefullname, ' .
            'c.shortname as courseshortname, ' .
            'instancename as instancename, ' .
            's.id as stepid, ' .
            'w.title as workflow, ' .
            'w.displaytitle as wfdisplaytitle, ' .
            'w.id as wfid ',
            '{tool_lifecycle_process} p ' .
            'JOIN {course} c ON p.courseid = c.id ' .
            'JOIN {tool_lifecycle_step} s ON p.workflowid = s.workflowid AND p.stepindex = s.sortindex ' .
            'JOIN {tool_lifecycle_workflow} w ON p.workflowid = w.id',
            join(' AND ', $where), $params);
        $this->define_baseurl($PAGE->url);
        $this->define_columns(['courseid', 'courseshortname', 'coursefullname', 'workflow', 'instancename', 'tools']);
        $this->define_headers([
            get_string('course'),
            get_string('shortnamecourse'),
            get_string('fullnamecourse'),
            get_string('workflow', 'tool_lifecycle'),
            get_string('step', 'tool_lifecycle'),
            get_string('tools', 'tool_lifecycle'), ]);

        $this->column_nosort = ['tools'];
    }

    /**
     * Render courseid column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_courseid($row) {
        return \html_writer::link(course_get_url($row->courseid), $row->courseid);
    }

    /**
     * Render courseshortname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_courseshortname($row) {
        return \html_writer::link(course_get_url($row->courseid), $row->courseshortname);
    }

    /**
     * Render coursefullname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_coursefullname($row) {
        return \html_writer::link(course_get_url($row->courseid), format_string($row->coursefullname));
    }

    /**
     * Render workflow column.
     * @param object $row Row data.
     * @return string Workflow title
     */
    public function col_workflow($row) {
        return $row->workflow . '<br><span class="workflow_displaytitle">' . $row->wfdisplaytitle . '</span>';
    }

    /**
     * Render tools column.
     * @param object $row Row data.
     * @return string Tools.
     */
    public function col_tools($row) {
        return \html_writer::link(new \moodle_url(urls::WORKFLOW_DETAILS,
            ['wf' => $row->wfid, 'courseid' => $row->courseid, 'step' => $row->stepid]
        ), get_string('see_in_workflow', 'tool_lifecycle'), ['class' => 'btn btn-secondary']);
    }
}
