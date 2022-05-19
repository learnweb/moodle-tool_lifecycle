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
 * Admin lib providing multiple classes for admin settings.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\form\form_step_instance;
use tool_lifecycle\local\form\form_trigger_instance;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\table\step_table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');

/**
 * Class that handles the display and configuration of a workflow.
 *
 * @package   tool_lifecycle
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
     * @param int $workflowid Id of the workflow.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function __construct($workflowid) {
        global $PAGE;
        // Has to be called before moodleform is created!
        admin_externalpage_setup('tool_lifecycle_active_workflows');
        $this->pageurl = new \moodle_url('/admin/tool/lifecycle/workflowsettings.php');
        $PAGE->set_title(get_string('adminsettings_workflow_definition_steps_heading', 'tool_lifecycle'));
        $PAGE->set_url($this->pageurl);
        $this->workflowid = $workflowid;
    }

    /**
     * Write the HTML for the submission plugins table.
     * @throws \moodle_exception
     */
    private function view_plugins_table() {
        global $OUTPUT, $PAGE;

        // Set up the table.
        $this->view_header();

        echo $OUTPUT->heading(get_string('adminsettings_workflow_definition_steps_heading', 'tool_lifecycle'));

        if (workflow_manager::is_editable($this->workflowid)) {
            $triggers = trigger_manager::get_chooseable_trigger_types();
            echo $OUTPUT->single_select(new \moodle_url($PAGE->url,
                array('action' => action::TRIGGER_INSTANCE_FORM, 'sesskey' => sesskey(), 'workflowid' => $this->workflowid)),
                'triggername', $triggers, '', array('' => get_string('add_new_trigger_instance', 'tool_lifecycle')));
        }

        if (workflow_manager::is_editable($this->workflowid)) {
            $steps = step_manager::get_step_types();
            echo '<span class="ml-1"></span>';
            echo $OUTPUT->single_select(new \moodle_url($PAGE->url,
                array('action' => action::STEP_INSTANCE_FORM, 'sesskey' => sesskey(),
                    'workflowid' => $this->workflowid, 'class' => 'ml-1')),
                'stepname', $steps, '', array('' => get_string('add_new_step_instance', 'tool_lifecycle')));
        }

        $url = new \moodle_url('/admin/tool/lifecycle/adminsettings.php');
        echo \html_writer::start_tag('div', array('class' => 'd-inline-block'));
        echo \html_writer::start_tag('form', array('action' => $url, 'method' => 'post', 'class' => 'form-inline'));
        echo \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        echo \html_writer::tag('button', get_string('back'), array('class' => 'btn btn-secondary ml-1'));
        echo \html_writer::end_tag('form');
        echo \html_writer::end_tag('div');

        $table = new step_table('tool_lifecycle_workflows', $this->workflowid);
        $table->out(50, false);

        $this->view_footer();
    }

    /**
     * Write the HTML for the step instance form.
     *
     * @param \moodleform $form Form to be displayed.
     * @throws \coding_exception
     */
    private function view_step_instance_form($form) {
        $workflow = workflow_manager::get_workflow($this->workflowid);
        $this->view_instance_form($form,
            get_string('adminsettings_edit_step_instance_heading', 'tool_lifecycle',
                $workflow->title));
    }

    /**
     * Write the HTML for the trigger instance form.
     *
     * @param \moodleform $form Form to be displayed.
     * @throws \coding_exception
     */
    private function view_trigger_instance_form($form) {
        $workflow = workflow_manager::get_workflow($this->workflowid);
        $this->view_instance_form($form,
            get_string('adminsettings_edit_trigger_instance_heading', 'tool_lifecycle',
                $workflow->title));
    }

    /**
     * Write the HTML for subplugin instance form with specific header.
     *
     * @param \moodleform $form Form to be displayed.
     * @param string $header Header of the form.
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
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \require_login_exception
     * @throws \required_capability_exception
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = \context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * This is the entry point for this controller class.
     * @param string $action Action string to be executed.
     * @param int $subpluginid Id of the subplugin associated.
     * @param int $workflowid Id of the workflow associated.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function execute($action, $subpluginid, $workflowid) {
        $this->check_permissions();

        // Handle other actions.
        step_manager::handle_action($action, $subpluginid, $workflowid);
        trigger_manager::handle_action($action, $subpluginid, $workflowid);
        workflow_manager::handle_action($action, $workflowid);

        if ($action === action::TRIGGER_INSTANCE_FORM) {
            if ($this->handle_trigger_instance_form()) {
                return;
            }
        }

        if ($action === action::STEP_INSTANCE_FORM) {
            if ($this->handle_step_instance_form()) {
                return;
            }
        }
        // If no action handler has printed any form yet, display the plugins tables.
        $this->view_plugins_table();
    }

    /**
     * Handles actions for the trigger instance form and causes related forms to be rendered.
     *
     * @return bool True, if no further action handling or output should be conducted.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function handle_trigger_instance_form() {
        global $PAGE;
        $subpluginname = null;
        $triggertomodify = null;
        $triggersettings = null;

        if (!$this->retrieve_trigger_parameters($triggertomodify, $subpluginname, $triggersettings)) {
            return false;
        }

        $form = new form_trigger_instance($PAGE->url, $this->workflowid, $triggertomodify, $subpluginname, $triggersettings);

        // Skip this part and continue with requiring a trigger if still null.
        if (!$form->is_cancelled()) {
            if ($form->is_submitted() && $form->is_validated() && $data = $form->get_submitted_data()) {
                // In case the workflow was active, we do not allow changes to the steps or trigger.
                if (!workflow_manager::is_editable($this->workflowid)) {
                    \core\notification::add(
                        get_string('active_workflow_not_changeable', 'tool_lifecycle'),
                        \core\notification::WARNING);
                } else {
                    if (!empty($data->id)) {
                        $triggertomodify = trigger_manager::get_instance($data->id);
                        $triggertomodify->subpluginname = $data->subpluginname;
                        $triggertomodify->instancename = $data->instancename;
                    } else {
                        $triggertomodify = trigger_subplugin::from_record($data);
                    }
                    trigger_manager::insert_or_update($triggertomodify);
                    // Save local subplugin settings.
                    settings_manager::save_settings($triggertomodify->id, settings_type::TRIGGER, $data->subpluginname, $data);
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
     * Retrieves the relevant parameters for the trigger instance form from the sent params.
     * Thereby it store the data in the given parameters.
     * @param int $triggertomodify Id of the trigger instance to be modified.
     * @param string $subpluginname Name of the subplugin, the trigger instance belongs to.
     * @param array $triggersettings Settings of the trigger instance.
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function retrieve_trigger_parameters(&$triggertomodify, &$subpluginname, &$triggersettings) {
        if ($triggerid = optional_param('subplugin', null, PARAM_INT)) {
            $triggertomodify = trigger_manager::get_instance($triggerid);
            // If step was removed!
            if (!$triggertomodify) {
                return false;
            }
            $triggersettings = settings_manager::get_settings($triggerid, settings_type::TRIGGER);
        } else if ($name = optional_param('subpluginname', null, PARAM_ALPHA)) {
            $subpluginname = $name;
        } else if ($name = optional_param('triggername', null, PARAM_ALPHA)) {
            $subpluginname = $name;
        } else {
            return false;
        }
        return true;
    }

    /**
     * Handles actions for the trigger instance form and causes related forms to be rendered.
     *
     * @return bool True, if no further action handling or output should be conducted.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function handle_step_instance_form() {
        global $PAGE;
        $steptomodify = null;
        $subpluginname = null;
        $stepsettings = null;

        if (!$this->retrieve_step_parameters($steptomodify, $subpluginname, $stepsettings)) {
            return false;
        }

        $form = new form_step_instance($PAGE->url, $steptomodify, $this->workflowid, $subpluginname, $stepsettings);

        if ($form->is_cancelled()) {
            return false;
        } else if ($form->is_submitted() && $form->is_validated() && $data = $form->get_submitted_data()) {
            // In case the workflow was active, we do not allow changes to the steps or trigger.
            if (!workflow_manager::is_editable($this->workflowid)) {
                \core\notification::add(
                    get_string('active_workflow_not_changeable', 'tool_lifecycle'),
                    \core\notification::WARNING);
            }
            if (!empty($data->id)) {
                $step = step_manager::get_step_instance($data->id);
                if (isset($data->instancename)) {
                    $step->instancename = $data->instancename;
                }
            } else {
                $step = step_subplugin::from_record($data);
            }
            step_manager::insert_or_update($step);
            // Save local subplugin settings.
            settings_manager::save_settings($step->id, settings_type::STEP, $form->subpluginname, $data, true);
        } else {
            $this->view_step_instance_form($form);
            return true;
        }
        return false;
    }

    /**
     * Retrieves the relevant parameters for the step instance form from the sent params.
     * Thereby it store the data in the given parameters.
     * @param int $steptomodify Id of the step instance to be modified.
     * @param string $subpluginname Name of the subplugin, the step instance belongs to.
     * @param array $stepsettings Settings of the step instance.
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function retrieve_step_parameters(&$steptomodify, &$subpluginname, &$stepsettings) {
        if ($stepid = optional_param('subplugin', null, PARAM_INT)) {
            $steptomodify = step_manager::get_step_instance($stepid);
            // If step was removed!
            if (!$steptomodify) {
                return false;
            }
            $stepsettings = settings_manager::get_settings($stepid, settings_type::STEP);
        } else if ($name = optional_param('subpluginname', null, PARAM_ALPHA)) {
            $subpluginname = $name;
        } else if ($name = optional_param('stepname', null, PARAM_ALPHA)) {
            $subpluginname = $name;
        } else {
            return false;
        }
        return true;
    }


}
