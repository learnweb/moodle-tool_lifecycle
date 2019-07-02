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
 * Table listing past interactions
 *
 * @package tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\table;

use core\plugininfo\format;
use tool_lifecycle\manager\interaction_manager;
use tool_lifecycle\manager\lib_manager;
use tool_lifecycle\manager\step_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class interaction_log_table extends \table_sql {

    public function __construct($uniqueid, $courseids) {
        parent::__construct($uniqueid);
        global $PAGE;

        $fields = "l.id as processid, c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname, " .
                "w.title as workflow, s.subpluginname as subpluginname, s.instancename as stepname, u.id as userid, "
                . get_all_user_name_fields(true, 'u') . ", l.time, l.action";
        $from = '{tool_lifecycle_action_log} l join ' .
                '{course} c on l.courseid = c.id join ' .
                '{tool_lifecycle_workflow} w on l.workflowid = w.id join ' .
                '{tool_lifecycle_step} s on l.workflowid = s.workflowid AND l.stepindex = s.sortindex join ' .
                '{user} u on l.userid = u.id';
        $ids = implode(',', $courseids);

        $where = 'FALSE';
        if ($ids) {
            $where = 'l.courseid IN (' . $ids . ')';
        }

        $this->column_nosort = array('step', 'action');
        $this->set_sql($fields, $from, $where, []);
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialises the columns of the table.
     */
    public function init() {
        $this->define_columns(['coursefullname', 'step', 'action', 'user', 'time']);
        $this->define_headers([
                get_string('course'),
                get_string('step', 'tool_lifecycle'),
                get_string('action', 'tool_lifecycle'),
                get_string('byuser', 'tool_lifecycle'),
                get_string('date'),
        ]);
        $this->setup();
    }

    /**
     * Render course column.
     * @param $row
     * @return string
     */
    public function col_coursefullname($row) {
        $courselink = \html_writer::link(course_get_url($row->courseid), $row->coursefullname);
        return $courselink . '<br><span class="secondary-info">' . $row->courseshortname . '</span>';
    }

    /**
     * Render process step column.
     * @param $row
     * @return string
     */
    public function col_step($row) {
        return $row->stepname . '<br><span class="secondary-info">Workflow: ' . $row->workflow . '</span>';
    }

    /**
     * Render user column.
     * @param $row
     * @return string
     */
    public function col_user($row) {
        global $CFG;
        return \html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $row->userid, fullname($row));
    }

    /**
     * Render time column.
     * @param $row
     * @return string
     * @throws \coding_exception
     */
    public function col_time($row) {
        $dateformat = get_string('strftimedatetime', 'core_langconfig');
        return userdate($row->time, $dateformat);
    }

    /**
     * Render action column.
     * @param $row
     * @return string
     */
    public function col_action($row) {
        $interactionlib = lib_manager::get_step_interactionlib($row->subpluginname);
        return $interactionlib->get_action_string($row->action);
    }

    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->box(get_string('nopastactions', 'tool_lifecycle'));
    }
}