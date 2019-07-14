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

use tool_lifecycle\manager\lib_manager;
use tool_lifecycle\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class interaction_remaining_table extends interaction_table {

    /** manual_trigger_tool[] list of all available trigger tools. */
    private $availabletools;

    public function __construct($uniqueid, $courseids) {
        parent::__construct($uniqueid);
        global $PAGE;

        $this->availabletools = workflow_manager::get_manual_trigger_tools_for_active_workflows();

        // COALESCE returns l.time if l.time != null and 0 otherwise.
        // We need to do this, so that courses without any action have a smaller timestamp than courses with an recorded action.
        // Otherwise, it would mess up the sorting.
        $fields = "c.id as courseid, p.id AS processid, c.fullname AS coursefullname, c.shortname AS courseshortname, " .
                  "cc.name AS category, COALESCE(l.time, 0) AS lastmodified, l.userid, l.action, s.subpluginname, " .
                   get_all_user_name_fields(true, 'u');
        $from = '{course} c ' .
            'LEFT JOIN {tool_lifecycle_action_log} l ON c.id = l.courseid ' .
            'LEFT JOIN ( ' .
                'SELECT a.courseid, MAX(a.time) AS maxtime ' .
                'FROM {tool_lifecycle_action_log} a ' .
                'GROUP BY a.courseid, a.time ' .
                ') m ' .
            'ON l.courseid = m.courseid AND l.time = m.maxtime ' .
            'LEFT JOIN {tool_lifecycle_process} p ON p.courseid = c.id ' .
            'LEFT JOIN {course_categories} cc ON c.category = cc.id ' .
            'LEFT JOIN {tool_lifecycle_step} s ON l.workflowid = s.workflowid AND l.stepindex = s.sortindex ' .
            'LEFT JOIN {user} u ON l.userid = u.id';

        $ids = implode(',', $courseids);

        $where = 'FALSE';
        if ($ids) {
            $where = 'c.id IN ('. $ids . ')';
        }

        $order = ' ORDER BY lastmodified DESC';

        $this->sortable(false);
        $this->set_sql($fields, $from, $where . $order, []);
        $this->set_count_sql("SELECT COUNT(1) FROM {course} c WHERE $where");
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialises the columns of the table.
     */
    public function init() {
        $this->define_columns(['courseid', 'coursefullname', 'category', 'status', 'lastmodified', 'tools']);
        $this->define_headers([
            get_string('course'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('category'),
            get_string('status', 'tool_lifecycle'),
            get_string('lastaction', 'tool_lifecycle'),
            get_string('tools', 'tool_lifecycle'),
        ]);
        $this->setup();
    }

    /**
     * Render tools column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_tools($row) {
        global $PAGE, $OUTPUT;

        if ($row->processid !== null) {
            return '';
        }

        $actions = [];
        foreach ($this->availabletools as $tool) {
            if (has_capability($tool->capability, \context_course::instance($row->courseid), null, false)) {
                $actions[$tool->triggerid] = new \action_menu_link_secondary(
                    new \moodle_url($PAGE->url, array('triggerid' => $tool->triggerid,
                        'courseid' => $row->courseid, 'sesskey' => sesskey())),
                    new \pix_icon($tool->icon, $tool->displayname, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                    $tool->displayname
                );
            }
        }

        $menu = new \action_menu();
        $menu->set_alignment(\action_menu::TR, \action_menu::BR);
        $menu->set_menu_trigger(get_string('action'));

        foreach ($actions as $action) {
            $menu->add($action);
        }

        return $OUTPUT->render($menu);
    }

    public function col_status($row) {
        $processstatus = parent::col_status($row);
        // If current process has status, show status.
        if ($processstatus != '') {
            return $processstatus;
        }
        // Otherwise, if there is no action saved for this process, show nothing.
        if (!$row->subpluginname) {
            return '';
        }
        // Otherwise, show latest action commited by user.
        global $CFG;
        $userlink = \html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $row->userid, fullname($row));
        $interactionlib = lib_manager::get_step_interactionlib($row->subpluginname);
        return $interactionlib->get_action_string($row->action, $userlink);
    }

    public function col_lastmodified($row) {
        if (!$row->lastmodified) {
            return '';
        }

        $dateformat = get_string('strftimedatetime', 'core_langconfig');
        return userdate($row->lastmodified, $dateformat);
    }

}