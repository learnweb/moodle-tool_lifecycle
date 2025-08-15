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
 * Offers the possibility to add or modify a workflow instance.
 *
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\form;

use tool_lifecycle\local\entity\workflow;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Provides a form to modify a workflow instance
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_workflow_instance extends \moodleform {

    /**
     * @var workflow
     */
    public $workflow;

    /**
     * Constructor
     * @param \moodle_url $url Url of the page.
     * @param workflow $workflow workflow entity.
     */
    public function __construct($url, $workflow) {
        $this->workflow = $workflow;

        parent::__construct($url);
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        $elementname = 'id';
        $mform->addElement('hidden', $elementname); // Save the record's id.
        $mform->setType($elementname, PARAM_TEXT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->id);
        }

        $mform->addElement('header', 'general_settings_header', get_string('workflowsettings', 'tool_lifecycle'));

        $elementname = 'title';
        $mform->addElement('text', $elementname, get_string('workflow_title', 'tool_lifecycle'));
        $mform->addHelpButton($elementname, 'workflow_title', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_TEXT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->title);
        }

        $elementname = 'displaytitle';
        $mform->addElement('text', $elementname, get_string('workflow_displaytitle', 'tool_lifecycle'));
        $mform->addHelpButton($elementname, 'workflow_displaytitle', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_TEXT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->displaytitle);
        }

        $elementname = 'rollbackdelay';
        $mform->addElement('duration', $elementname, get_string('workflow_rollbackdelay', 'tool_lifecycle'));
        $mform->addHelpButton($elementname, 'workflow_rollbackdelay', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_INT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->rollbackdelay);
        } else {
            $mform->setDefault($elementname, get_config('tool_lifecycle', 'duration'));
        }

        $elementname = 'finishdelay';
        $mform->addElement('duration', $elementname, get_string('workflow_finishdelay', 'tool_lifecycle'));
        $mform->addHelpButton($elementname, 'workflow_finishdelay', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_INT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->finishdelay);
        } else {
            $mform->setDefault($elementname, get_config('tool_lifecycle', 'duration'));
        }

        $elementname = 'delayforallworkflows';
        $mform->addElement('checkbox', $elementname, get_string('workflow_delayforallworkflows', 'tool_lifecycle'));
        $mform->addHelpButton($elementname, 'workflow_delayforallworkflows', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_BOOL);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->delayforallworkflows);
        }

        $elementname = 'includedelayedcourses';
        $mform->addElement('advcheckbox', $elementname, get_string($elementname, 'tool_lifecycle'),
            null, null, [0, 1]);
        $mform->addHelpButton($elementname, $elementname, 'tool_lifecycle');
        $mform->setType($elementname, PARAM_INT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->includedelayedcourses);
        }

        $elementname = 'includesitecourse';
        $mform->addElement('advcheckbox', $elementname, get_string($elementname, 'tool_lifecycle'),
            null, null, [0, 1]);
        $mform->addHelpButton($elementname, $elementname, 'tool_lifecycle');
        $mform->setType($elementname, PARAM_INT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->includesitecourse);
        }

        $elementname = 'andor';
        $groupelements = array(
            $mform->createElement('radio', $elementname, '', 'AND', '0'),
            $mform->createElement('radio', $elementname, '', 'OR', '1')
        );
        $mform->addElement('group', 'andorgroup', get_string('andor', 'tool_lifecycle'), $groupelements, null, true);
        $mform->addHelpButton('andorgroup', 'andor', 'tool_lifecycle');
        $mform->setType($elementname, PARAM_INT);
        if (isset($this->workflow)) {
            $mform->setDefault("andorgroup[$elementname]", $this->workflow->andor);
        } else {
            $mform->setDefault("andorgroup[$elementname]", '0');
        }

        $this->add_action_buttons();
    }

}
