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
        $this->define_columns(['instancename', 'name', 'followedby']);
        $this->define_headers([
            get_string('step_instancename', 'tool_cleanupcourses'),
            get_string('step_name', 'tool_cleanupcourses'),
            get_string('step_followedby', 'tool_cleanupcourses'),
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
        $options = array();
        foreach ($steps as $id => $step) {
            $options[$id] = $step->instancename;
        }

        // Determine, which step is selected.
        $selected = '';
        if ($row->followedby !== null) {
            $selected = (int) $row->followedby;
        }

        return $OUTPUT->single_select(new \moodle_url($PAGE->url,
            array('action' => ACTION_FOLLOWEDBY_STEP, 'subplugin' => $row->id, 'sesskey' => sesskey())),
            'followedby', $options, $selected);
    }

}