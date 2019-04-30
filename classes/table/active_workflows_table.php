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
 * Table listing all active workflow definitions.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\table;

use tool_lifecycle\manager\lib_manager;
use tool_lifecycle\manager\step_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../lib.php');

abstract class active_workflows_table extends workflow_table {

    /**
     * Render tools column for active workflows.
     *
     * @param $row
     * @return string action buttons for workflows
     */
    public function col_tools($row) {
        global $OUTPUT;
        $output = '';

        $alt = get_string('viewsteps', 'tool_lifecycle');
        $icon = 't/viewdetails';
        $url = new \moodle_url('/admin/tool/lifecycle/workflowsettings.php',
            array('workflowid' => $row->id, 'sesskey' => sesskey()));
        $output .= $OUTPUT->action_icon($url, new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
            null, array('title' => $alt));

        if (workflow_manager::is_disableable($row->id)) {
            $action = ACTION_WORKFLOW_DUPLICATE;
            $alt = get_string('duplicateworkflow', 'tool_lifecycle');
            $icon = 't/copy';
            $output .= $this->format_icon_link($action, $row->id, $icon, $alt);

            $alt = get_string('disableworkflow', 'tool_lifecycle');
            $icon = 't/disable';
            $url = new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php',
                array('workflowid' => $row->id, 'action' => ACTION_WORKFLOW_DISABLE, 'sesskey' => sesskey()));
            $confirmaction = new \confirm_action(get_string('disableworkflow_confirm', 'tool_lifecycle'));
            $output .= $OUTPUT->action_icon($url,
                new \pix_icon($icon, $alt, 'tool_lifecycle', array('title' => $alt)),
                $confirmaction,
                array('title' => $alt));

            $alt = get_string('abortdisableworkflow', 'tool_lifecycle');
            $icon = 't/stop';
            $url = new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php',
                array('workflowid' => $row->id, 'action' => ACTION_WORKFLOW_ABORTDISABLE, 'sesskey' => sesskey()));
            $confirmaction = new \confirm_action(get_string('abortdisableworkflow_confirm', 'tool_lifecycle'));
            $output .= $OUTPUT->action_icon($url,
                new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                $confirmaction,
                array('title' => $alt)
            );
        }

        return $output;
    }

}