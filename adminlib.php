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

namespace tool_cleanupcourses;

use tool_cleanupcourses\form\form_workflow_instance;
use tool_cleanupcourses\form\form_step_instance;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\settings_manager;
use tool_cleanupcourses\manager\trigger_manager;
use tool_cleanupcourses\entity\workflow;
use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\manager\workflow_manager;
use tool_cleanupcourses\table\workflow_table;
use tool_cleanupcourses\table\trigger_table;
use tool_cleanupcourses\table\step_table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');

/**
 * External Page for showing active cleanup processes
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class admin_page_active_processes extends \admin_externalpage {

    /**
     * The constructor - calls parent constructor
     *
     */
    public function __construct() {
        $url = new \moodle_url('/admin/tool/cleanupcourses/activeprocesses.php');
        parent::__construct('tool_cleanupcourses_activeprocesses',
            get_string('active_processes_list_header', 'tool_cleanupcourses'),
            $url);
    }
}

/**
 * External Page for showing course backups
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class admin_page_course_backups extends \admin_externalpage {

    /**
     * The constructor - calls parent constructor
     *
     */
    public function __construct() {
        $url = new \moodle_url('/admin/tool/cleanupcourses/coursebackups.php');
        parent::__construct('tool_cleanupcourses_coursebackups',
            get_string('course_backups_list_header', 'tool_cleanupcourses'),
            $url);
    }
}

/**
 * External Page for defining settings for subplugins
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class admin_page_sublugins extends \admin_externalpage {

    /**
     * The constructor - calls parent constructor
     *
     */
    public function __construct() {
        $url = new \moodle_url('/admin/tool/cleanupcourses/subpluginssettings.php');
        parent::__construct('tool_cleanupcourses_subpluginssettings',
            get_string('subpluginssettings_heading', 'tool_cleanupcourses'),
            $url);
    }
}

/**
 * Class that handles the display and configuration of the subplugin settings.
 *
 * @package   tool_cleanupcourses
 * @copyright 2015 Tobias Reischmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subplugin_settings {

    /** @var object the url of the subplugin settings page */
    private $pageurl;

    /**
     * Constructor for this subplugin settings
     */
    public function __construct() {
        global $PAGE;
        $this->pageurl = new \moodle_url('/admin/tool/cleanupcourses/subpluginssettings.php');
        $PAGE->set_url($this->pageurl);
    }

    /**
     * Write the HTML for the submission plugins table.
     */
    private function view_plugins_table() {
        global $OUTPUT, $PAGE;

        // Set up the table.
        $this->view_header();

        echo $OUTPUT->heading(get_string('subpluginssettings_trigger_heading', 'tool_cleanupcourses'));

        $table = new trigger_table('tool_cleanupcourses_triggers');
        $table->out(5000, false);

        echo $OUTPUT->heading(get_string('subpluginssettings_step_heading', 'tool_cleanupcourses'));

        echo $OUTPUT->single_button(new \moodle_url($PAGE->url,
            array('action' => ACTION_WORKFLOW_INSTANCE_FROM, 'sesskey' => sesskey())),
            get_string('add_workflow', 'tool_cleanupcourses'));

        $table = new workflow_table('tool_cleanupcourses_workflows');
        $table->out(5000, false);

        $this->view_footer();
    }

    /**
     * Write the HTML for the add workflow form.
     * @param form_workflow_instance $form
     */
    private function view_workflow_instance_form($form) {
        global $OUTPUT;

        // Set up the table.
        $this->view_header();

        echo $OUTPUT->heading(get_string('subpluginssettings_edit_workflow_instance_heading', 'tool_cleanupcourses'));

        echo $form->render();

        $this->view_footer();
    }

    /**
     * Write the page header
     */
    private function view_header() {
        global $OUTPUT;
        // Print the page heading.
        echo $OUTPUT->header();
    }

    /**
     * Write the page footer
     */
    private function view_footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the subplugin settings
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = \context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * This is the entry point for this controller class.
     */
    public function execute($action, $subpluginid, $workflowid) {
        global $PAGE;
        $this->check_permissions();

        // Has to be called before moodleform is created!
        admin_externalpage_setup('tool_cleanupcourses_subpluginssettings');

        trigger_manager::handle_action($action, $subpluginid);
        workflow_manager::handle_action($action, $workflowid);

        $form = new form_workflow_instance($PAGE->url, workflow_manager::get_workflow($workflowid));

        if ($action === ACTION_WORKFLOW_INSTANCE_FROM) {
            $this->view_workflow_instance_form($form);
        } else {
            if ($form->is_submitted() && !$form->is_cancelled() && $data = $form->get_submitted_data()) {
                if ($data->id) {
                    $workflow = workflow_manager::get_workflow($data->id);
                    $workflow->title = $data->title;
                } else {
                    $workflow = workflow::from_record($data);
                }
                workflow_manager::insert_or_update($workflow);
            }
            $this->view_plugins_table();
        }
    }

}

/**
 * Class that handles the display and configuration of a workflow.
 *
 * @package   tool_cleanupcourses
 * @copyright 2015 Tobias Reischmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class workflow_settings {

    /** @var object the url of the subplugin settings page */
    private $pageurl;

    /** @var int id of the workflow the settings should be displayed for (null for new workflow).
     */
    private $workflowid;

    /**
     * Constructor for this subplugin settings
     */
    public function __construct($workflowid) {
        global $PAGE;
        // Has to be called before moodleform is created!
        admin_externalpage_setup('tool_cleanupcourses_subpluginssettings');
        $this->pageurl = new \moodle_url('/admin/tool/cleanupcourses/workflowsettings.php');
        $PAGE->set_url($this->pageurl);
        $this->workflowid = $workflowid;
    }

    /**
     * Write the HTML for the submission plugins table.
     */
    private function view_plugins_table() {
        global $OUTPUT, $PAGE;

        // Set up the table.
        $this->view_header();

        echo $OUTPUT->heading(get_string('subpluginssettings_step_heading', 'tool_cleanupcourses'));

        $steps = step_manager::get_step_types();
        echo $OUTPUT->single_select(new \moodle_url($PAGE->url,
            array('action' => ACTION_STEP_INSTANCE_FORM, 'sesskey' => sesskey(), 'workflowid' => $this->workflowid)),
            'subpluginname', $steps, '', array('' => get_string('add_new_step_instance', 'tool_cleanupcourses')));
        echo $OUTPUT->single_button( new \moodle_url('/admin/tool/cleanupcourses/subpluginssettings.php'),
            get_string('back'));

        $table = new step_table('tool_cleanupcourses_workflows', $this->workflowid);
        $table->out(5000, false);

        $this->view_footer();
    }

    /**
     * Write the HTML for the step instance form.
     */
    private function view_step_instance_form($form) {
        global $OUTPUT;

        // Set up the table.
        $this->view_header();

        echo $OUTPUT->heading(get_string('subpluginssettings_edit_instance_heading', 'tool_cleanupcourses'));

        echo $form->render();

        $this->view_footer();
    }

    /**
     * Write the page header
     */
    private function view_header() {
        global $OUTPUT;
        // Print the page heading.
        echo $OUTPUT->header();
    }

    /**
     * Write the page footer
     */
    private function view_footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the subplugin settings
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = \context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * This is the entry point for this controller class.
     */
    public function execute($action, $subplugin) {
        global $PAGE;
        $this->check_permissions();

        step_manager::handle_action($action, $subplugin);

        $steptomodify = null;
        $subpluginname = null;
        $stepsettings = null;
        if ($stepid = optional_param('subplugin', null, PARAM_INT)) {
            $steptomodify = step_manager::get_step_instance($stepid);
            // If step was removed!
            if (!$steptomodify) {
                $this->view_plugins_table();
                return;
            }
            $stepsettings = settings_manager::get_settings($stepid);
        } else if ($name = optional_param('subpluginname', null, PARAM_ALPHA)) {
            $subpluginname = $name;
        } else {
            $this->view_plugins_table();
            return;
        }

        $form = new form_step_instance($PAGE->url, $steptomodify, $this->workflowid, $subpluginname, $stepsettings);

        if ($action === ACTION_STEP_INSTANCE_FORM) {
            $this->view_step_instance_form($form);
        } else {
            if ($form->is_submitted() && !$form->is_cancelled() && $data = $form->get_submitted_data()) {
                $step = step_manager::get_step_instance($data->id);
                if ($step) {
                    $step->instancename = $data->instancename;
                } else {
                    $step = step_subplugin::from_record($data);
                }
                step_manager::insert_or_update($step);
                // Save local subplugin settings.
                settings_manager::save_settings($step->id, $form->subpluginname, $data);
            }
            $this->view_plugins_table();
        }
    }

}