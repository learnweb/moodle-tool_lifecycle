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
 * Offers the possibility to add or modify a step instance.
 *
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\form;

use tool_lifecycle\action;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\step\libbase;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Provides a form to modify a step instance
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_step_instance extends \moodleform {

    /**
     * @var step_subplugin
     */
    public $step;

    /**
     * @var string name of the subplugin to be created
     */
    public $subpluginname;

    /**
     * @var libbase name of the subplugin to be created
     */
    public $lib;

    /**
     * @var array/null local settings of the step instance
     */
    public $stepsettings;

    /**
     * @var int id of the workflow
     */
    private $workflowid;

    /**
     * Constructor
     *
     * @param \moodle_url $url .
     * @param int $workflowid id of the step's workflow
     * @param step_subplugin $step step entity.
     * @param string $subpluginname name of the subplugin.
     * @param array $stepsettings settings of the step.
     * @throws \moodle_exception if neither step nor subpluginname are set.
     */
    public function __construct($url, $workflowid, $step, $subpluginname = null, $stepsettings = null) {
        $this->step = $step;
        $this->workflowid = $workflowid;
        if ($step) {
            $this->subpluginname = $step->subpluginname;
        } else if ($subpluginname) {
            $this->subpluginname = $subpluginname;
        } else {
            throw new \moodle_exception('One of the parameters $step or $subpluginname have to be set!');
        }
        $this->lib = lib_manager::get_step_lib($this->subpluginname);
        $this->stepsettings = $stepsettings;

        parent::__construct($url, null, 'post', '', null);
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id'); // Save the record's id.
        $mform->setType('id', PARAM_TEXT);

        $mform->addElement('hidden', 'workflowid'); // Save the record's id.
        $mform->setType('workflowid', PARAM_INT);

        $mform->addElement('hidden', 'action'); // Save the current action.
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', action::STEP_INSTANCE_FORM);

        $mform->addElement('header', 'general_settings_header', get_string('general_settings_header', 'tool_lifecycle'));

        $elementname = 'instancename';
        $mform->addElement('text', $elementname, get_string('step_instancename', 'tool_lifecycle'));
        $mform->addHelpButton($elementname, 'step_instancename', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'subpluginnamestatic';
        $mform->addElement('static', $elementname, get_string('step_subpluginname', 'tool_lifecycle'));
        $mform->addHelpButton($elementname, 'step_subpluginname', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_TEXT);
        $elementname = 'subpluginname';
        $mform->addElement('hidden', $elementname);
        $mform->setType($elementname, PARAM_TEXT);

        // Insert the subplugin specific settings.
        if (!empty($this->lib->instance_settings())) {
            $mform->addElement('header', 'step_settings_header', get_string('step_settings_header', 'tool_lifecycle'));
            $this->lib->extend_add_instance_form_definition($mform);
        }

        $this->add_action_buttons();
    }

    /**
     * In case of read only mode only the cancel button is rendered.
     */
    private function add_cancel_button() {
        $mform =& $this->_form;

        // Add a group 'buttonar' to allow excluding it from freezing.
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Defines forms elements
     */
    public function definition_after_data() {
        $mform = $this->_form;

        $mform->setDefault('workflowid', $this->workflowid);

        if ($this->step) {
            $mform->setDefault('id', $this->step->id);
            $mform->setDefault('instancename', $this->step->instancename);
            $subpluginname = $this->step->subpluginname;
        } else {
            $mform->setDefault('id', '');
            $subpluginname = $this->subpluginname;
        }
        $mform->setDefault('subpluginnamestatic',
            get_string('pluginname', 'lifecyclestep_' . $subpluginname));
        $mform->setDefault('subpluginname', $subpluginname);

        // Setting the default values for the local step settings.
        if ($this->stepsettings) {
            foreach ($this->stepsettings as $key => $value) {
                $mform->setDefault($key, $value);
            }
        }

        // Insert the subplugin specific settings.
        $this->lib->extend_add_instance_form_definition_after_data($mform, $this->stepsettings);

        // For active workflows, we do not want the form to be editable.
        if ($this->workflowid && !workflow_manager::is_editable($this->workflowid)) {
            // The group buttonar is the array of submit buttons. For inactive workflows this is only a cancel button.
            $notfreeze = ['buttonar'];
            foreach ($this->lib->instance_settings() as $setting) {
                if ($setting->editable) {
                    $notfreeze[] = $setting->name;
                }
            }
            $mform->hardFreezeAllVisibleExcept($notfreeze);
        }
    }

}
