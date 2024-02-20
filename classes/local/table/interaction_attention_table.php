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

use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\step_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses for a specific user and a specific subplugin
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interaction_attention_table extends interaction_table {

    /**
     * Constructor for deactivated_workflows_table.
     * @param int $uniqueid Unique id of this table.
     * @param int[] $courseids List of ids for courses that require attention.
     * @param object $filterdata Object of filter criteria
     */
    public function __construct($uniqueid, $courseids, $filterdata = null) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;

        $fields = "p.id as processid, c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname, " .
            "c.startdate, cc.name as category , s.id as stepinstanceid, s.instancename as stepinstancename, ".
            "s.subpluginname as subpluginname";
        $from = '{tool_lifecycle_process} p join ' .
            '{course} c on p.courseid = c.id join ' .
            '{tool_lifecycle_step} s ' .
            'on p.workflowid = s.workflowid AND p.stepindex = s.sortindex ' .
            'left join {course_categories} cc on c.category = cc.id';
        $ids = implode(',', $courseids);

        $where = ['FALSE'];
        if ($ids) {
            $where = ['p.courseid IN (' . $ids . ')'];
        }

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
                $where[] = 'c.id = :courseid';
                $params['courseid'] = $filterdata->courseid;
            }
        }

        $this->column_nosort = ['status', 'tools'];
        $this->set_sql($fields, $from, join(" AND ", $where), $params);
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialises the columns of the table.
     */
    public function init() {
        $this->define_columns(['coursefullname', 'startdate', 'category', 'status', 'tools', 'date']);
        $this->define_headers([
            get_string('coursename', 'tool_lifecycle'),
            get_string('startdate'),
            get_string('category'),
            get_string('status', 'tool_lifecycle'),
            get_string('tools', 'tool_lifecycle'),
            get_string('date', 'tool_lifecycle'),
        ]);
        $this->setup();
    }

    /**
     * Render tools column.
     * @param object $row Row data.
     * @return string pluginname of the subplugin
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     */
    public function col_tools($row) {
        $output = '';
        $step = step_manager::get_step_instance($row->stepinstanceid);

        $tools = interaction_manager::get_action_tools($step->subpluginname, $row->processid);
        foreach ($tools as $tool) {
            $output .= $this->format_icon_link($tool['action'], $row->processid, $step->id, $tool['alt']);
        }
        return $output;
    }

    /**
     * Render date column.
     * @param object $row Row data.
     * @return string pluginname of the subplugin
     */
    public function col_date($row) {
        $lib = lib_manager::get_step_interactionlib($row->subpluginname);
        $date = $lib->get_due_date($row->processid, $row->stepinstanceid);

        if ($date === null) {
            return '';
        }

        return $date;
    }

    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $processid URL parameter to include in the link
     * @param int $stepinstanceid ID of the step instance
     * @param string $alt The string description of the link used as the title and alt text
     *
     * @return string The icon/link
     * @throws \moodle_exception
     */
    private function format_icon_link($action, $processid, $stepinstanceid, $alt) {
        global $PAGE, $OUTPUT;

        $button = new \single_button(new \moodle_url($PAGE->url,
            [
                'stepid' => $stepinstanceid,
                'action' => $action,
                'processid' => $processid,
                'sesskey' => sesskey(),
            ]), $alt
        );
        return $OUTPUT->render($button);
    }
}
