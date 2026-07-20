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
 * Form class to offer admins to decide how to proceed.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_opencast;

use core\output\html_writer;
use tool_lifecycle\step\interactionopencast;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form class to offer admins to decide how to proceed.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interaction_form extends \moodleform {
    /** @var int $courseid Course Id. */
    private $courseid;
    /** @var int $processid Id of the process. */
    private $processid;
    /** @var int $stepid Id of the step instance. */
    private $stepid;
    /** @var string $stateinfo The information for show to the admin to decide. */
    private $stateinfo;

    /**
     * Constructor
     * @param \moodle_url $url Url of the current page.
     * @param int $courseid Id of the process.
     * @param int $processid Id of the process.
     * @param int $stepid Id of the step instance.
     * @param string $stateinfo State Info.
     */
    public function __construct($url, $courseid, $processid, $stepid, $stateinfo) {
        $this->courseid = $courseid;
        $this->processid = $processid;
        $this->stepid = $stepid;
        $this->stateinfo = $stateinfo;

        parent::__construct($url);
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'processid');
        $mform->setType('processid', PARAM_INT);

        $mform->addElement('hidden', 'stepid');
        $mform->setType('stepid', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement(
            'header',
            'interaction_form_header',
            get_string('interaction_form_header', 'lifecyclestep_opencast')
        );

        $stateinfo = html_writer::div(
            html_writer::span($this->stateinfo),
            'p-4'
        );

        $mform->addElement('html', $stateinfo);

        $decisionoptions = [
            process_status_helper::DECISION_CONFIRM => get_string(
                'interaction_form_option_' . process_status_helper::DECISION_CONFIRM,
                'lifecyclestep_opencast'
            ),
            process_status_helper::DECISION_ABORT => get_string(
                'interaction_form_option_' . process_status_helper::DECISION_ABORT,
                'lifecyclestep_opencast'
            ),
        ];

        $selectelmid = 'decision';
        $mform->addElement(
            'select',
            $selectelmid,
            get_string('interaction_form_select_decision', 'lifecyclestep_opencast'),
            $decisionoptions
        );
        $mform->addHelpButton($selectelmid, 'interaction_form_select_decision', 'lifecyclestep_opencast');

        $this->add_action_buttons();
    }

    /**
     * Defines forms elements
     */
    public function definition_after_data() {
        $mform = $this->_form;

        $mform->setDefault('courseid', $this->courseid);
        $mform->setDefault('processid', $this->processid);
        $mform->setDefault('stepid', $this->stepid);
        $mform->setDefault('action', interactionopencast::ACTION_RESULT);
        $mform->setDefault('decision', process_status_helper::DECISION_CONFIRM);
    }
}
