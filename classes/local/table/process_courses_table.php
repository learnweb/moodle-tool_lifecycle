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
 * Table listing all courses of this workflow in an active process or with a process error.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all workflow courses in an active process or with a process error.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_courses_table extends \table_sql {

    /** @var string $type of the courses list */
    private $type;

    /** @var int $workflowid Id of the workflow */
    private $workflowid;

    /**
     * Builds a table of courses.
     * @param array $courseids of the courses to list
     * @param string $workflowname
     * @param null $workflowid
     * @param string $filterdata optional, term to filter the table by course id or -name
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($courseids, $workflowname = '', $workflowid = null, $filterdata = '') {
        parent::__construct('tool_lifecycle-workflow-courses-in-process');
        global $DB, $PAGE;

        if (!$courseids) {
            return;
        }

        $this->define_baseurl($PAGE->url);
        $this->caption = get_string('workflow_processesanderrors', 'tool_lifecycle')." '".$workflowname."' (".count($courseids).")";
         $this->workflowid = $workflowid;
        $this->captionattributes = ['class' => 'ml-3'];
        $columns = ['courseid', 'coursefullname', 'coursecategory', 'step', 'error'];
        $this->define_columns($columns);
        $headers = [
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
            get_string('step', 'tool_lifecycle'),
            get_string('error'),
        ];
        $this->define_headers($headers);

        $fields = "c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname,
            cc.name as coursecategory, s.instancename as step, pe.errormessage as errormessage, pe.errortrace as errortrace";
        $from = "
            {course} c
            JOIN {course_categories} cc ON c.category = cc.id
            LEFT JOIN {tool_lifecycle_process} p ON p.courseid = c.id AND p.workflowid = $workflowid
            LEFT JOIN {tool_lifecycle_proc_error} pe ON pe.courseid = c.id AND pe.workflowid = $workflowid
            JOIN {tool_lifecycle_step} s ON (p.workflowid = s.workflowid AND p.stepindex = s.sortindex)
            OR (pe.workflowid = s.workflowid AND pe.stepindex = s.sortindex)
            ";
        [$insql, $inparams] = $DB->get_in_or_equal($courseids);
        $where = "c.id ".$insql;

        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where = " c.id = $filterdata ";
            } else {
                $where = $where . " AND ( c.fullname LIKE '%$filterdata%' OR c.shortname LIKE '%$filterdata%')";
            }
        }

        $this->set_sql($fields, $from, $where, $inparams);
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
     * Render error column.
     *
     * @param object $row Row data.
     * @return string error cell
     */
    public function col_error($row) {
        if ($row->errormessage) {
            return "<details><summary>" .
                nl2br(htmlentities($row->errormessage, ENT_COMPAT)) .
                "</summary><code>" .
                nl2br(htmlentities($row->errortrace, ENT_COMPAT)) .
                "</code></details>";
        } else {
            return "---";
        }
    }

    /**
     * Prints a customized "nothing to display" message.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;
        echo \html_writer::div($OUTPUT->notification(get_string('nothingtodisplay', 'moodle'), 'info'),
            'm-3');
    }
}
