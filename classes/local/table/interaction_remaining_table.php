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
namespace tool_lifecycle\local\table;

use core_date;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\data\manual_trigger_tool;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses for a specific user and a specific subplugin
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interaction_remaining_table extends interaction_table {

    /** @var manual_trigger_tool[] $availabletools list of all available trigger tools. */
    private $availabletools;
    /** @var array[] $trigger2courses map from manual trigger id to courses that are selected by
     * automatic trigger(s) which is (are) associated to manual trigger.
     */
    private $trigger2courses = [];

    /**
     * Constructor for deactivated_workflows_table.
     * @param int $uniqueid Unique id of this table.
     * @param int[] $courseids List of ids for courses that require no attention.
     */
    public function __construct($uniqueid, $courseids) {
        parent::__construct($uniqueid);
        global $PAGE, $CFG, $DB;

        $this->availabletools = workflow_manager::get_manual_trigger_tools_for_active_workflows();
        foreach ($this->availabletools as $tool) {
            // Check if there is an automatic trigger associated with the manual trigger.
            $records = $DB->get_records_select('tool_lifecycle_trigger', "subpluginname != :plugin and workflowid in
                (select workflowid from {tool_lifecycle_trigger} where id = :id)",
                ['id' => $tool->triggerid, 'plugin' => 'manual']);
            if ($records && count($records) > 0) {
                $first = reset($records);
                // There is at least one automatic trigger associated
                // => check for AND condition.
                $field = $DB->get_field('tool_lifecycle_workflow', 'andor', ['id' => $first->workflowid]);
                if ($field) {
                    // No AND condition => OR condition is not supported.
                    echo 'OR condition is not supported.<br>';
                    continue;
                }
                // Ok, we have an AND condition =>
                // Store courses selected by this (these) trigger(s).
                $triggers = [];
                foreach ($records as $record) {
                    $triggers[] = $record;
                }
                if (count($triggers) > 0) {
                    $processor = new \tool_lifecycle\processor();
                    $recordset = $processor->get_course_recordset($triggers);
                    $this->trigger2courses[$tool->triggerid] = $recordset;
                }
            }
        }

        // COALESCE returns l.time if l.time != null and 0 otherwise.
        // We need to do this, so that courses without any action have a smaller timestamp than courses with an recorded action.
        // Otherwise, it would mess up the sorting.
        $fields = "c.id as courseid, p.id AS processid, c.fullname AS coursefullname, c.shortname AS courseshortname, " .
                  "c.startdate, cc.name AS category, cc.path as categorypath, COALESCE(l.time, 0) AS lastmodified, l.userid, " .
                  "l.action, s.subpluginname, ";
        if ($CFG->branch >= 311) {
            $fields .= \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;
        } else {
            $fields .= get_all_user_name_fields(true, 'u');
        }

        $from = '{course} c ' .
            'LEFT JOIN (' .
                /* This Subquery creates a table with the one record per course from {tool_lifecycle_action_log}
                   with the highest id (the newest record per course) */
                'SELECT * FROM {tool_lifecycle_action_log} a ' .
                'INNER JOIN ( ' .
                    'SELECT b.courseid as cid, MAX(b.id) as maxlogid ' .
                    'FROM {tool_lifecycle_action_log} b ' .
                    'GROUP BY b.courseid ' .
                ') m ON a.courseid = m.cid AND a.id = m.maxlogid ' .
            ') l ON c.id = l.courseid ' .
            'LEFT JOIN {tool_lifecycle_process} p ON p.courseid = c.id ' .
            'LEFT JOIN {course_categories} cc ON c.category = cc.id ' .
            'LEFT JOIN {tool_lifecycle_step} s ON l.workflowid = s.workflowid AND l.stepindex = s.sortindex ' .
            'LEFT JOIN {user} u ON l.userid = u.id';

        $ids = implode(',', $courseids);

        $where = 'FALSE';
        if ($ids) {
            $where = 'c.id IN ('. $ids . ')';
        }

        $this->column_nosort = ['status', 'tools'];
        $this->sortable(true, 'lastmodified', 'DESC');
        $this->set_sql($fields, $from, $where, []);
        $this->set_count_sql("SELECT COUNT(1) FROM {course} c WHERE $where");
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialises the columns of the table.
     */
    public function init() {
        $this->define_columns(['coursefullname', 'startdate', 'category', 'status', 'lastmodified', 'tools']);
        $this->define_headers([
            get_string('coursename', 'tool_lifecycle'),
            get_string('startdate'),
            get_string('category'),
            get_string('status', 'tool_lifecycle'),
            get_string('lastaction', 'tool_lifecycle'),
            get_string('tools', 'tool_lifecycle'),
        ]);
        $this->setup();
    }

    /**
     * Render tools column.
     * @param object $row Row data.
     * @return string Rendered tools html
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_tools($row) {
        global $PAGE, $OUTPUT, $DB;

        if ($row->processid !== null) {
            return '--';
        }
        if (empty($this->availabletools)) {
            return "--";
        }
        $actions = [];
        foreach ($this->availabletools as $tool) {
            // Check if the manual trigger has at least one automatic trigger associated.
            if (array_key_exists($tool->triggerid, $this->trigger2courses)) {
                // There is at least one automatic trigger =>
                // Check if 'this' course is included in course set.
                $found = false;
                foreach ($this->trigger2courses[$tool->triggerid] as $element) {
                    if ($row->courseid === $element->id) {
                        // Course is included in course set of automatic trigger(s).
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // Course is not included in automatic trigger(s) set
                    // => do not display action in action menu.
                    continue;
                }
            }

            // Check capability.
            if (has_capability($tool->capability, \context_course::instance($row->courseid), null, false)) {
                $actions[$tool->triggerid] = new \action_menu_link_secondary(
                    new \moodle_url($PAGE->url, ['triggerid' => $tool->triggerid,
                        'courseid' => $row->courseid, 'sesskey' => sesskey(), ]),
                    new \pix_icon($tool->icon, $tool->displayname, 'moodle', ['class' => 'iconsmall', 'title' => '']),
                    $tool->displayname
                );
            }
        }

        $menu = new \action_menu();
        $menu->set_menu_trigger(get_string('action'));

        foreach ($actions as $action) {
            $menu->add($action);
        }

        return $OUTPUT->render($menu);
    }

    /**
     * Render status column.
     * @param object $row Row data.
     * @return string Rendered status html
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
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
        if ($row->userid == -1) {
            $userlink = get_string("anonymous_user", 'tool_lifecycle');
        } else {
            $userlink = \html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $row->userid, fullname($row));
        }
        $interactionlib = lib_manager::get_step_interactionlib($row->subpluginname);
        return $interactionlib->get_action_string($row->action, $userlink);
    }


    /**
     * Render lastmodified column.
     * @param object $row Row data.
     * @return string Rendered lastmodified html
     * @throws \coding_exception
     */
    public function col_lastmodified($row) {
        global $USER;

        if (!$row->lastmodified) {
            return '';
        }

        $dateformat = get_string('strftimedatetime', 'core_langconfig');

        return userdate($row->lastmodified, $dateformat, core_date::get_user_timezone($USER));
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->box(get_string('noremainingcoursestodisplay', 'tool_lifecycle'));
    }

}
