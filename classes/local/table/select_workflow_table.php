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
 * Table listing all workflows with a "copy from" button.
 *
 * @package    tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use tool_lifecycle\action;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\urls;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../../lib.php');

/**
 * Table listing all workflows with a "copy from" button.
 *
 * @package    tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class select_workflow_table extends \flexible_table {

    /**
     * Constructor for workflow_table.
     * @param int $uniqueid Unique id of this table.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_attribute('class', 'lifecycle-table');
        $this->set_attribute('id', $uniqueid);
        $this->define_baseurl($PAGE->url);
        $this->define_columns(['title', 'status', 'since', 'trigger', 'processes', 'tools']);
        $this->define_headers([
            get_string('workflow_title', 'tool_lifecycle'),
            get_string('status'),
            get_string('since'),
            get_string('trigger', 'tool_lifecycle'),
            get_string('workflow_processes', 'tool_lifecycle'),
            get_string('workflow_tools', 'tool_lifecycle'),
        ]);
        $this->sortable(false);
        $this->setup();
    }

    /**
     * Method to display the table.
     * @return void
     */
    public function out() {
        global $DB;
        $records = $DB->get_records_sql('SELECT id, title, displaytitle, timeactive, timedeactive, sortindex ' .
            'FROM {tool_lifecycle_workflow} ORDER BY timedeactive IS NOT NULL, timeactive IS NOT NULL');
        $this->format_and_add_array_of_rows($records, true);
        $this->finish_output();
    }

    /**
     * Render title column.
     * @param object $row Row data.
     * @return string Rendered title.
     */
    public function col_title($row) {
        return $row->title . '<br><span class="workflow_displaytitle">' . $row->displaytitle . '</span>';
    }

    /**
     * Render the trigger column.
     * @param object $row Row data.
     * @return string instancename of the trigger
     */
    public function col_trigger($row) {
        $triggers = trigger_manager::get_triggers_for_workflow($row->id);
        $triggerstring = '';
        if ($triggers) {
            $triggerstring = $triggers[0]->instancename;
            for ($i = 1; $i < count($triggers); $i++) {
                $triggerstring .= ', ';
                $triggerstring .= $triggers[$i]->instancename;
            }
        }
        return $triggerstring;
    }

    /**
     * Render the processes column. It shows the number of active processes for the workflow instance.
     * @param object $row Row data.
     * @return string Number of processes.
     * @throws \dml_exception
     */
    public function col_processes($row) {
        if ($row->timeactive && !$row->timedeactive) {
            return process_manager::count_processes_by_workflow($row->id);
        } else {
            return '';
        }
    }

    /**
     * Render status column.
     * @param object $row Row data.
     * @return string status column
     */
    public function col_status($row) {
        if ($row->timedeactive) {
            return get_string('deactivated', 'tool_lifecycle');
        } else if ($row->timeactive) {
            return get_string('active', 'tool_lifecycle');
        } else {
            return get_string('draft', 'tool_lifecycle');
        }
    }

    /**
     * Render since column.
     * @param object $row Row data.
     * @return string since column
     */
    public function col_since($row) {
        if ($row->timedeactive) {
            return userdate($row->timedeactive, get_string('strftimedatetime'));
        } else if ($row->timeactive) {
            return userdate($row->timeactive, get_string('strftimedatetime'));
        } else {
            return '';
        }
    }

    /**
     * Render tools column.
     * @param object $row Row data.
     * @return string action buttons for workflows
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_tools($row) {
        global $PAGE;
        $createcopy = get_string('create_copy', 'tool_lifecycle');
        return \html_writer::link(new \moodle_url($PAGE->url, ['wf' => $row->id]),
            $createcopy, ['class' => 'btn btn-primary', 'title' => $createcopy]);
    }

}
