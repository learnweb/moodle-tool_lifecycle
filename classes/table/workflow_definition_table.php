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
 * Table listing all workflow definitions.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\table;

use tool_lifecycle\action;
use tool_lifecycle\manager\lib_manager;
use tool_lifecycle\manager\step_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../lib.php');

class workflow_definition_table extends workflow_table {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_sql("id, title, timeactive, displaytitle",
            '{tool_lifecycle_workflow}',
            "timeactive IS NULL AND timedeactive IS NULL");
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    public function init() {
        $this->define_columns(['title', 'timeactive', 'tools']);
        $this->define_headers([
            get_string('workflow_title', 'tool_lifecycle'),
            get_string('workflow_timeactive', 'tool_lifecycle'),
            get_string('workflow_tools', 'tool_lifecycle'),
            ]);
        $this->sortable(false, 'title');
        $this->setup();
    }

    /**
     * Render activate column.
     * @param $row
     * @return string activate time for workflows
     */
    public function col_timeactive($row) {
        global $OUTPUT, $PAGE;
        if ($row->timeactive) {
            return userdate($row->timeactive, get_string('strftimedatetime'), 0);
        }
        if (workflow_manager::is_valid($row->id)) {
            return $OUTPUT->single_button(new \moodle_url($PAGE->url,
                array('action' => action::WORKFLOW_ACTIVATE,
                    'sesskey' => sesskey(),
                    'workflowid' => $row->id)),
                get_string('activateworkflow', 'tool_lifecycle'));
        } else {
            return $OUTPUT->pix_icon('i/warning', get_string('invalid_workflow_details', 'tool_lifecycle')) .
                get_string('invalid_workflow', 'tool_lifecycle');
        }
    }

    /**
     * Render tools column.
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

        $trigger = trigger_manager::get_triggers_for_workflow($row->id);
        if (!empty($trigger)) {
            $lib = lib_manager::get_trigger_lib($trigger[0]->subpluginname);
        }

        if (!isset($lib) || $lib->has_multiple_instances()) {

            $action = action::WORKFLOW_INSTANCE_FROM;
            $alt = get_string('editworkflow', 'tool_lifecycle');
            $icon = 't/edit';
            $output .= $this->format_icon_link($action, $row->id, $icon, $alt);

            $action = action::WORKFLOW_DUPLICATE;
            $alt = get_string('duplicateworkflow', 'tool_lifecycle');
            $icon = 't/copy';
            $output .= $this->format_icon_link($action, $row->id, $icon, $alt);

            $action = action::WORKFLOW_BACKUP;
            $alt = get_string('backupworkflow', 'tool_lifecycle');
            $icon = 't/backup';
            $output .= $this->format_icon_link($action, $row->id, $icon, $alt);

            if (!workflow_manager::is_active($row->id)) {
                $action = action::WORKFLOW_DELETE;
                $alt = get_string('deleteworkflow', 'tool_lifecycle');
                $icon = 't/delete';
                $output .= $this->format_icon_link($action, $row->id, $icon, $alt);
            }
        }

        return $output;
    }

}