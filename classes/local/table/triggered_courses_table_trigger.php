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
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\processor;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses triggered by a trigger.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class triggered_courses_table_trigger extends \table_sql {

    /**
     * Builds a table of courses.
     * @param trigger_subplugin $trigger of which the courses are listed
     * @param string $type of list: triggered or excluded
     * @param string $filterdata optional, term to filter the table by course id or -name
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($trigger, $type, $filterdata = '') {
        parent::__construct('tool_lifecycle-courses-in-trigger');
        global $PAGE;

        $workflow = workflow_manager::get_workflow($trigger->workflowid);

        $processor = new processor();
        $lib = lib_manager::get_trigger_lib($trigger->subpluginname);

        // Exclude delayed courses and sitecourse according to the workflow settings.
        $sitecourse = $workflow->includesitecourse ? [] : [1];
        if ($workflow->includedelayedcourses) {
            $delayedcourses = [];
        } else {
            $delayedcourses = array_merge(delayed_courses_manager::get_delayed_courses_for_workflow($workflow->id),
                delayed_courses_manager::get_globally_delayed_courses());
        }
        $excludedcourses = array_merge($sitecourse, $delayedcourses);
        if ($lib->check_course_code()) {
            [$triggercourses, , ] = $processor->get_triggercourses_forcounting_check_course(
                $trigger, $excludedcourses, $delayedcourses);
        } else {
            [$triggercourses, , ] = $processor->get_triggercourses_forcounting(
                $trigger, $excludedcourses, $delayedcourses);
        }
        if (!$triggercourses) {
            return;
        }

        $this->define_baseurl($PAGE->url);
        $a = new \stdClass();
        $a->title = $trigger->instancename;
        $a->courses = $triggercourses;
        if ($type == 'triggerid') {
            $this->caption = get_string('coursestriggered', 'tool_lifecycle', $a);
        } else if ($type == 'excluded') {
            $this->caption = get_string('coursesexcluded', 'tool_lifecycle', $a);
        }
        $this->captionattributes = ['class' => 'ml-3'];
        $columns = ['courseid', 'coursefullname', 'coursecategory'];
        $this->define_columns($columns);
        $headers = [
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
        ];
        $this->define_headers($headers);

        [$where, $whereparams] = $lib->get_course_recordset_where($trigger->id);
        $where = str_replace("{course}", "c", $where);
        // If exclude-trigger show selected courses to exclude.
        $where = str_replace("<>", "=", str_replace(" NOT ", " ", $where));

        $fields = "c.id as courseid, c.fullname as coursefullname, c.shortname as courseshortname, cc.name as coursecategory";
        $from = "{course} c LEFT JOIN {course_categories} cc ON c.category = cc.id ";

        if (!$workflow->includesitecourse) {
            $where .= " AND c.id <> 1 ";
        }

        if (!$workflow->includedelayedcourses && $excludedcourses) {
            $where .= " AND NOT c.id in (select courseid FROM {tool_lifecycle_delayed_workf} WHERE delayeduntil > :time1
                    AND workflowid = :workflowid)
                    AND NOT c.id in (select courseid FROM {tool_lifecycle_delayed} WHERE delayeduntil > :time2) ";
            $whereparams = array_merge($whereparams,
                ['time1' => time(), 'time2' => time(), 'workflowid' => $workflow->id]);
        }

        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where .= " AND {course}.id = $filterdata ";
            } else {
                $where .= " AND ( {course}.fullname LIKE '%$filterdata%' OR {course}.shortname LIKE '%$filterdata%')";
            }
        }

        $this->set_sql($fields, $from, $where, $whereparams);
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
        global $OUTPUT;
        echo \html_writer::div($OUTPUT->notification(get_string('nothingtodisplay', 'moodle'), 'info'),
            'm-3');
    }
}
