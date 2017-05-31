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

use tool_cleanupcourses\manager\interaction_manager;
use tool_cleanupcourses\manager\step_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class interaction_table extends \table_sql {

    public function __construct($uniqueid, $stepid) {
        parent::__construct('tool_cleanupcourses_interaction_table');

        $stepinstance = step_manager::get_step_instance($stepid);
        global $PAGE, $USER;
        $this->set_sql('c.id as courseid, c.fullname as coursename, s.subpluginname as subpluginname ',
            '{tool_cleanupcourses_process} p join ' .
            '{course} c on p.courseid = c.id join ' .
            '{tool_cleanupcourses_step} s on p.stepid = s.id',
            "TRUE");
        $this->define_baseurl($PAGE->url);
        $this->init();

        $capability = interaction_manager::get_relevant_capability($stepinstance->subpluginname);
        $courses = get_user_capability_course($capability, $USER, false);
    }

    public function init() {
        $this->define_columns(['course', 'subplugin']);
        $this->define_headers([get_string('course'), get_string('step', 'tool_cleanupcourses')]);
        $this->setup();
    }

    /**
     * Render course column.
     * @param $row
     * @return string course link
     */
    public function col_course($row) {
        return \html_writer::link(course_get_url($row->courseid), $row->coursename);
    }

    /**
     * Render subplugin column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_subplugin($row) {

        $subpluginname = $row->subpluginname;

        return get_string('pluginname', 'cleanupcoursesstep_' . $subpluginname);
    }
}