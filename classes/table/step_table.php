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
 * Table listing step instances
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\table;

use tool_lifecycle\action;
use tool_lifecycle\entity\trigger_subplugin;
use tool_lifecycle\manager\step_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../lib.php');

class step_table extends \table_sql {

    /** int workflowid */
    private $workflowid;

    /**
     * step_table constructor.
     * @param string $uniqueid
     * @param int $workflowid
     */
    public function __construct($uniqueid, $workflowid) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;
        $this->set_attribute('class', $this->attributes['class'] . ' ' . $uniqueid);
        $this->workflowid = $workflowid;
        list($sqlwhere, $params) = $DB->get_in_or_equal($workflowid);
        $this->set_sql("id, subpluginname, instancename, sortindex, sortindex as show, 'step' as type",
            '{tool_lifecycle_step}',
            "workflowid " . $sqlwhere, $params);
        $this->define_baseurl(new \moodle_url($PAGE->url, array('workflowid' => $workflowid)));
        $this->pageable(false);
        $this->init();
    }

    public function build_table() {
        $triggers = trigger_manager::get_triggers_for_workflow($this->workflowid);
        foreach ($triggers as $trigger) {
            $this->format_and_add_array_of_rows(array(
                array(
                    'id' => $trigger->id,
                    'type' => 'trigger',
                    'subpluginname' => $trigger->subpluginname,
                    'sortindex' => $trigger->sortindex,
                    'show' => $trigger->sortindex,
                    'instancename' => $trigger->instancename,
                )
            ), false);
        }
        return parent::build_table();
    }

    public function init() {
        $columns = ['type', 'instancename', 'subpluginname'];
        $headers = [
            get_string('step_type', 'tool_lifecycle'),
            get_string('step_instancename', 'tool_lifecycle'),
            get_string('step_subpluginname', 'tool_lifecycle'),
            ];
        if (! workflow_manager::is_editable($this->workflowid)) {
            $columns [] = 'show';
            $headers [] = get_string('step_show', 'tool_lifecycle');
        } else {
            $columns [] = 'sortindex';
            $headers [] = get_string('step_sortindex', 'tool_lifecycle');
            $columns [] = 'edit';
            $headers [] = get_string('step_edit', 'tool_lifecycle');
            $columns [] = 'delete';
            $headers [] = get_string('step_delete', 'tool_lifecycle');
        }
        $this->define_columns($columns);
        $this->define_headers($headers);

        if (!workflow_manager::is_editable($this->workflowid)) {
            $this->sortable(false, 'show');
        } else {
            $this->sortable(false, 'sortindex');
        }
        $this->setup();
    }

    /**
     * Render the type column. This column displays Trigger or Step, depending of the type of the subplugin.
     * @param $row
     * @return string type of the subplugin
     */
    public function col_type($row) {
        if ($row->type == 'step') {
            return get_string('step', 'tool_lifecycle');
        }
        return get_string('trigger', 'tool_lifecycle');
    }

    /**
     * Render subpluginname column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_subpluginname($row) {

        $subpluginname = $row->subpluginname;
        if ($row->type == 'step') {
            return get_string('pluginname', 'lifecyclestep_' . $subpluginname);
        } else {
            return get_string('pluginname', 'lifecycletrigger_' . $subpluginname);
        }
    }

    /**
     * Render sortindex column.
     * @param $row
     * @return string action buttons for changing sortorder of the subplugin
     */
    public function col_sortindex($row) {
        global $OUTPUT;
        $output = '';
        if ($row->type == 'step') {
            if ($row->sortindex !== null) {
                if ($row->sortindex > 1) {
                    $alt = 'up';
                    $icon = 't/up';
                    $action = action::UP_STEP;
                    $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
                } else {
                    $output .= $OUTPUT->spacer();
                }
                if ($row->sortindex < step_manager::count_steps_of_workflow($this->workflowid)) {
                    $alt = 'down';
                    $icon = 't/down';
                    $action = action::DOWN_STEP;
                    $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
                } else {
                    $output .= $OUTPUT->spacer();
                }
            }
        }
        if ($row->type == 'trigger') {
            if ($row->sortindex !== null) {
                if ($row->sortindex > 1) {
                    $alt = 'up';
                    $icon = 't/up';
                    $action = action::UP_TRIGGER;
                    $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
                } else {
                    $output .= $OUTPUT->spacer();
                }
                if ($row->sortindex < trigger_manager::count_triggers_of_workflow($this->workflowid)) {
                    $alt = 'down';
                    $icon = 't/down';
                    $action = action::DOWN_TRIGGER;
                    $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
                } else {
                    $output .= $OUTPUT->spacer();
                }
            }
        }

        return  $output;
    }

    /**
     * Render edit column.
     * @param $row
     * @return string action button for editing of the subplugin
     */
    public function col_edit($row) {

        $alt = 'edit';
        $icon = 't/edit';
        if ($row->type == 'step') {
            $action = action::STEP_INSTANCE_FORM;
        } else {
            $action = action::TRIGGER_INSTANCE_FORM;
        }

        return  $this->format_icon_link($action, $row->id, $icon, get_string($alt));
    }

    /**
     * Render show column.
     * @param $row
     * @return string action button for editing of the subplugin
     */
    public function col_show($row) {

        $alt = 'view';
        $icon = 't/viewdetails';
        if ($row->type == 'step') {
            $action = action::STEP_INSTANCE_FORM;
        } else {
            $action = action::TRIGGER_INSTANCE_FORM;
        }

        return  $this->format_icon_link($action, $row->id, $icon, get_string($alt));
    }

    /**
     * Render delete column.
     * @param $row
     * @return string action button for deleting the subplugin
     */
    public function col_delete($row) {

        $alt = 'delete';
        $icon = 't/delete';
        if ($row->type == 'step') {
            $action = action::STEP_INSTANCE_DELETE;
        } else {
            $action = action::TRIGGER_INSTANCE_DELETE;
        }
        return $this->format_icon_link($action, $row->id, $icon, get_string($alt));
    }

    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $subpluginid URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     */
    private function format_icon_link($action, $subpluginid, $icon, $alt) {
        global $PAGE, $OUTPUT;

        return $OUTPUT->action_icon(new \moodle_url($PAGE->url,
                array('action' => $action,
                    'subplugin' => $subpluginid,
                    'sesskey' => sesskey(),
                    'workflowid' => $this->workflowid)),
                new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                null, array('title' => $alt)) . ' ';
    }

}