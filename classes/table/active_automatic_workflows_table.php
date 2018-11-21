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
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\table;

use tool_lifecycle\manager\process_manager;
use tool_lifecycle\manager\step_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../lib.php');

class active_automatic_workflows_table extends workflow_table {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;
        list($sqlwhereactive, $params) = $DB->get_in_or_equal(true);
        list($sqlwheremanual, $paramsmanual) = $DB->get_in_or_equal(false);
        $sqlwhere = 'active ' . $sqlwhereactive . ' AND manual ' . $sqlwheremanual;
        $params[1] = $paramsmanual[0];
        $this->set_sql("id, title, displaytitle, timeactive, sortindex", '{tool_lifecycle_workflow}',
            $sqlwhere, $params);
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    public function init() {
        $this->define_columns(['title', 'timeactive', 'trigger', 'processes', 'sortindex', 'tools', 'disable']);
        $this->define_headers([
            get_string('workflow_title', 'tool_lifecycle'),
            get_string('workflow_timeactive', 'tool_lifecycle'),
            get_string('trigger', 'tool_lifecycle'),
            get_string('workflow_processes', 'tool_lifecycle'),
            get_string('workflow_sortindex', 'tool_lifecycle'),
            get_string('workflow_tools', 'tool_lifecycle'),
            get_string('disableworkflow', 'tool_lifecycle'),
            ]);
        $this->sortable(false, 'sortindex');
        $this->setup();
    }

    /**
     * Render sortindex column.
     * @param $row
     * @return string action buttons for changing sortorder of active workflows
     */
    public function col_sortindex($row) {
        global $OUTPUT;
        $output = '';
        if ($row->sortindex !== null) {
            if ($row->sortindex > 1) {
                $alt = 'up';
                $icon = 't/up';
                $action = ACTION_UP_WORKFLOW;
                $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
            } else {
                $output .= $OUTPUT->spacer();
            }
            if ($row->sortindex < count(workflow_manager::get_active_automatic_workflows())) {
                $alt = 'down';
                $icon = 't/down';
                $action = ACTION_DOWN_WORKFLOW;
                $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
            } else {
                $output .= $OUTPUT->spacer();
            }
        }

        return  $output;
    }

    /**
     * Render disable column.
     * @param $row
     * @return string action buttons for workflows
     */
    public function col_disable($row)
    {
        global $OUTPUT;
        $output = '';

        $url = new \moodle_url('/admin/tool/lifecycle/workflowsettings.php',
            array('workflowid' => $row->id, 'action' => ACTION_WORKFLOW_DISABLE, 'sesskey' => sesskey()));
        $output .=
            '<div>' . $OUTPUT->single_button(
                $url,
                get_string('disableworkflow', 'tool_lifecycle'))
            . '</div>';

        $url = new \moodle_url('/admin/tool/lifecycle/workflowsettings.php',
            array('workflowid' => $row->id, 'action' => ACTION_WORKFLOW_ABORTDISABLE, 'sesskey' => sesskey()));
        $output .=
            '<div>' . $OUTPUT->single_button(
                $url,
                get_string('abortdisableworkflow', 'tool_lifecycle'))
            . '</div>';

        return $output;
    }

}