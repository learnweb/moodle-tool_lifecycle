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
 * Table listing all courses triggered by a trigger.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\process;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses triggered by a trigger.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class triggered_courses_table extends \table_sql {

    /**
     * Builds a table of courses.
     * @param trigger $trigger trigger to show courses of, triggered or excluded courses
     * @param string $type whether triggered or excluded courses are shown
     * @param array $courseids
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($trigger, $type, $courseids) {
        parent::__construct('tool_lifecycle-courses-in-trigger');
        global $DB, $PAGE;

        if (!$courseids) {
            return;
        }

        $this->define_baseurl($PAGE->url);
        if ($type == 'triggered') {
            $this->caption = get_string('coursestriggered', 'tool_lifecycle', $trigger->instancename);
        } else if ($type == 'delayed') {
            $this->caption = get_string('coursesdelayed', 'tool_lifecycle', $trigger->instancename);
        } else {
            $this->caption = get_string('coursesexcluded', 'tool_lifecycle', $trigger->instancename);
        }
        $this->captionattributes = ['class' => 'ml-3'];
        $this->define_columns(['courseid', 'coursefullname', 'coursecategory']);
        $this->define_headers([
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
        ]);

        $fields = "c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname, cc.name as coursecategory";
        $from = "{course} c INNER JOIN {course_categories} cc ON c.category = cc.id";
        [$insql, $inparams] = $DB->get_in_or_equal($courseids);
        $where = "c.id ".$insql;
        $this->set_sql($fields, $from, $where, $inparams);
        $this->set_sortdata([['sortby' => 'fullname', 'sortorder' => '1']]);
    }

    /**
     * Render coursefullname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_coursefullname($row) {
        $courselink = \html_writer::link(course_get_url($row->courseid),
            format_string($row->coursefullname), ['target' => '_blank']);
        return $courselink . '<br><span class="secondary-info">' . $row->courseshortname . '</span>';
    }

    /**
     * Prints a customized "nothing to display" message.
     */
    public function print_nothing_to_display() {
        echo \html_writer::tag('h4', get_string('nothingtodisplay'), ['class' => 'm-2']);
    }
}
