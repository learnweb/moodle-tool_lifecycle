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
namespace tool_lifecycle\local\table;

use tool_lifecycle\action;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\urls;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../../lib.php');

/**
 * Table listing all active workflow definitions.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class active_workflows_table extends workflow_table {

    /**
     * Render tools column for active workflows.
     *
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
        $overviewurl = new \moodle_url(urls::WORKFLOW_DETAILS,
            ['wf' => $row->id]);
        $output .= $OUTPUT->action_icon($overviewurl, new \pix_icon($icon, $alt, 'moodle', ['title' => $alt]),
            null, ['title' => $alt]);

        if (workflow_manager::is_disableable($row->id)) {
            $action = action::WORKFLOW_BACKUP;
            $alt = get_string('backupworkflow', 'tool_lifecycle');
            $icon = 't/backup';
            $output .= $this->format_icon_link($action, $row->id, $icon, $alt);

            $alt = get_string('disableworkflow', 'tool_lifecycle');
            $icon = 't/disable';
            $url = new \moodle_url(urls::DEACTIVATED_WORKFLOWS,
                ['workflowid' => $row->id, 'action' => action::WORKFLOW_DISABLE, 'sesskey' => sesskey()]);
            $confirmaction = new \confirm_action(get_string('disableworkflow_confirm', 'tool_lifecycle'));
            $output .= $OUTPUT->action_icon($url,
                new \pix_icon($icon, $alt, 'tool_lifecycle', ['title' => $alt]),
                $confirmaction,
                ['title' => $alt]);

            $alt = get_string('abortdisableworkflow', 'tool_lifecycle');
            $icon = 't/stop';
            $url = new \moodle_url(urls::DEACTIVATED_WORKFLOWS,
                ['workflowid' => $row->id, 'action' => action::WORKFLOW_ABORTDISABLE, 'sesskey' => sesskey()]);
            $confirmaction = new \confirm_action(get_string('abortdisableworkflow_confirm', 'tool_lifecycle'));
            $output .= $OUTPUT->action_icon($url,
                new \pix_icon($icon, $alt, 'moodle', ['title' => $alt]),
                $confirmaction,
                ['title' => $alt]
            );
        }

        return $output;
    }

}
