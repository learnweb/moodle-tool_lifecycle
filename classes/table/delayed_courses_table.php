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
 * Table listing all courses for a specific user and a specific subplugin
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all delayed courses
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delayed_courses_table extends \table_sql {

    /**
     * Constructor for deactivated_workflows_table.

     */
    public function __construct() {
        parent::__construct('tool_lifecycle-delayed-courses');
        $fields = 'c.id as courseid, c.fullname as coursefullname, cat.name as category, dw.workflowid, w.title as workflow, dw.delayeduntil AS workflowdelay, d.delayeduntil AS globaldelay, maxtable.wfcount AS workflowcount';

        $from = '(' .
                'SELECT courseid, MAX(dw.id) AS maxid, COUNT(*) AS wfcount ' .
                'FROM {tool_lifecycle_delayed_workf} dw ' .
                'JOIN {tool_lifecycle_workflow} w ON dw.workflowid = w.id ' . // To make sure no outdated delays are counted.
                'WHERE dw.delayeduntil >= :time ' .
                'AND w.timeactive IS NOT NULL ' .
                // TODO AND dw.workflowid IN $workflows
                'GROUP BY courseid ' .
            ') maxtable ' .
            'JOIN {tool_lifecycle_delayed_workf} dw ON maxtable.maxid = dw.id ' .
            'JOIN {tool_lifecycle_workflow} w ON dw.workflowid = w.id ' .
            'FULL JOIN  {tool_lifecycle_delayed} d ON dw.courseid = d.courseid ' .
            'JOIN {course} c ON c.id = COALESCE(dw.courseid, d.courseid) ' .
            'JOIN {course_categories} cat ON c.category = cat.id';
        $where = 'true';
        $params = ['time' => time()];

        $this->set_sql($fields, $from, $where, $params);
        $this->column_nosort = ['workflow', 'tools'];
        $this->define_columns(['coursefullname', 'category', 'workflow', 'tools']);
        $this->define_headers([
                get_string('coursename', 'tool_lifecycle'),
                get_string('category'),
                get_string('delays', 'tool_lifecycle'),
                get_string('tools', 'tool_lifecycle')
        ]);
    }

    public function col_workflow($row) {
        if($row->globaldelay >= time()) {
            if ($row->workflowcount == 1) {
                $text = get_string('delayed_globally_and_seperately_for_one', 'tool_lifecycle');
            } else if ($row->workflowcount > 1) {
                $text = get_string('delayed_globally_and_seperately', 'tool_lifecycle', $row->workflowcount);
            } else {
                $dateformat = get_string('strftimedatetimeshort', 'core_langconfig');
                $date = userdate($row->globaldelay, $dateformat);
                $text = get_string('delayed_globally', 'tool_lifecycle', $date);
            }
        } else {
            if ($row->workflowcount <= 0) {
                $text = '';
            } else if($row->workflowcount == 1) {
                $dateformat = get_string('strftimedatetimeshort', 'core_langconfig');
                $date = userdate($row->workflowdelay, $dateformat);
                $text = get_string('delayed_for_workflow_until', 'tool_lifecycle',
                        array('name' => $row->workflow, 'date' => $date));
            } else {
                $text = get_string('delayed_for_workflows', 'tool_lifecycle', $row->workflowcount);
            }
        }

        return \html_writer::start_span('tool_lifecycle-hint', array('title' => $this->get_mouseover($row))) .
                $text .
                \html_writer::end_span();
    }

    private function get_mouseover($row) {
        global $DB;
        $text = '';
        $dateformat = get_string('strftimedatetimeshort', 'core_langconfig');
        if($row->globaldelay >= time()) {
            $date = userdate($row->globaldelay, $dateformat);
            $text .= get_string('globally_until_date', 'tool_lifecycle', $date) . '&#13;';
        }
        if($row->workflowcount == 1) {
            $date = userdate($row->workflowdelay, $dateformat);
            $text .= get_string('name_until_date', 'tool_lifecycle',
                    array('name' => $row->workflow, 'date' => $date)) . '&#13;';
        } else if ($row->workflowcount > 1) {
            $sql = 'SELECT dw.id, dw.delayeduntil, w.title
                    FROM {tool_lifecycle_delayed_workf} dw
                    JOIN {tool_lifecycle_workflow} w ON dw.workflowid = w.id
                    WHERE dw.courseid = :courseid';
            $records = $DB->get_records_sql($sql, ['courseid' => $row->courseid]);
            foreach ($records as $record) {
                $date = userdate($record->delayeduntil, $dateformat);
                $text .= get_string('name_until_date', 'tool_lifecycle',
                        array('name' => $record->title, 'date' => $date)) . '&#13;';
            }
        }
        return $text;
    }

    /**
     * Render tools column.
     * @param object $row Row data.
     * @return string pluginname of the subplugin
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     */
    public function col_tools($row) {
        global $PAGE, $OUTPUT;
        $button = new \single_button(
                new \moodle_url($PAGE->url,
                        array('action' => 'delete', 'cid' => $row->courseid, 'sesskey' => sesskey())),
                get_string('delete_delay', 'tool_lifecycle'));
        return $OUTPUT->render($button);
    }
}