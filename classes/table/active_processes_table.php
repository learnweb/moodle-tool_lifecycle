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
 * Table listing active processes
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class active_processes_table extends \table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_sql('c.fullname as course, s.name as name ',
            '{tool_cleanupcourses_process} p join ' .
            '{course} c on p.courseid = c.id join ' .
            '{tool_cleanupcourses_step} s on p.stepid = s.id',
            "TRUE");
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    public function init() {
        $this->define_columns(['course', 'subplugin']);
        $this->define_headers([get_string('course'), get_string('step', 'tool_cleanupcourses')]);
        $this->setup();
    }

    /**
     * Render subplugin column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_subplugin($row) {

        $name = $row->name;

        return get_string('pluginname', 'cleanupcoursesstep_' . $name);
    }
}