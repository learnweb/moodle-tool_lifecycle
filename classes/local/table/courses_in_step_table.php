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
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\step_manager;

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

    /**
     * Constructor for courses_in_step_table.
     * @param step_subplugin $step Id of the step.
     */
    public function __construct($step) {
        parent::__construct('tool_lifecycle-courses-in-step');
        global $PAGE;

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
        $from = "{tool_lifecycle_process} p join " .
        "{course} c on p.courseid = c.id join " .
        "{tool_lifecycle_step} s ".
        "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex ";

        $where = "p.stepindex = :stepindex AND p.workflowid = :wfid";

        $this->column_nosort = array('status', 'tools');
        $this->set_sql($fields, $from, $where, ['stepindex' => $step->sortindex, 'wfid' => $step->workflowid]);
    }

    /**
     * Render coursefullname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_coursefullname($row) {
        $courselink = \html_writer::link(course_get_url($row->courseid), format_string($row->coursefullname));
        return $courselink . '<br><span class="secondary-info">' . $row->courseshortname . '</span>';
    }

    /**
     * Render startdate column.
     * @param object $row Row data.
     * @return string human readable date
     * @throws \coding_exception
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
     * Render tools column.
     * @param object $row Row data.
     * @return string pluginname of the subplugin
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     */
    public function col_tools($row) {
        global $OUTPUT, $PAGE;
        $output = '';
        $output .= $OUTPUT->single_button(new \moodle_url($PAGE->url,
            ['action' => 'rollback', 'processid' => $row->processid, 'sesskey' => sesskey()]),
            get_string('rollback', 'tool_lifecycle')
        );
        $output .= $OUTPUT->single_button(new \moodle_url($PAGE->url,
            ['action' => 'proceed', 'processid' => $row->processid, 'sesskey' => sesskey()]),
            get_string('proceed', 'tool_lifecycle')
        );
        return $output;
    }
}
