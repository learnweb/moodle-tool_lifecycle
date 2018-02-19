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

use tool_cleanupcourses\entity\trigger_subplugin;
use tool_cleanupcourses\form\form_workflow_instance;
use tool_cleanupcourses\form\form_step_instance;
use tool_cleanupcourses\form\form_trigger_instance;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\settings_manager;
use tool_cleanupcourses\manager\trigger_manager;
use tool_cleanupcourses\entity\workflow;
use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\manager\workflow_manager;
use tool_cleanupcourses\table\workflow_definition_table;
use tool_cleanupcourses\table\active_workflows_table;
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
        $url = new \moodle_url('/admin/tool/cleanupcourses/adminsettings.php');
        parent::__construct('tool_cleanupcourses_adminsettings',
            get_string('adminsettings_heading', 'tool_cleanupcourses'),
            $url);
    }
}

/**
 * Class that handles the display and configuration the settings.
 *
 * @package   tool_cleanupcourses
 * @copyright 2015 Tobias Reischmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_settings {

    /** @var object the url of the subplugin settings page */
    private $pageurl;

    /**
     * Constructor for this subplugin settings
     */
    public function __construct() {
        global $PAGE;
        $this->pageurl = new \moodle_url('/admin/tool/cleanupcourses/adminsettings.php');
        $PAGE->set_url($this->pageurl);
    }

    /**
     * Write the HTML for the submission plugins table.
     */
    private function view_plugins_table() {
        global $OUTPUT, $PAGE;

        // Set up the table.
        $this->view_header();

        echo $OUTPUT->heading(get_string('active_workflows_heading', 'tool_cleanupcourses'));

        $table = new active_workflows_table('tool_cleanupcourses_active_workflows');
        $table->out(5000, false);

        echo $OUTPUT->heading(get_string('workflow_definition_heading', 'tool_cleanupcourses'));

        echo $OUTPUT->single_button(new \moodle_url($PAGE->url,
            array('action' => ACTION_WORKFLOW_INSTANCE_FROM, 'sesskey' => sesskey())),
            get_string('add_workflow', 'tool_cleanupcourses'));

        $table = new workflow_definition_table('tool_cleanupcourses_workflow_definitions');
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

        echo $OUTPUT->heading(get_string('adminsettings_edit_workflow_definition_heading', 'tool_cleanupcourses'));

        echo $form->render();

        $this->view_footer();
    }

    /**
     * Redirect to workflow details page.
     * @param $workflowid int id of the workflow.
     * @throws \moodle_exception
     */
    private function view_workflow_details($workflowid) {
        $url = new \moodle_url('/admin/tool/cleanupcourses/workflowsettings.php',
            array('workflowid' => $workflowid, 'sesskey' => sesskey()));
        redirect($url);
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
    public function execute($action, $workflowid) {
        global $PAGE;
        $this->check_permissions();

        // Has to be called before moodleform is created!
        admin_externalpage_setup('tool_cleanupcourses_adminsettings');

        workflow_manager::handle_action($action, $workflowid);

        $form = new form_workflow_instance($PAGE->url, workflow_manager::get_workflow($workflowid));

        if ($action === ACTION_WORKFLOW_INSTANCE_FROM) {
            $this->view_workflow_instance_form($form);
        } else {
            if ($form->is_submitted() && !$form->is_cancelled() && $data = $form->get_submitted_data()) {
                if ($data->id) {
                    $workflow = workflow_manager::get_workflow($data->id);
                    $workflow->title = $data->title;
                    $newworkflow = false;
                } else {
                    $workflow = workflow::from_record($data);
                    $newworkflow = true;
                }
                workflow_manager::insert_or_update($workflow);
                // If a new workflow was created, redirect to details page to directly create a trigger.
                if ($newworkflow) {
                    $this->view_workflow_details($workflow->id);
                    return;
                }
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
        admin_externalpage_setup('tool_cleanupcourses_adminsettings');
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

        echo $OUTPUT->heading(get_string('adminsettings_workflow_definition_steps_heading', 'tool_cleanupcourses'));

        if (!workflow_manager::is_active($this->workflowid)) {
            $steps = step_manager::get_step_types();
            echo $OUTPUT->single_select(new \moodle_url($PAGE->url,
                array('action' => ACTION_STEP_INSTANCE_FORM, 'sesskey' => sesskey(), 'workflowid' => $this->workflowid)),
                'subpluginname', $steps, '', array('' => get_string('add_new_step_instance', 'tool_cleanupcourses')));
        }
        echo $OUTPUT->single_button( new \moodle_url('/admin/tool/cleanupcourses/adminsettings.php'),
            get_string('back'));

        $table = new step_table('tool_cleanupcourses_workflows', $this->workflowid);
        $table->out(5000, false);

        $this->view_footer();
    }

    /**
     * Write the HTML for the step instance form.
     * @param $form \moodleform form to be displayed.
     */
    private function view_step_instance_form($form) {
        $workflow = workflow_manager::get_workflow($this->workflowid);
        $this->view_instance_form($form,
            get_string('adminsettings_edit_step_instance_heading', 'tool_cleanupcourses',
                $workflow->title));
    }

    /**
     * Write the HTML for the trigger instance form.
     * @param $form \moodleform form to be displayed.
     */
    private function view_trigger_instance_form($form) {
        $workflow = workflow_manager::get_workflow($this->workflowid);
        $this->view_instance_form($form,
            get_string('adminsettings_edit_trigger_instance_heading', 'tool_cleanupcourses',
                $workflow->title));
    }

    /**
     * Write the HTML for subplugin instance form with specific header.
     * @param $form \moodleform form to be displayed.
     * @param $header string header of the form.
     */
    private function view_instance_form($form, $header) {
        global $OUTPUT;

        // Set up the table.
        $this->view_header();

        echo $OUTPUT->heading($header);

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

        if ($action === ACTION_TRIGGER_INSTANCE_FORM) {
            if ($this->handle_trigger_instance_form()) {
                return;
            }
        }

        // If trigger is not yet set, redirect to trigger form!
        $trigger = trigger_manager::get_trigger_for_workflow($this->workflowid);
        if ($trigger === null) {
            $subpluginname = null;
            if ($name = optional_param('subpluginname', null, PARAM_ALPHA)) {
                $subpluginname = $name;
            }
            $triggerform = new form_trigger_instance($PAGE->url, $this->workflowid, null, $subpluginname);
            $this->view_trigger_instance_form($triggerform);
            return;
        }

        // Handle other actions.
        step_manager::handle_action($action, $subplugin);
        workflow_manager::handle_action($action, $subplugin);

        if ($action === ACTION_STEP_INSTANCE_FORM) {
            if ($this->handle_step_instance_form()) {
                return;
            }
        }
        // If no action handler has printed any form yet, display the plugins tables.
        $this->view_plugins_table();
    }

    /**
     * Handles actions for the trigger instance form and causes related forms to be rendered.
     * @return bool True, if no further action handling or output should be conducted.
     */
    private function handle_trigger_instance_form() {
        global $OUTPUT, $PAGE;
        $subpluginname = null;
        $settings = null;
        if ($trigger = trigger_manager::get_trigger_for_workflow($this->workflowid)) {
            $settings = settings_manager::get_settings($trigger->id, SETTINGS_TYPE_TRIGGER);
        }
        if ($name = optional_param('subpluginname', null, PARAM_ALPHA)) {
            $subpluginname = $name;
        }
        $form = new form_trigger_instance($PAGE->url, $this->workflowid, $trigger, $subpluginname, $settings);

        // Skip this part and continue with requiring a trigger if still null.
        if (!$form->is_cancelled()) {
            if ($form->is_submitted() && $form->is_validated() && $data = $form->get_submitted_data()) {
                // In case the workflow is active, we do not allow changes to the steps or trigger.
                if (workflow_manager::is_active($this->workflowid)) {
                    echo $OUTPUT->notification(
                        get_string('active_workflow_not_changeable', 'tool_cleanupcourses'),
                        'warning');
                } else {
                    if (!empty($data->id)) {
                        $trigger = trigger_manager::get_instance($data->id);
                        $trigger->subpluginname = $data->subpluginname;
                        $trigger->instancename = $data->instancename;
                    } else {
                        $trigger = trigger_subplugin::from_record($data);
                    }
                    trigger_manager::insert_or_update($trigger);
                    // Save local subplugin settings.
                    settings_manager::save_settings($trigger->id, SETTINGS_TYPE_TRIGGER, $data->subpluginname, $data);
                }
                return false;
            } else {
                $this->view_trigger_instance_form($form);
                return true;
            }
        }
        return false;
    }

    /**
     * Handles actions for the trigger instance form and causes related forms to be rendered.
     * @return bool True, if no further action handling or output should be conducted.
     */
    private function handle_step_instance_form() {
        global $OUTPUT, $PAGE;
        $steptomodify = null;
        $subpluginname = null;
        $stepsettings = null;
        if ($stepid = optional_param('subplugin', null, PARAM_INT)) {
            $steptomodify = step_manager::get_step_instance($stepid);
            // If step was removed!
            if (!$steptomodify) {
                return false;
            }
            $stepsettings = settings_manager::get_settings($stepid, SETTINGS_TYPE_STEP);
        } else if ($name = optional_param('subpluginname', null, PARAM_ALPHA)) {
            $subpluginname = $name;
        } else {
            return false;
        }

        $form = new form_step_instance($PAGE->url, $steptomodify, $this->workflowid, $subpluginname, $stepsettings);

        if ($form->is_cancelled()) {
            return false;
        } else if ($form->is_submitted() && $form->is_validated() && $data = $form->get_submitted_data()) {
            // In case the workflow is active, we do not allow changes to the steps or trigger.
            if (workflow_manager::is_active($this->workflowid)) {
                echo $OUTPUT->notification(
                    get_string('active_workflow_not_changeable', 'tool_cleanupcourses'),
                    'warning');
            } else {
                if (!empty($data->id)) {
                    $step = step_manager::get_step_instance($data->id);
                    $step->instancename = $data->instancename;
                } else {
                    $step = step_subplugin::from_record($data);
                }
                step_manager::insert_or_update($step);
                // Save local subplugin settings.
                settings_manager::save_settings($step->id, SETTINGS_TYPE_STEP, $form->subpluginname, $data);
            }
        } else {
            $this->view_step_instance_form($form);
            return true;
        }
        return false;
    }


}