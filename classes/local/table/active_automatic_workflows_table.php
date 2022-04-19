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
namespace tool_lifecycle\local\table;

use tool_lifecycle\action;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../../lib.php');

/**
 * Table listing all active automatically triggered workflows.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class active_automatic_workflows_table extends active_workflows_table {

    /**
     * Constructor for active_automatic_workflows_table.
     * @param int $uniqueid Unique id of this table.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;
        list($sqlwheremanual, $paramsmanual) = $DB->get_in_or_equal(false);
        $sqlwhere = 'timeactive IS NOT NULL AND manual ' . $sqlwheremanual;
        $params[1] = $paramsmanual[0];
        $this->set_sql("id, title, displaytitle, timeactive, sortindex", '{tool_lifecycle_workflow}',
            $sqlwhere, $params);
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    /**
     * Initialize the table.
     */
    public function init() {
        $this->define_columns(['title', 'timeactive', 'trigger', 'processes', 'sortindex', 'tools']);
        $this->define_headers([
            get_string('workflow_title', 'tool_lifecycle'),
            get_string('workflow_timeactive', 'tool_lifecycle'),
            get_string('trigger', 'tool_lifecycle'),
            get_string('workflow_processes', 'tool_lifecycle'),
            get_string('workflow_sortindex', 'tool_lifecycle'),
            get_string('workflow_tools', 'tool_lifecycle'),
        ]);
        $this->sortable(false, 'sortindex');
        $this->setup();
    }

    /**
     * Render sortindex column.
     *
     * @param object $row Row data.
     * @return string action buttons for changing sortorder of active workflows
     * @throws \coding_exception
     */
    public function col_sortindex($row) {
        global $OUTPUT;
        $output = '';
        if ($row->sortindex !== null) {
            if ($row->sortindex > 1) {
                $alt = 'up';
                $icon = 't/up';
                $action = action::UP_WORKFLOW;
                $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
            } else {
                $output .= $OUTPUT->spacer();
            }
            if ($row->sortindex < count(workflow_manager::get_active_automatic_workflows())) {
                $alt = 'down';
                $icon = 't/down';
                $action = action::DOWN_WORKFLOW;
                $output .= $this->format_icon_link($action, $row->id, $icon, get_string($alt));
            } else {
                $output .= $OUTPUT->spacer();
            }
        }

        return $output;
    }

}
