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
 * Table listing all courses for a specific user and a specific subplugin
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\table;

use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\manager\interaction_manager;
use tool_cleanupcourses\manager\step_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class interaction_attention_table extends interaction_table {

    public function __construct($uniqueid, $courseids) {
        parent::__construct($uniqueid);
        global $PAGE;

        $fields = 'p.id as processid, c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname, s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname';
        $from = '{tool_cleanupcourses_process} p join ' .
            '{course} c on p.courseid = c.id join ' .
            '{tool_cleanupcourses_step} s '.
            'on p.workflowid = s.workflowid AND p.stepindex = s.sortindex';

        $ids = join(',', $courseids);

        $where = 'p.courseid IN ('. $ids . ')';


        $this->set_sql($fields, $from, $where, []);
        $this->define_baseurl($PAGE->url);
        $this->init();

    }

    /**
     * Render tools column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_tools($row) {
        $output = '';
        $step = step_manager::get_step_instance($row->stepinstanceid);

        $tools = interaction_manager::get_action_tools($step->subpluginname, $row->processid);
        foreach ($tools as $tool) {
            $output .= $this->format_icon_link($tool['action'], $row->processid, $step->id, $tool['alt']);
        }
        return $output;
    }

    /**
     * Render status column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_status($row) {
        $step = step_manager::get_step_instance($row->stepinstanceid);
        return interaction_manager::get_status_message($step->subpluginname, $row->processid);
    }

    /**
     * Util function for writing an action icon link
     *
     * @param string $action    URL parameter to include in the link
     * @param string $processid URL parameter to include in the link
     * @param int    $stepinstanceid ID of the step instance
     * @param string $alt       The string description of the link used as the title and alt text
     *
     * @return string The icon/link
     */
    private function format_icon_link($action, $processid, $stepinstanceid, $alt) {
        global $PAGE, $OUTPUT;

        $button = new \single_button(new \moodle_url($PAGE->url,
            array(
                'stepid' => $stepinstanceid,
                'action' => $action,
                'processid' => $processid,
                'sesskey' => sesskey()
            )), $alt
        );
        return $OUTPUT->render($button);
    }
}