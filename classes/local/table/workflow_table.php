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
 * Table listing all active automatically triggered workflows.
 *
 * @package tool_lifecycle
 * @copyright  2018 Jan Dageförde WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use core_date;
use html_writer;
use tool_lifecycle\action;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\urls;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../../lib.php');

/**
 * Table listing all active automatically triggered workflows.
 *
 * @package tool_lifecycle
 * @copyright  2018 Jan Dageförde WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class workflow_table extends \table_sql {

    /**
     * Constructor for workflow_table.
     * @param int $uniqueid Unique id of this table.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->set_attribute('class', $this->attributes['class'] . ' ' . $uniqueid);
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
     * Render activate column.
     * @param object $row Row data.
     * @return string activate time for workflows
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_timeactive($row) {
        global $OUTPUT, $PAGE, $USER;
        if ($row->timeactive) {
            return userdate($row->timeactive, get_string('strftimedatetime'),
                core_date::get_user_timezone($USER));
        }
        return $OUTPUT->single_button(new \moodle_url($PAGE->url,
            ['action' => action::WORKFLOW_ACTIVATE,
                'sesskey' => sesskey(),
                'workflowid' => $row->id, ]),
            get_string('activateworkflow', 'tool_lifecycle'));
    }

    /**
     * Render deactivated column.
     * @param object $row Row data.
     * @return string deactivate time for workflows
     * @throws \coding_exception
     */
    public function col_timedeactive($row) {
        global $USER;
        if ($row->timedeactive) {
            return userdate($row->timedeactive, get_string('strftimedatetime'),
                core_date::get_user_timezone($USER));
        }
        return get_string('workflow_active', 'tool_lifecycle');
    }

    /**
     * Render the trigger column.
     * @param object $row Row data.
     * @return string instancename of the trigger
     * @throws \dml_exception
     */
    public function col_trigger($row) {
        global $OUTPUT;
        $out = "";
        $triggers = trigger_manager::get_triggers_for_workflow($row->id);
        if ($triggers) {
            foreach ($triggers as $key => $trigger) {
                $triggertitle = "[".$trigger->subpluginname."] ".$trigger->instancename;
                $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
                if (isset($lib)) {
                    $triggericon = $lib->get_icon();
                    $out .= $OUTPUT->pix_icon($triggericon, $triggertitle);
                } else {
                    $out .= $OUTPUT->pix_icon('i/warning', get_string('notfound', 'error') . ': ' . $trigger->subpluginname);
                }
            }
        } else {
            $out = "--";
        }
        return $out;
    }

    /**
     * Render the step column.
     * @param object $row Row data.
     * @return string instancename of the step
     * @throws \dml_exception
     */
    public function col_step($row) {
        global $OUTPUT;
        $out = "";
        $steps = step_manager::get_step_instances($row->id);
        if ($steps) {
            foreach ($steps as $key => $step) {
                $steptitle = "[".$step->subpluginname."] ".$step->instancename;
                $lib = lib_manager::get_step_lib($step->subpluginname);
                if (isset($lib)) {
                    $stepicon = $lib->get_icon();
                    $out .= $OUTPUT->pix_icon($stepicon, $steptitle);
                } else {
                    $out .= $OUTPUT->pix_icon('i/warning', get_string('notfound', 'error') . ': ' . $step->subpluginname);
                }
            }
        } else {
            $out = "--";
        }
        return $out;
    }

    /**
     * Render the processes column. It shows the number of active processes for the workflow instance.
     * @param object $row Row data.
     * @return string Number of processes.
     * @throws \dml_exception
     */
    public function col_processes($row) {
        return process_manager::count_processes_by_workflow($row->id);
    }

    /**
     * Render tools column.
     * @param object $row Row data.
     * @return string action buttons for workflows
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_tools($row) {
        global $OUTPUT;
        $output = '';

        $alt = get_string('viewsteps', 'tool_lifecycle');
        $icon = 't/viewdetails';
        $url = new \moodle_url(urls::WORKFLOW_DETAILS,
            ['wf' => $row->id]);
        $output .= $OUTPUT->action_icon($url, new \pix_icon($icon, $alt, 'moodle', ['title' => $alt]),
            null, ['title' => $alt]);

        return $output;
    }

    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $workflowid URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     * @throws \moodle_exception
     */
    protected function format_icon_link($action, $workflowid, $icon, $alt) {
        global $OUTPUT, $PAGE;

        return $OUTPUT->action_icon(new \moodle_url($PAGE->url,
                ['action' => $action,
                    'workflowid' => $workflowid,
                    'sesskey' => sesskey(), ]),
                new \pix_icon($icon, $alt, 'moodle', ['title' => $alt]),
                null , ['title' => $alt]) . ' ';
    }

    /**
     * This function is not part of the public api.
     * @param array $row Row date
     * @param string $classname classes to add
     * @return string HTML code for the row passed.
     */
    public function print_row($row, $classname = '') {
        echo $this->get_row_html($row, $classname);
    }

    /**
     * Generate html code for the passed row.
     *
     * @param array $row Row data.
     * @param string $classname classes to add.
     *
     * @return string $html html code for the row passed.
     */
    public function get_row_html($row, $classname = '') {
        static $suppresslastrow = null;
        $rowclasses = [];

        if ($classname) {
            $rowclasses[] = $classname;
        }

        $rowid = $this->uniqueid . '_r' . $this->currentrow;
        $html = '';

        $html .= html_writer::start_tag('tr', ['class' => implode(' ', $rowclasses), 'id' => $rowid]);

        // If we have a separator, print it.
        if ($row === null) {
            $colcount = count($this->columns);
            $html .= html_writer::tag('td', html_writer::tag(
                'div',
                '',
                ['class' => 'tabledivider']
            ), ['colspan' => $colcount]);
        } else {
            $html .= $this->get_row_cells_html($rowid, $row, $suppresslastrow);
        }

        $html .= html_writer::end_tag('tr');

        $suppressenabled = array_sum($this->column_suppress);
        if ($suppressenabled) {
            $suppresslastrow = $row;
        }
        $this->currentrow++;
        return $html;
    }
}
