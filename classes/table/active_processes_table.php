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
namespace tool_cleanupcourses\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class active_processes_table extends \table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_sql('c.id as courseid, ' .
            'c.fullname as coursefullname, ' .
            'c.shortname as courseshortname, ' .
            'instancename as instancename ',
            '{tool_cleanupcourses_process} p join ' .
            '{course} c on p.courseid = c.id join ' .
            '{tool_cleanupcourses_step} s '.
            'on p.workflowid = s.workflowid AND p.stepindex = s.sortindex',
            "TRUE");
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    public function init() {
        $this->define_columns(['courseid', 'courseshortname', 'coursefullname', 'subplugin']);
        $this->define_headers([
            get_string('course'),
            get_string('shortnamecourse'),
            get_string('fullnamecourse'),
            get_string('step', 'tool_cleanupcourses')]);
        $this->setup();
    }

    /**
     * Render courseid column.
     * @param $row
     * @return string course link
     */
    public function col_courseid($row) {
        return \html_writer::link(course_get_url($row->courseid), $row->courseid);
    }

    /**
     * Render courseshortname column.
     * @param $row
     * @return string course link
     */
    public function col_courseshortname($row) {
        return \html_writer::link(course_get_url($row->courseid), $row->courseshortname);
    }

    /**
     * Render coursefullname column.
     * @param $row
     * @return string course link
     */
    public function col_coursefullname($row) {
        return \html_writer::link(course_get_url($row->courseid), $row->coursefullname);
    }

    /**
     * Render subplugin column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_subplugin($row) {

        return $row->instancename;
    }
}