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
 * Table listing of all courses that are already in a process of the workflow.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use core_date;
use stdClass;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\urls;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing of all courses that are already in a process of the workflow.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_in_process_table extends \table_sql {

    /** @var int $workflowid Id of the workflow */
    private $workflowid;

    /** @var int $tablerows number of table rows effectively written */
    public $tablerows = 0;

    /**
     * Builds a table of courses.
     * @param workflow $workflow of which the courses are listed
     * @param string $filterdata optional, term to filter the table by course id or -name
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($workflow, $filterdata = '') {
        parent::__construct('tool_lifecycle-courses-in-process');
        global $PAGE;

        $this->define_baseurl($PAGE->url);
        $this->workflowid = $workflow->id;

        $a = new \stdClass();
        $a->title = $workflow->title;
        $this->caption = get_string('coursesinprocess', 'tool_lifecycle', $a);
        $this->captionattributes = ['class' => 'ml-3'];

        $columns = ['courseid', 'coursefullname', 'coursecategory', 'processtype'];
        $this->define_columns($columns);
        $headers = [
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
            get_string('step', 'tool_lifecycle')."/".get_string('error'),
        ];
        $this->define_headers($headers);

        $fields = " c.id as courseid,
                    c.fullname as coursefullname,
                    c.shortname as courseshortname,
                    cc.name as coursecategory,
                    pe.id as errorid,
                    s.instancename as stepname";
        $from = " {course} c LEFT JOIN {course_categories} cc ON c.category = cc.id
                    LEFT JOIN {tool_lifecycle_process} p ON c.id = p.courseid AND p.workflowid = $workflow->id
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON c.id = pe.courseid AND pe.workflowid = $workflow->id
                    LEFT JOIN {tool_lifecycle_step} s ON p.stepindex = s.sortindex AND s.workflowid = $workflow->id
                    ";

        $where = " p.workflowid IS NOT NULL OR pe.workflowid IS NOT NULL ";

        if (!$workflow->includesitecourse) {
            $where = "($where) AND c.id <> 1 ";
        }

        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where = "($where) AND c.id = $filterdata ";
            } else {
                $where = "($where) AND (c.fullname LIKE '%$filterdata%' OR c.shortname LIKE '%$filterdata%')";
            }
        }

        $this->set_sql($fields, $from, $where, []);
    }

    /**
     * Build the table from the fetched data.
     *
     * Take the data returned from the db_query and go through all the rows
     * processing each col using either col_{columnname} method or other_cols
     * method or if other_cols return NULL, then put the data straight into the
     * table.
     *
     * After calling this function, remember to call close_recordset.
     */
    public function build_table() {
        if (!$this->rawdata) {
            return;
        }
        foreach ($this->rawdata as $row) {
            $formattedrow = $this->format_row($row);
            $this->add_data_keyed($formattedrow, $this->get_row_class($row));
            $this->tablerows++;
        }
    }

    /**
     * Render coursefullname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_coursefullname($row) {
        $courselink = \html_writer::link(course_get_url($row->courseid),
            format_string($row->coursefullname), ['target' => '_blank']);
        return $courselink . '<br><span class="secondary-info">' . $row->courseshortname . '</span>';
    }

    /**
     * Render processtype column.
     *
     * @param object $row Row data.
     * @return string of link to processerror-page or string 'step'
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_processtype($row) {
        if ($row->errorid) {
            $params = [
                'workflow' => $this->workflowid,
                'course' => $row->courseid,
            ];
            return \html_writer::link(
                new \moodle_url(urls::PROCESS_ERRORS, $params),
                get_string('process_error', 'tool_lifecycle'),
                ['class' => 'error']);
        } else {
            if ($row->stepname) {
                return $row->stepname;
            } else {
                return get_string('step', 'tool_lifecycle');
            }
        }
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_finish() {
        echo \html_writer::div(get_string('total')." ".get_string('page').": ".$this->tablerows." ".
            get_string('courses'), 'm-3');
    }

    /**
     * Prints a customized "nothing to display" message.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;
        echo \html_writer::div($OUTPUT->notification(get_string('nothingtodisplay', 'moodle'), 'info'),
            'm-3');
        echo \html_writer::div("&nbsp;&nbsp;&nbsp;".\html_writer::link(new \moodle_url(urls::WORKFLOW_DETAILS,
                ["wf" => $this->workflowid, "showsql" => "1", "showtablesql" => "1", "showdetails" => "1"]),
                "&nbsp;&nbsp;&nbsp;", ["class" => "text-muted fs-6 text-decoration-none"]));
    }
}
