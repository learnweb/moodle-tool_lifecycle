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
 * Life Cycle Admin Approve Step. Form with steps to proceed.
 *
 * @package lifecyclestep_adminapprove
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace lifecyclestep_adminapprove;

use core\output\html_writer;
use moodle_url;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\settings_type;

/**
 * form for approve step actions
 */
class approvestep_form extends \moodleform {
    /** @var int step identifier */
    private $stepid;
    /** @var int course identifier */
    private $courseid;
    /** @var int course category */
    private $category;
    /** @var string coursename */
    private $coursename;
    /** @var int pagesize */
    private $pagesize;

    /**
     * constructor
     * @param int $stepid step id
     * @param int $courseid course id
     * @param int $category course category
     * @param string $coursename course name
     * @param int $pagesize page size
     */
    public function __construct($stepid, $courseid, $category, $coursename, $pagesize = 0) {
        $this->stepid = $stepid;
        $this->courseid = $courseid;
        $this->category = $category;
        $this->coursename = $coursename;
        $this->pagesize = $pagesize;
        parent::__construct();
    }

    /**
     * form definition
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     * @throws \dml_exception
     */
    protected function definition() {
        global $PAGE;
        $mform = $this->_form;

        // Create table with optional link for displaying all records.
        $table = new decision_table($this->stepid, $this->courseid, $this->category, $this->coursename);
        $table->define_baseurl($PAGE->url);

        // Put the table with input elements into the form so that we get the selection on submission.
        ob_start();
        $table->out($this->pagesize, false);
        $output = ob_get_contents();
        ob_end_clean();
        $PAGE->requires->js_call_amd('lifecyclestep_adminapprove/init', 'init', [$table->totalrows]);

        $mform->addElement('html', $output);

        $displaylist = [];
        $displaylist[''] = get_string('choosedots');

        $rollbackcustlabel =
            settings_manager::get_settings($this->stepid, settings_type::STEP)['rollbackbuttonlabel'] ?? null;
        $rollbackcustlabel = !empty($rollbackcustlabel) ?
            $rollbackcustlabel : get_string('rollback', 'lifecyclestep_adminapprove');

        $proceedcustlabel =
            settings_manager::get_settings($this->stepid, settings_type::STEP)['proceedbuttonlabel'] ?? null;
        $proceedcustlabel = !empty($proceedcustlabel) ?
            $proceedcustlabel : get_string('proceed', 'lifecyclestep_adminapprove');

        $params = ['action' => 'proceed', 'stepid' => $this->stepid, 'sesskey' => sesskey()];
        $url = new moodle_url($PAGE->url, $params);
        $displaylist[$url->out(false)] = $proceedcustlabel;

        $params = ['action' => 'rollback', 'stepid' => $this->stepid, 'sesskey' => sesskey()];
        $url = new moodle_url($PAGE->url, $params);
        $displaylist[$url->out(false)] = $rollbackcustlabel;

        $label = html_writer::tag('label', get_string('withselectedcourses', 'lifecyclestep_adminapprove'),
            ['for' => 'formactionid', 'class' => 'col-form-label d-inline']);

        // Create element for action on selected records.
        $selectactionparams = [
            'id' => 'formactionid',
            // Window.onbeforeunload = null: suppress warning about unchanged data
            // This.form.submit(): force submit by selecting an action
            // This.form.action=this.value: set action url to selected url.
            'onchange' => "window.onbeforeunload = null;this.form.action=this.value;this.form.submit();",
            'data-action' => 'toggle',
            'data-togglegroup' => 'lifecycle-adminapprove-table',
            'data-toggle' => 'action',
            'disabled' => true,
        ];
        $select = html_writer::select($displaylist, 'formaction', '', ['' => 'choosedots'], $selectactionparams);

        $a = html_writer::div($label . $select);
        $c = html_writer::div($a, 'btn-group');
        $d = html_writer::div($c, 'form-inline');
        $mform->addElement('html', html_writer::div($d, 'buttons'));
        // Since we have more than one form on the page (filter form and this form) a submission runs into a warning
        // because of unsaved data in the other form. In order to suppress this message we disable
        // all checks for form changes.
        $PAGE->requires->js_call_amd('core_form/changechecker', 'disableAllChecks');
    }
}
