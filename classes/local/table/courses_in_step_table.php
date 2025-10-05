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
 * Table listing all courses for a specific step.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use core_date;
use tool_lifecycle\local\entity\step_subplugin;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses for a specific step.
 *
 * @package tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_in_step_table extends \table_sql {

    /** @var int|null if set, it is the courseid to focus on. */
    private $courseid;

    /** @var string search string to filter courses list */
    private $search = "";

    /**
     * Constructor for courses_in_step_table.
     * @param step_subplugin $step step to show courses of
     * @param int|null $courseid if supplied, courseid to focus on
     * @param int $ncourses number of displayed courses
     * @param string $filterdata optional term to filter table by course id or -name
     * @throws \coding_exception
     */
    public function __construct($step, $courseid, $ncourses = 0, $filterdata = '') {
        parent::__construct('tool_lifecycle-courses-in-step');
        global $PAGE;

        $this->courseid = $courseid;

        if (!$courseid ?? false && $ncourses > 0) {
            $this->caption = get_string('coursesinstep', 'tool_lifecycle', $step->instancename)." ($ncourses)";
        } else if ($courseid) {
            $this->caption = get_string('coursesinstep', 'tool_lifecycle', $step->instancename)." (1)";
        }
        $this->captionattributes = ['class' => 'ml-2'];

        $this->define_baseurl($PAGE->url);
        $this->define_columns(['courseid', 'coursefullname', 'startdate', 'tools']);
        $this->define_headers([
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('startdate'),
            get_string('tools', 'tool_lifecycle'),
        ]);

        $fields = "p.id as processid, c.id as courseid, c.fullname as coursefullname, c.startdate, " .
            "c.shortname as courseshortname, s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname";
        $from = "{tool_lifecycle_process} p inner join " .
        "{course} c on p.courseid = c.id inner join " .
        "{tool_lifecycle_step} s ".
        "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex ";

        $where = "p.stepindex = :stepindex AND p.workflowid = :wfid";

        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where = " AND c.id = $filterdata ";
            } else {
                $where = $where . " AND ( c.fullname LIKE '%$filterdata%' OR c.shortname LIKE '%$filterdata%')";
            }
            $this->search = $filterdata;
        }

        $this->column_nosort = ['status', 'tools'];
        $this->set_sql($fields, $from, $where, ['stepindex' => $step->sortindex, 'wfid' => $step->workflowid]);
        if ($courseid) {
            $this->set_sortdata([]);
        }
    }

    /**
     * Sets the page number to the page where the courseid is located.
     * @param int $pagesize pagesize, items per page.
     */
    public function jump_to_course($pagesize) {
        global $DB;
        $params = $this->sql->params;
        $params['courseid'] = $this->courseid;
        $sql = "SELECT COUNT(*) FROM {$this->sql->from} WHERE {$this->sql->where} AND c.id < :courseid";
        $count = $DB->count_records_sql($sql, $params);
        $this->set_page_number(intval(ceil(($count + 1) / $pagesize)));
    }

    /**
     * Query the db. Store results in the table object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar. Bar
     * will only be used if there is a fullname column defined for the table.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        if ($this->courseid) {
            $this->jump_to_course($pagesize);
        }
        parent::query_db($pagesize, $useinitialsbar);
    }

    /**
     * Get any extra classes names to add to this row in the HTML.
     * @param object $row the data for this row.
     * @return string added to the class="" attribute of the tr.
     */
    public function get_row_class($row) {
        if ($row->courseid == $this->courseid) {
            return 'table-primary';
        }
        return '';
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
     * Render startdate column.
     * @param object $row Row data.
     * @return string human readable date
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
     * Render tools column.
     * @param object $row Row data.
     * @return string pluginname of the subplugin
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     */
    public function col_tools($row) {
        global $OUTPUT, $PAGE;
        $output = $OUTPUT->single_button(
            new \moodle_url($PAGE->url, ['action' => 'rollback', 'processid' => $row->processid,
                'sesskey' => sesskey(), 'search' => $this->search]),
            get_string('rollback', 'tool_lifecycle'),
            'post',
            ['class' => 'mr-1']
        );
        $output .= $OUTPUT->single_button(
            new \moodle_url($PAGE->url, ['action' => 'proceed', 'processid' => $row->processid,
                'sesskey' => sesskey(), 'search' => $this->search]),
            get_string('proceed', 'tool_lifecycle'),
            'post',
            ['class' => 'mt-1']
        );
        return $output;
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
