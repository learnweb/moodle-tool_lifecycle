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
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;
use tool_lifecycle\urls;

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

    /** @var string $type of the courses list: triggerid or delayed */
    private $type;

    /** @var int $triggerid Id of the trigger */
    private $triggerid;

    /** @var int $workflowid Id of the trigger's workflow */
    private $workflowid;

    /** @var int $triggerexclude if a trigger has setting exclude activated */
    private $triggerexclude;

    /**
     * Builds a table of courses.
     * @param int $numbercourses number of courses listed here
     * @param trigger_subplugin $trigger of which the courses are listed
     * @param string $type of list: triggered or excluded
     * @param string $filterdata optional, term to filter the table by course id or -name
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($numbercourses, $trigger, $type, $filterdata = '') {
        parent::__construct('tool_lifecycle-courses-in-trigger');
        global $PAGE, $SESSION;

        $workflow = workflow_manager::get_workflow($trigger->workflowid);

        $this->triggerid = $trigger->id;
        $this->workflowid = $workflow->id;
        $this->type = $type;

        $settings = settings_manager::get_settings($trigger->id, settings_type::TRIGGER);
        $this->triggerexclude = $settings['exclude'] ?? false;

        $this->define_baseurl($PAGE->url);
        $a = new \stdClass();
        $a->title = $trigger->instancename;
        $a->courses = $numbercourses;
        if ($type == 'triggerid') {
            $this->caption = get_string('coursestriggered', 'tool_lifecycle', $a);
        } else if ($type == 'excluded') {
            $this->caption = get_string('coursesexcluded', 'tool_lifecycle', $a);
        }
        $this->captionattributes = ['class' => 'ml-3'];
        $this->caption .= "&nbsp;&nbsp;&nbsp;".\html_writer::link(new \moodle_url(urls::WORKFLOW_DETAILS,
                ["wf" => $workflow->id, "showsql" => "1", "showtablesql" => "1", "showdetails" => "1"]),
                "&nbsp;&nbsp;&nbsp;", ["class" => "text-muted fs-6 text-decoration-none"]);

        $columns = ['courseid', 'coursefullname', 'coursecategory'];
        $this->define_columns($columns);
        $headers = [
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
        ];
        $this->define_headers($headers);

        $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
        [$where, $whereparams] = $lib->get_course_recordset_where($trigger->id);
        $where = str_replace("{course}", "c", $where);
        // If exclude-trigger show selected courses to exclude.
        $where = str_replace("<>", "=", str_replace(" NOT ", " ", $where));

        $fields = " c.id as courseid,
                    c.fullname as coursefullname,
                    c.shortname as courseshortname,
                    cc.name as coursecategory,
                    COALESCE(p.courseid, pe.courseid, 0) as hasprocess,
                    CASE
                        WHEN COALESCE(p.workflowid, 0) > COALESCE(pe.workflowid, 0) THEN p.workflowid
                        WHEN COALESCE(p.workflowid, 0) < COALESCE(pe.workflowid, 0) THEN pe.workflowid
                        ELSE 0
                    END as workflowid,
                    CASE
                        WHEN COALESCE(d.delayeduntil, 0) > COALESCE(dw.delayeduntil, 0) THEN d.delayeduntil
                        WHEN COALESCE(d.delayeduntil, 0) < COALESCE(dw.delayeduntil, 0) THEN dw.delayeduntil
                        ELSE 0
                    END as delay ";

        $from = " {course} c LEFT JOIN {course_categories} cc ON c.category = cc.id
                    LEFT JOIN {tool_lifecycle_process} p ON c.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON c.id = pe.courseid
                    LEFT JOIN {tool_lifecycle_delayed} d ON c.id = d.courseid
                    LEFT JOIN {tool_lifecycle_delayed_workf} dw ON c.id = dw.courseid ";

        if (!$workflow->includesitecourse) {
            $where .= " AND c.id <> 1 ";
        }

        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where .= " AND {course}.id = $filterdata ";
            } else {
                $where .= " AND ( {course}.fullname LIKE '%$filterdata%' OR {course}.shortname LIKE '%$filterdata%')";
            }
        }

        $debugsql = "SELECT ".$fields." FROM ".$from." WHERE ".$where;
        foreach ($whereparams as $key => $value) {
            $debugsql = str_replace(":".$key, $value, $debugsql);
        }
        $SESSION->debugtablesql = $debugsql;

        $this->set_sql($fields, $from, $where, $whereparams);
    }

    /**
     * Build the table from the fetched data.
     *
     * Take the data returned from the db_query and go through all the rows
     * processing each col using either col_{columnname} method or other_cols
     * method or if other_cols returns NULL then put the data straight into the
     * table.
     *
     * After calling this function, don't forget to call close_recordset.
     */
    public function build_table() {

        if (!$this->rawdata) {
            return;
        }

        $trigger = trigger_manager::get_instance($this->triggerid);
        $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);

        foreach ($this->rawdata as $row) {
            $response = $lib->check_course($row->courseid, $this->triggerid);

            if (!($response == trigger_response::exclude() || $response == trigger_response::trigger())) {
                continue;
            }
            if ($row->hasprocess) {
                continue;
            }
            if ($row->delay && $row->delay > time()) {
                continue;
            } else {
                if ($this->type == 'triggerid') {
                    if ($response == trigger_response::trigger()) {
                        $formattedrow = $this->format_row($row);
                        $this->add_data_keyed($formattedrow, $this->get_row_class($row));
                    }
                } else if ($this->type == 'excluded') {
                    if ($response == trigger_response::exclude() || $this->triggerexclude) {
                        $formattedrow = $this->format_row($row);
                        $this->add_data_keyed($formattedrow, $this->get_row_class($row));
                    }
                }
            }
        }
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
        echo \html_writer::div("&nbsp;&nbsp;&nbsp;".\html_writer::link(new \moodle_url(urls::WORKFLOW_DETAILS,
                ["wf" => $this->workflowid, "showsql" => "1", "showtablesql" => "1", "showdetails" => "1"]),
                "&nbsp;&nbsp;&nbsp;", ["class" => "text-muted fs-6 text-decoration-none"]));
    }
}
