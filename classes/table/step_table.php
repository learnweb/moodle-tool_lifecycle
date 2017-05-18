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
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\table;

use tool_cleanupcourses\manager\step_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../lib.php');

class step_table extends \table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_sql("id, name, instancename, followedby", '{tool_cleanupcourses_step}', "TRUE");
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    public function init() {
        $this->define_columns(['instancename', 'name', 'followedby', 'edit', 'delete']);
        $this->define_headers([
            get_string('step_instancename', 'tool_cleanupcourses'),
            get_string('step_name', 'tool_cleanupcourses'),
            get_string('step_followedby', 'tool_cleanupcourses'),
            get_string('step_edit', 'tool_cleanupcourses'),
            get_string('step_delete', 'tool_cleanupcourses'),
            ]);
        $this->sortable(false);
        $this->setup();
    }

    /**
     * Render name column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_name($row) {

        $name = $row->name;

        return get_string('pluginname', 'cleanupcoursesstep_' . $name);
    }

    /**
     * Render followedby column.
     * @param $row
     * @return string action button for enabling/disabling of the subplugin
     */
    public function col_followedby($row) {
        global $PAGE, $OUTPUT;

        $manager = new step_manager();
        $steps = $manager->get_step_instances();

        // Determine, which step is selected.
        $selected = '';
        if ($row->followedby !== null) {
            $selected = (int) $row->followedby;
        }

        return $OUTPUT->single_select(new \moodle_url($PAGE->url,
            array('action' => ACTION_FOLLOWEDBY_STEP, 'subplugin' => $row->id, 'sesskey' => sesskey())),
            'followedby', $steps, $selected);
    }

    /**
     * Render edit column.
     * @param $row
     * @return string action button for editing of the subplugin
     */
    public function col_edit($row) {

        $alt = 'edit';
        $icon = 't/edit';
        $action = ACTION_STEP_INSTANCE_FORM;

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
        $action = ACTION_STEP_INSTANCE_DELETE;

        return  $this->format_icon_link($action, $row->id, $icon, get_string($alt));
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
                array('action' => $action, 'subplugin' => $subpluginid, 'sesskey' => sesskey())),
                new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                null , array('title' => $alt)) . ' ';
    }

}