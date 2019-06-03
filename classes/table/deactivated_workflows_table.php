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
 * Table listing deactivated workflows
 *
 * @package tool_lifecycle
 * @copyright  2018 Yorick Reum, JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\table;

use tool_lifecycle\manager\lib_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class deactivated_workflows_table extends workflow_table {

    /**
     * deactivated_workflows_table constructor.
     *
     * @param string $uniqueid
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_sql(
            "id, title, timedeactive, displaytitle",
            '{tool_lifecycle_workflow}',
            "timeactive IS NULL AND timedeactive IS NOT NULL"
        );
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    public function init() {
        $this->define_columns(['title', 'timedeactive', 'processes', 'tools']);
        $this->define_headers([
            get_string('workflow_title', 'tool_lifecycle'),
            get_string('workflow_timedeactive', 'tool_lifecycle'),
            get_string('workflow_processes', 'tool_lifecycle'),
            get_string('workflow_tools', 'tool_lifecycle'),
        ]);
        $this->sortable(false, 'title');
        $this->setup();
    }

    /**
     * Render tools column.
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

        $action = ACTION_WORKFLOW_DUPLICATE;
        $alt = get_string('duplicateworkflow', 'tool_lifecycle');
        $icon = 't/copy';
        $output .= $this->format_icon_link($action, $row->id, $icon, $alt);

        $action = ACTION_WORKFLOW_INSTANCE_FROM;
        $alt = get_string('editworkflow', 'tool_lifecycle');
        $icon = 't/edit';
        $output .= $this->format_icon_link($action, $row->id, $icon, $alt);

        if (workflow_manager::is_abortable($row->id)) {
            $alt = get_string('abortprocesses', 'tool_lifecycle');
            $icon = 't/stop';
            $url = new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php',
                array('workflowid' => $row->id, 'action' => ACTION_WORKFLOW_ABORT, 'sesskey' => sesskey()));
            $confirmaction = new \confirm_action(get_string('abortprocesses_confirm', 'tool_lifecycle'));
            $output .= $OUTPUT->action_icon($url,
                new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                $confirmaction,
                array('title' => $alt)
            );

        }

        if (workflow_manager::is_removable($row->id)) {
            $alt = get_string('deleteworkflow', 'tool_lifecycle');
            $icon = 't/delete';
            $url = new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php',
                array('workflowid' => $row->id, 'action' => ACTION_WORKFLOW_DELETE, 'sesskey' => sesskey()));
            $confirmaction = new \confirm_action(get_string('deleteworkflow_confirm', 'tool_lifecycle'));
            $output .= $OUTPUT->action_icon($url,
                new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                $confirmaction,
                array('title' => $alt)
            );
        }

        return $output;
    }
}