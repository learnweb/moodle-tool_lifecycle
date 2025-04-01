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
 * Table listing all courses triggered by a trigger.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\process;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses triggered by a trigger.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class triggered_courses_table extends \table_sql {

    /**
     * Builds a table of courses.
     * @param array $courseids of the courses to list
     * @param string $type of list: triggered, delayed, excluded
     * @param string $triggername optional, if type triggered
     * @param string $workflowname optional, if type delayed
     * @param int $workflowid optional, if type delayed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($courseids, $type, $triggername = '', $workflowname = '', $workflowid = null, $filterdata = '') {
        parent::__construct('tool_lifecycle-courses-in-trigger');
        global $DB, $PAGE;

        if (!$courseids) {
            return;
        }

        $this->define_baseurl($PAGE->url);
        if ($type == 'triggered') {
            $this->caption = get_string('coursestriggered', 'tool_lifecycle', $triggername)." (".count($courseids).")";
        } else if ($type == 'delayed') {
            $this->caption = get_string('coursesdelayed', 'tool_lifecycle', $workflowname)." (".count($courseids).")";
        } else {
            $this->caption = get_string('coursesexcluded', 'tool_lifecycle', $triggername)." (".count($courseids).")";
        }
        $this->captionattributes = ['class' => 'ml-3'];
        $columns = ['courseid', 'coursefullname', 'coursecategory'];
        if ($type == 'delayed') {
            $columns[] = 'delayeduntil';
        }
        $this->define_columns($columns);
        $headers = [
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
        ];
        if ($type == 'delayed') {
            $headers[] = get_string('delayeduntil', 'tool_lifecycle');
        }
        $this->define_headers($headers);

        $fields = "c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname, cc.name as coursecategory";
        if ($type == 'delayed') {
            $fields .= ", dwf.delayeduntil as delayeduntil";
        }
        $from = "{course} c INNER JOIN {course_categories} cc ON c.category = cc.id";
        if ($type == 'delayed') {
            $from .= " INNER JOIN {tool_lifecycle_delayed_workf} dwf ON c.id = dwf.courseid";
        }
        [$insql, $inparams] = $DB->get_in_or_equal($courseids);
        $where = "c.id ".$insql;
        if ($type == 'delayed') {
            $from .= " AND dwf.workflowid = $workflowid";
        }

        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where = " c.id = $filterdata ";
            } else {
                $where = $where . " AND ( c.fullname LIKE '%$filterdata%' OR c.shortname LIKE '%$filterdata%')";
            }
        }

        $this->set_sql($fields, $from, $where, $inparams);
        $this->set_sortdata([['sortby' => 'fullname', 'sortorder' => '1']]);
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
     * Render delayeduntil column.
     * @param object $row Row data.
     * @return string date
     * @throws \coding_exception
     */
    public function col_delayeduntil($row) {
        $delayeduntil = userdate($row->delayeduntil, get_string('strftimedatetime', 'core_langconfig'));
        return $delayeduntil;
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
