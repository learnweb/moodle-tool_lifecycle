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

class interaction_table extends \table_sql {

    /** @var step_subplugin $stepinstance */
    private $stepinstance;

    public function __construct($uniqueid, $stepid) {
        parent::__construct('tool_cleanupcourses_interaction_table');

        $this->stepinstance = step_manager::get_step_instance($stepid);
        global $PAGE, $USER, $DB;

        $fields = 'p.id as processid, c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname';
        $from = '{tool_cleanupcourses_process} p join ' .
                '{course} c on p.courseid = c.id join ' .
                '{tool_cleanupcourses_step} s on p.stepid = s.id';
        if (interaction_manager::show_relevant_courses_instance_dependent($this->stepinstance->subpluginname)) {
            $where = 'p.stepid = :stepid';
            $params = array('stepid' => $stepid);
        } else {
            $where = 's.subpluginname = :subpluginname';
            $params = array('subpluginname' => $this->stepinstance->subpluginname);
        }

        $capability = interaction_manager::get_relevant_capability($this->stepinstance->subpluginname);
        $courses = get_user_capability_course($capability, $USER, false);
        if ($courses) {
            $listofcourseids = array_reduce($courses, function ($course1, $course2) {
                if (!$course1) {
                    return $course2->id;
                }
                if (!$course2) {
                    return $course1->id;
                }
                return $course1->id . ',' . $course2->id;
            });
            $where .= ' AND c.id in (:listofcourseids)';
            $params['listofcourseids'] = $listofcourseids;
        } else {
            $where .= ' AND FALSE';
        }

        $this->set_sql($fields, $from, $where, $params);
        $this->define_baseurl($PAGE->url);
        $this->init();

    }

    public function init() {
        $this->define_columns(['courseid', 'courseshortname', 'coursefullname', 'tools', 'status']);
        $this->define_headers([
            get_string('course'),
            get_string('shortnamecourse'),
            get_string('fullnamecourse'),
            get_string('tools', 'tool_cleanupcourses'),
            get_string('status', 'tool_cleanupcourses'),
            ]);
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
     * Render tools column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_tools($row) {
        $output = '';
        $tools = interaction_manager::get_action_tools($this->stepinstance->subpluginname, $row->processid);
        foreach ($tools as $tool) {
            $output .= $this->format_icon_link($tool['action'], $row->processid, $tool['icon'], $tool['alt']);
        }
        return $output;
    }

    /**
     * Render status column.
     * @param $row
     * @return string pluginname of the subplugin
     */
    public function col_status($row) {
        return interaction_manager::get_status_message($this->stepinstance->subpluginname, $row->processid);
    }

    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $processid URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     */
    private function format_icon_link($action, $processid, $icon, $alt) {
        global $PAGE, $OUTPUT;

        return $OUTPUT->action_icon(new \moodle_url($PAGE->url,
                array(
                    'stepid' => $this->stepinstance->id,
                    'action' => $action,
                    'processid' => $processid,
                    'sesskey' => sesskey()
                )),
                new \pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                null , array('title' => $alt)) . ' ';
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->heading(get_string('nocoursestodisplay', 'tool_cleanupcourses'));
    }
}