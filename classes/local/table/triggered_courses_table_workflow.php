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
 * Table listing all courses triggered by a workflow's triggers.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\delayed_courses_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\processor;
use tool_lifecycle\urls;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses triggered by a workflow's triggers.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class triggered_courses_table_workflow extends \table_sql {

    /** @var string $type of the courses list: triggeredworkflow, delayed or used */
    private $type;

    /** @var int $workflowid Id of the workflow */
    private $workflowid;

    /** @var bool $selectable Is workflow a draft */
    private $selectable = false;

    /** @var bool $coursecheckcode use course_check function to trigger courses */
    private $checkcoursecode = false;

    /**
     * Builds a table of courses.
     * @param int $courses number of courses to list
     * @param workflow $workflow of which the courses are listed
     * @param string $type of list: triggeredworkflow, delayed, used
     * @param string $filterdata optional, term to filter the table by course id or -name
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($courses, $workflow, $type, $filterdata = '') {
        parent::__construct('tool_lifecycle-courses-in-trigger');
        global $PAGE, $SESSION;

        $this->define_baseurl($PAGE->url);
        $this->type = $type;
        $this->workflowid = $workflow->id;

        $a = new \stdClass();
        $a->title = $workflow->title;
        $a->courses = $courses;
        if ($type == 'triggeredworkflow') {
            $this->caption = get_string('coursestriggeredworkflow', 'tool_lifecycle', $a);
            $this->selectable = workflow_manager::is_active($workflow->id);
        } else if ($type == 'delayed') {
            $this->caption = get_string('coursesdelayed', 'tool_lifecycle', $a);
        } else if ($type == 'used') {
            $this->caption = get_string('coursesused', 'tool_lifecycle', $a);
        }
        $this->caption .= "&nbsp;&nbsp;&nbsp;".\html_writer::link(new \moodle_url(urls::WORKFLOW_DETAILS,
                ["wf" => $workflow->id, "showsql" => "1", "showtablesql" => "1", "showdetails" => "1"]),
                "&nbsp;&nbsp;&nbsp;", ["class" => "text-muted fs-6 text-decoration-none"]);
        $this->captionattributes = ['class' => 'ml-3'];

        $columns = ['courseid', 'coursefullname', 'coursecategory'];
        if ($type == 'triggeredworkflow' && $this->selectable) {
            $columns[] = 'tools';
        } else if ($type == 'delayed') {
            $columns[] = 'delayeduntil';
            $columns[] = 'tools';
        } else if ($type == 'used') {
            $columns[] = 'otherworkflow';
        }
        $this->define_columns($columns);
        $headers = [
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
        ];
        if ($type == 'triggeredworkflow' && $this->selectable) {
            $headers[] = get_string('tools', 'tool_lifecycle');
        } else if ($type == 'delayed') {
            $headers[] = get_string('delayeduntil', 'tool_lifecycle');
            $headers[] = get_string('tools', 'tool_lifecycle');
        } else if ($type == 'used') {
            $headers[] = get_string('workflow', 'tool_lifecycle');
        }
        $this->define_headers($headers);

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
        if ($type == 'used') {
            $fields .= ", COALESCE(wfp.title, wfpe.title) as otherworkflow";
        }
        $from = " {course} c LEFT JOIN {course_categories} cc ON c.category = cc.id
                    LEFT JOIN {tool_lifecycle_process} p ON c.id = p.courseid
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON c.id = pe.courseid
                    LEFT JOIN {tool_lifecycle_delayed} d ON c.id = d.courseid
                    LEFT JOIN {tool_lifecycle_delayed_workf} dw ON c.id = dw.courseid ";
        if ($type == 'used') {
            $from .= " LEFT JOIN {tool_lifecycle_workflow} wfp ON p.workflowid = wfp.id
                       LEFT JOIN {tool_lifecycle_workflow} wfpe ON pe.workflowid = wfpe.id";
        }

        $where = 'true';
        $inparams = [];
        $triggers = trigger_manager::get_triggers_for_workflow($workflow->id);
        $andor = ($workflow->andor ?? 0) == 0 ? 'AND' : 'OR';
        foreach ($triggers as $trigger) {
            $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
            if ($lib->is_manual_trigger()) {
                continue;
            } else {
                if (!$this->checkcoursecode) {
                    $this->checkcoursecode = $lib->check_course_code();
                }
                [$sql, $params] = $lib->get_course_recordset_where($trigger->id);
                $sql = preg_replace("/{course}/", "c", $sql, 1);
                if (!empty($sql)) {
                    $where .= " $andor " . $sql;
                    $inparams = array_merge($inparams, $params);
                }
            }
        }

        if (!$workflow->includesitecourse) {
            $where = "($where) AND c.id <> 1 ";
        }
        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where = "($where) AND c.id = $filterdata ";
            } else {
                $where = "($where) AND (c.fullname LIKE '%$filterdata%' OR c.shortname LIKE '%$filterdata%')";
            }
        }

        $debugsql = $fields.$from.$where;
        foreach ($inparams as $key => $value) {
            $debugsql = str_replace(":".$key, $value, $debugsql);
        }
        $SESSION->debugtablesql = $debugsql;

        $this->set_sql($fields, $from, $where, $inparams);
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

        if ($this->checkcoursecode) {
            $autotriggers = [];
            $triggers = trigger_manager::get_triggers_for_workflow($this->workflowid);
            foreach ($triggers as $trigger) {
                $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
                if ($lib->is_manual_trigger()) {
                    continue;
                } else {
                    $autotriggers[] = $trigger;
                }
            }
        }
        foreach ($this->rawdata as $row) {
            if ($this->checkcoursecode) {
                $action = false;
                foreach ($autotriggers as $trigger) {
                    $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
                    $response = $lib->check_course($row->id, $trigger->id);
                    if ($response == trigger_response::next()) {
                        if (!$action) {
                            $action = true;
                        }
                        continue;
                    }
                    if ($response == trigger_response::exclude()) {
                        if (!$action) {
                            $action = true;
                        }
                        continue;
                    }
                    if ($response == trigger_response::trigger()) {
                        continue;
                    }
                }
                if (!$action) {
                    if ($row->hasprocess) {
                        if ($this->workflowid && ($row->workflowid != $this->workflowid)) {
                            if ($this->type == 'used') {
                                $formattedrow = $this->format_row($row);
                                $this->add_data_keyed($formattedrow, $this->get_row_class($row));
                            }
                        }
                    } else if ($row->delay && $row->delay > time()) {
                        if ($this->type == 'delayed') {
                            $formattedrow = $this->format_row($row);
                            $this->add_data_keyed($formattedrow, $this->get_row_class($row));
                        }
                    } else {
                        if ($this->type == 'triggeredworkflow') {
                            $formattedrow = $this->format_row($row);
                            $this->add_data_keyed($formattedrow, $this->get_row_class($row));
                        }
                    }
                }
            } else {
                if ($row->hasprocess) {
                    if ($row->workflowid && ($row->workflowid != $this->workflowid)) {
                        if ($this->type == 'used') {
                            $formattedrow = $this->format_row($row);
                            $this->add_data_keyed($formattedrow, $this->get_row_class($row));
                        }
                    }
                } else if ($row->delay && $row->delay > time()) {
                    if ($this->type == 'delayed') {
                        $formattedrow = $this->format_row($row);
                        $this->add_data_keyed($formattedrow, $this->get_row_class($row));
                    }
                } else {
                    if ($this->type == 'triggeredworkflow') {
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
     * Render delayeduntil column.
     * @param object $row Row data.
     * @return string date
     * @throws \coding_exception
     */
    public function col_delayeduntil($row) {
        if ($delay = delayed_courses_manager::get_course_delayed($row->courseid)) {
            return userdate($delay, get_string('strftimedatetime', 'core_langconfig'));
        }
        return "-";
    }

    /**
     * Render tools column.
     *
     * @param object $row Row data.
     * @return string html of the delete button
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_tools($row) {
        global $OUTPUT, $PAGE;

        $button = "";
        if ($this->type == 'delayed') {
            $params = [
                'action' => 'deletedelay',
                'cid' => $row->courseid,
                'sesskey' => sesskey(),
                'wf' => $this->workflowid,
            ];
            $button = new \single_button(new \moodle_url(urls::WORKFLOW_DETAILS, $params),
                get_string('delete_delay', 'tool_lifecycle'));
        } else if ($this->type == 'triggeredworkflow' && $this->selectable) {
            $params = [
                'action' => 'select',
                'cid' => $row->courseid,
                'sesskey' => sesskey(),
                'wf' => $this->workflowid,
            ];
            $button = new \single_button(new \moodle_url($PAGE->url, $params), get_string('select'));
        }
        if ($button) {
            return $OUTPUT->render($button);
        } else {
            return '';
        }
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
