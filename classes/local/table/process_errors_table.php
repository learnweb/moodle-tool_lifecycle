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
 * Table listing all process errors
 *
 * @package tool_lifecycle
 * @copyright  2021 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use core\exception\coding_exception;
use core_date;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all process errors
 *
 * @package tool_lifecycle
 * @copyright  2021 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_errors_table extends \table_sql {

    /**
     * @var array "cached" lang strings
     */
    private $strings;

    /**
     * Constructor for process_errors_table.
     *
     * @param object $filterdata the previously submitted filter data
     * @throws \coding_exception
     */
    public function __construct($filterdata) {
        global $OUTPUT;

        parent::__construct('tool_lifecycle-process_errors');

        $this->strings = [
                'proceed' => get_string('proceed', 'tool_lifecycle'),
                'rollback' => get_string('rollback', 'tool_lifecycle'),
                'delete' => get_string('deleteprocesserror', 'tool_lifecycle'),
        ];

        $fields = 'c.id, c.fullname as course,
            w.id as workflowid, w.title as workflow,
            s.id as stepid, s.instancename as step,
            pe.id as errorid, pe.courseid, pe.errormessage, pe.errortrace, pe.errortimecreated';

        $from = '{tool_lifecycle_proc_error} pe ' .
            'LEFT JOIN {tool_lifecycle_workflow} w ON pe.workflowid = w.id ' .
            'LEFT JOIN {tool_lifecycle_step} s ON pe.workflowid = s.workflowid AND pe.stepindex = s.sortindex ' .
            'LEFT JOIN {course} c ON pe.courseid = c.id ';
        $where = 'TRUE';
        $params = [];
        $workflow = $filterdata->workflow ?? null;
        if ($workflow) {
            $where .= ' AND w.id = :workflow';
            $params['workflow'] = $workflow;
        }
        $step = $filterdata->step ?? null;
        if ($step) {
            $where .= ' AND s.id = :step';
            $params['step'] = $step;
        }
        $course = $filterdata->course ?? null;
        if ($course) {
            $where .= ' AND c.id = :course';
            $params['course'] = $course;
        }
        $this->set_sql($fields, $from, $where, $params);
        $this->column_nosort = ['select', 'tools'];
        $this->define_columns(['select', 'workflow', 'step', 'courseid', 'course', 'errortime', 'error', 'tools']);
        $this->define_headers([
                $OUTPUT->render(new \core\output\checkbox_toggleall('procerrors-table', true, [
                        'id' => 'select-all-procerrors',
                        'name' => 'select-all-procerrors',
                        'label' => get_string('selectall'),
                        'labelclasses' => 'sr-only',
                        'classes' => 'm-1',
                        'checked' => false,
                ])),
                get_string('workflow', 'tool_lifecycle'),
                get_string('step', 'tool_lifecycle'),
                get_string('courseid', 'tool_lifecycle'),
                get_string('course'),
                get_string('errortime', 'tool_lifecycle'),
                get_string('error'),
                get_string('tools', 'tool_lifecycle'),
        ]);
    }

    /**
     * Render error column.
     *
     * @param object $row Row data.
     * @return string error cell
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_error($row) {
        return "<details><summary>" .
                nl2br(htmlentities($row->errormessage, ENT_COMPAT)) .
                "</summary><code>" .
                nl2br(htmlentities($row->errortrace, ENT_COMPAT)) .
                "</code></details>";
    }

    /**
     * Render time of error.
     *
     * @param object $row Row data.
     * @return string errortime cell
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_errortime($row) {
        global $USER;

        return userdate($row->errortimecreated,
            get_string('strftimedatetimeshortaccurate', 'core_langconfig'),
            core_date::get_user_timezone($USER));
    }

    /**
     * Render tools column.
     *
     * @param object $row Row data.
     * @return string pluginname of the subplugin
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_tools($row) {
        global $OUTPUT;

        $actionmenu = new \action_menu();
        if ($row->workflowid && $row->stepid ?? false) {
            $actionmenu->add_primary_action(
                new \action_menu_link_primary(
                    new \moodle_url('', ['action' => 'proceed', 'id[]' => $row->errorid, 'sesskey' => sesskey()]),
                    new \pix_icon('e/tick', $this->strings['proceed']),
                    $this->strings['proceed']
                )
            );
            $actionmenu->add_primary_action(
                new \action_menu_link_primary(
                    new \moodle_url('', ['action' => 'rollback', 'id[]' => $row->errorid, 'sesskey' => sesskey()]),
                    new \pix_icon('e/undo', $this->strings['rollback']),
                    $this->strings['rollback']
                )
            );
        } else {
            $actionmenu->add_primary_action(
                new \action_menu_link_primary(
                    new \moodle_url('', ['action' => 'delete', 'id[]' => $row->errorid, 'sesskey' => sesskey()]),
                    new \pix_icon('t/delete', $this->strings['delete']),
                    $this->strings['delete']
                )
            );
        }
        return $OUTPUT->render($actionmenu);
    }

    /**
     * Generate the select column.
     *
     * @param \stdClass $data
     * @return string
     * @throws \coding_exception
     * @throws coding_exception
     */
    public function col_select($data) {
        global $OUTPUT;

        if ($data->workflowid && $data->stepid) {
            $checkbox = new \core\output\checkbox_toggleall('procerrors-table', false, [
                'classes' => 'usercheckbox m-1',
                'id' => 'procerror' . $data->id,
                'name' => 'procerror-select',
                'value' => $data->id,
                'checked' => false,
                'label' => get_string('selectitem', 'moodle', $data->id),
                'labelclasses' => 'accesshide',
            ]);

            return $OUTPUT->render($checkbox);
        } else {
            return '';
        }
    }

    /**
     * Override the table show_hide_link to not show for select column.
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function show_hide_link($column, $index) {
        if ($index > 0) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_start() {
        global $OUTPUT;
        parent::wrap_html_start();
        echo "<br>";

        $actionmenu = new \action_menu();
        $actionmenu->add_secondary_action(
                new \action_menu_link_secondary(
                        new \moodle_url(''),
                        new \pix_icon('e/tick', $this->strings['proceed']),
                        $this->strings['proceed'],
                        ['data-lifecycle-action' => 'proceed']
                )
        );

        $actionmenu->add_secondary_action(
                new \action_menu_link_secondary(
                        new \moodle_url(''),
                        new \pix_icon('e/undo', $this->strings['rollback']),
                        $this->strings['rollback'],
                        ['data-lifecycle-action' => 'rollback']
                )
        );

        $actionmenu->set_menu_trigger(get_string('forselected', 'tool_lifecycle'));
        echo $OUTPUT->render_action_menu($actionmenu);
    }
}
