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
 * Life Cycle Admin Approve Step
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_adminapprove;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Show the steps available.
 */
class step_table extends \table_sql {

    /**
     * Construct the table.
     * @throws \coding_exception
     */
    public function __construct() {
        parent::__construct('lifecyclestep_adminapprove-steptable');
        $this->define_baseurl("/admin/tool/lifecycle/step/adminapprove/index.php");
        $this->define_columns(['stepname', 'workflowname', 'courses']);
        $this->define_headers(
            [get_string('step', 'tool_lifecycle'), get_string('workflow', 'lifecyclestep_adminapprove'),
                get_string('amount_courses', 'lifecyclestep_adminapprove')]);
        $this->set_attribute('id', 'adminapprove-steptable');
        $this->sortable(false);
        $fields = 's.id as id, s.instancename as stepname, w.title as workflowname, b.courses as courses';
        $from = '( ' .
            'SELECT p.workflowid, p.stepindex, COUNT(1) as courses FROM {lifecyclestep_adminapprove} a ' .
            'JOIN {tool_lifecycle_process} p ON p.id = a.processid ' .
            'WHERE a.status = 0 ' .
            'GROUP BY p.workflowid, p.stepindex ' .
        ') b ' .
        'JOIN {tool_lifecycle_step} s ON s.workflowid = b.workflowid AND s.sortindex = b.stepindex ' .
        'JOIN {tool_lifecycle_workflow} w ON w.id = b.workflowid';
        $this->set_sql($fields, $from, 'TRUE');
    }

    /**
     * Show the stepname.
     * @param object $row
     * @return string
     */
    public function col_stepname($row) {
        return '<div data-stepid="' . $row->id . '" hidden></div> <a href="approvestep.php?stepid='. $row->id .'">'
                . $row->stepname . '</a>';
    }

    /**
     * Print information if table does not have content.
     * @return void
     * @throws \coding_exception
     */
    public function print_nothing_to_display() {
        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();
        $this->print_initials_bar();
        echo get_string('nostepstodisplay', 'lifecyclestep_adminapprove');
    }

}
