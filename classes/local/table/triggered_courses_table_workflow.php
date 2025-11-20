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

use core\exception\moodle_exception;
use core_date;
use stdClass;
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

    /** @var string $type of the courses list: triggeredworkflow, delayed, used, processes */
    private $type;

    /** @var int $workflowid Id of the workflow */
    private $workflowid;

    /** @var bool $selectable Is workflow a draft */
    private $selectable = false;

    /** @var bool $coursecheckcode use course_check function to trigger courses */
    private $checkcoursecode = false;

    /** @var int $otherwf the number of courses in another workflow on this page */
    public $otherwf = 0;

    /** @var int $delayed the number of courses that are delayed on this page */
    public $delayed = 0;

    /** @var int $triggered the number of courses that are triggered on this page */
    public $triggered = 0;

    /** @var int $tablerows number of table rows effectively written on this page */
    public $tablerows = 0;

    /** @var int $excludedbycheckcourse number of courses excluded by function check_course on this page */
    public $excludedbycheckcourse = 0;

    /**
     * Builds a table of courses.
     * @param workflow $workflow of which the courses are listed
     * @param string $filterdata optional, term to filter the table by course id or -name
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($workflow, $filterdata = '') {
        parent::__construct('tool_lifecycle-trigger-courses-in-workflow');
        global $PAGE, $SESSION;

        $this->define_baseurl($PAGE->url);
        $this->workflowid = $workflow->id;

        $a = new \stdClass();
        $a->title = $workflow->title;
        $this->caption = get_string('coursestriggeredworkflow', 'tool_lifecycle', $a);
        $this->selectable = workflow_manager::is_active($workflow->id);
        $this->captionattributes = ['class' => 'ml-3'];

        $columns = ['courseid', 'coursefullname', 'coursecategory', 'tools'];
        $this->define_columns($columns);
        $headers = [
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('coursecategory', 'moodle'),
            get_string('tools', 'tool_lifecycle'),
        ];
        $this->define_headers($headers);

        $fields = " c.id as courseid,
                    c.fullname as coursefullname,
                    c.shortname as courseshortname,
                    cc.name as coursecategory,
                    pe.id as errorid,
                    COALESCE(p.courseid, pe.courseid, 0) as hasprocess,
                    COALESCE(po.workflowid, peo.workflowid, 0) as hasotherwfprocess,
                    CASE
                        WHEN COALESCE(d.delayeduntil, 0) > COALESCE(dw.delayeduntil, 0) THEN d.delayeduntil
                        WHEN COALESCE(d.delayeduntil, 0) < COALESCE(dw.delayeduntil, 0) THEN dw.delayeduntil
                        ELSE 0
                    END as delaycourse ";
        $from = " {course} c LEFT JOIN {course_categories} cc ON c.category = cc.id
                    LEFT JOIN {tool_lifecycle_process} p ON c.id = p.courseid AND p.workflowid = $workflow->id
                    LEFT JOIN {tool_lifecycle_proc_error} pe ON c.id = pe.courseid AND pe.workflowid = $workflow->id
                    LEFT JOIN {tool_lifecycle_process} po ON c.id = po.courseid AND po.workflowid <> $workflow->id
                    LEFT JOIN {tool_lifecycle_proc_error} peo ON c.id = peo.courseid AND peo.workflowid <> $workflow->id
                    LEFT JOIN {tool_lifecycle_delayed} d ON c.id = d.courseid
                    LEFT JOIN {tool_lifecycle_delayed_workf} dw ON c.id = dw.courseid AND dw.workflowid=$workflow->id";

        $triggers = trigger_manager::get_triggers_for_workflow($workflow->id);
        $andor = ($workflow->andor ?? 0) == 0 ? 'AND' : 'OR';
        $where = $andor == 'AND' ? 'true ' : 'false ';
        $inparams = [];
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

        if ($filterdata) {
            if (is_numeric($filterdata)) {
                $where = "($where) AND c.id = $filterdata ";
            } else {
                $where = "($where) AND (c.fullname LIKE '%$filterdata%' OR c.shortname LIKE '%$filterdata%')";
            }
        }

        $debugsql = "SELECT ".$fields." FROM ".$from." WHERE ".$where;
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
     * method or if other_cols return NULL, then put the data straight into the
     * table.
     *
     * After calling this function, remember to call close_recordset.
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
        $course = new stdClass();
        foreach ($this->rawdata as $row) {
            $validrow = true;
            if ($this->checkcoursecode) {
                $row->status = trigger_response::trigger();
                foreach ($autotriggers as $trigger) {
                    $lib = lib_manager::get_automatic_trigger_lib($trigger->subpluginname);
                    $course->id = $row->courseid;
                    $response = $lib->check_course($course, $trigger->id);
                    if ($response == trigger_response::exclude()) {
                        $validrow = false;
                        break;
                    }
                }
            }
            if (!$validrow) {
                $row->status = trigger_response::exclude();
                $this->excludedbycheckcourse++;
            }
            $formattedrow = $this->format_row($row);
            $this->add_data_keyed($formattedrow, $this->get_row_class($row));
            $this->tablerows++;
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
        global $USER;
        $delay = delayed_courses_manager::get_course_delayed($row->courseid);
        $delaywf = delayed_courses_manager::get_course_delayed_workflow($row->courseid, $this->workflowid);
        if ($delay || $delaywf) {
            return userdate(max($delay, $delaywf), get_string('strftimedatetime', 'core_langconfig'),
                core_date::get_user_timezone($USER));
        }
        return "-";
    }

    /**
     * Render tools column.
     *
     * @param object $row Row data.
     * @return string HTML of the select button or nothing
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_tools($row) {
        global $OUTPUT, $PAGE;
        $out = "";
        if ($row->hasotherwfprocess) {
            $this->otherwf++;
            $out .= \html_writer::div(get_string('alreadyinprocessotherworkflow', 'tool_lifecycle'),
                'text-warning');
        }
        if ($row->delaycourse && $row->delaycourse > time()) {
            $this->delayed++;
            $out .= \html_writer::div(get_string('delayed', 'tool_lifecycle'), 'text-info');
        }
        if ($row->status && !($row->status == trigger_response::trigger())) {
            $out .= \html_writer::div(get_string('excludedbycoursecode', 'tool_lifecycle'),
                'text-warning');
        }
        if ($this->selectable) {
            $params = [
                'action' => 'select',
                'cid' => $row->courseid,
                'sesskey' => sesskey(),
                'wf' => $this->workflowid,
            ];
            $button = new \single_button(new \moodle_url($PAGE->url, $params), get_string('select'));
            $out .= $OUTPUT->render($button);
        }
        if (!$out) {
            $this->triggered++;
            $out = \html_writer::span(get_string('statusok'), 'text-success');
        }

        return $out;
    }

    /**
     * Render processtype column.
     *
     * @param object $row Row data.
     * @return string of link to processerror-page or string 'step'
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_processtype($row) {
        if ($row->errorid) {
            $params = [
                'workflow' => $this->workflowid,
                'course' => $row->courseid,
            ];
            return \html_writer::link(
                new \moodle_url(urls::PROCESS_ERRORS, $params),
                get_string('process_error', 'tool_lifecycle'),
                ['class' => 'error']);
        } else {
            return get_string('step', 'tool_lifecycle');
        }
    }

    /**
     * Render otherworkflow column.
     * @param object $row Row data.
     * @return string date
     * @throws \coding_exception
     */
    public function col_otherworkflow($row) {
        global $DB;
        if ($row->hasotherwfprocess) {
            if ($workflowname = $DB->get_field('tool_lifecycle_workflow', 'title', ['id' => $row->hasotherwfprocess])) {
                return $workflowname;
            } else {
                return "-";
            }
        }
        return "-";
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_finish() {
        $a = new \stdClass();
        $a->otherwf = $this->otherwf;
        $a->delayed = $this->delayed;
        $a->triggered = $this->triggered;
        $a->tablerows = $this->tablerows;
        $cont = get_string('numbersotherwfordelayed', 'tool_lifecycle', $a);
        if ($this->checkcoursecode) {
            $cont .= " / ".$this->excludedbycheckcourse." ".
                get_string('excludedbycoursecode', 'tool_lifecycle');
        }
        echo \html_writer::div($cont, 'm-3');
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
