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
use tool_cleanupcourses\manager\workflow_manager;
use tool_cleanupcourses\local\data\manual_trigger_tool;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class interaction_remaining_table extends interaction_table {

    /** manual_trigger_tool[] list of all available trigger tools. */
    private $availabletools;

    public function __construct($uniqueid, $courseids) {
        parent::__construct($uniqueid);
        global $PAGE;

        $this->availabletools = workflow_manager::get_manual_trigger_tools_for_active_workflows();

        $fields = 'c.id as courseid, p.id as processid, c.fullname as coursefullname, c.shortname as courseshortname ';
        $from = '{course} c left join ' .
            '{tool_cleanupcourses_process} p on p.courseid = c.id ';

        // TODO implement method get_Status
        $ids = join(',', $courseids);

        $where = 'c.id IN ('. $ids . ')';

        $this->set_sql($fields, $from, $where, []);
        $this->define_baseurl($PAGE->url);
        $this->init();
    }
    /**
     * Render status column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_status($row) {
        if ($row->processid !== null) {
            return interaction_manager::get_process_status_message($row->processid);
        }

        return '';
    }
    /**
     * Render tools column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_tools($row) {
        global $PAGE, $OUTPUT;

        if ($row->processid !== null) {
            return '';
        }

        $actions = [];
        foreach ($this->availabletools as $tool) {
            if (has_capability($tool->capability, \context_course::instance($row->courseid))) {
                $actions[$tool->triggerid] = new \action_menu_link_secondary(
                    new \moodle_url($PAGE->url, array('triggerid' => $tool->triggerid,
                        'courseid' => $row->courseid, 'sesskey' => sesskey())),
                    new \pix_icon($tool->icon, $tool->displayname, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                    $tool->displayname
                );
            }
        }

        $menu = new \action_menu();
        $menu->set_alignment(\action_menu::TR, \action_menu::BR);
        $menu->set_menu_trigger(get_string('action'));

        foreach ($actions as $action) {
            $menu->add($action);
        }

        return $OUTPUT->render($menu);
    }

}