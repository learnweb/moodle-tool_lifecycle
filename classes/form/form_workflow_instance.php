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
 * @package    tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\form;

use tool_cleanupcourses\entity\workflow;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\lib_manager;
use tool_cleanupcourses\step\libbase;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Provides a form to modify a workflow instance
 */
class form_workflow_instance extends \moodleform {


    /**
     * @var workflow
     */
    public $workflow;

    /**
     * Constructor
     * @param \moodle_url $url.
     * @param workflow $workflow workflow entity.
     * @throws \moodle_exception if neither step nor subpluginname are set.
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

        $mform->addElement('header', 'general_settings_header', get_string('general_settings_header', 'tool_cleanupcourses'));

        $elementname = 'title';
        $mform->addElement('text', $elementname, get_string('workflow_title', 'tool_cleanupcourses'));
        $mform->setType($elementname, PARAM_TEXT);
        if (isset($this->workflow)) {
            $mform->setDefault($elementname, $this->workflow->title);
        }

        $this->add_action_buttons();
    }

}
