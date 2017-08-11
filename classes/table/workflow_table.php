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
 * Table listing workflows.
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\table;

use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\trigger_manager;
use tool_cleanupcourses\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../lib.php');

class workflow_table extends \table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_sql("id, title, timeactive", '{tool_cleanupcourses_workflow}', "TRUE");
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    public function init() {
        $this->define_columns(['title', 'timeactive', 'tools']);
        $this->define_headers([
            get_string('workflow_title', 'tool_cleanupcourses'),
            get_string('workflow_timeactive', 'tool_cleanupcourses'),
            get_string('workflow_tools', 'tool_cleanupcourses'),
            ]);
        $this->sortable(false, 'workflow_timeactive');
        $this->setup();
    }

    /**
     * Render tools column.
     * @param $row
     * @return string action buttons for workflows
     */
    public function col_tools($row) {
        global $OUTPUT;
        $output = '';

        $alt = 'edit';
        $icon = 't/edit';
        $url = new \moodle_url('/admin/tool/cleanupcourses/workflowsettings.php',
            array('workflowid' => $row->id, 'sesskey' => sesskey()));
        $output .= $OUTPUT->action_icon($url, new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
            null , array('title' => $alt));

        return $output;
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
                null , array('title' => $alt)) . ' ';
    }

}