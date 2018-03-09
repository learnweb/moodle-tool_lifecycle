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
 * Offers the possibility to enter a new coursename
 *
 * @package    tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace cleanupcoursesstep_duplicate;

use tool_cleanupcourses\manager\process_manager;
use tool_cleanupcourses\step\interactionduplicate;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Provides a form to modify a step instance
 */
class form_duplicate extends \moodleform {

    private $processid;
    private $stepid;

    /**
     * Constructor
     * @param \moodle_url $url.
     * @param int $processid if of the process
     */
    public function __construct($url, $processid, $stepid) {
        $this->processid = $processid;
        $this->stepid = $stepid;

        parent::__construct($url);
    }

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id'); // Save the record's id.
        $mform->setType('id', PARAM_TEXT);

        $mform->addElement('hidden', 'processid'); // Save the record's id.
        $mform->setType('processid', PARAM_INT);

        $mform->addElement('hidden', 'stepid'); // Save the record's id.
        $mform->setType('stepid', PARAM_INT);

        $mform->addElement('hidden', 'action'); // Save the current action.
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', 'duplicateform');

        $mform->addElement('header', 'duplicate_course_header',
            get_string('duplicate_course_header', 'cleanupcoursesstep_duplicate'));

        $elementname = 'shortname';
        $mform->addElement('text', $elementname, get_string('shortnamecourse'));
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'fullname';
        $mform->addElement('text', $elementname, get_string('fullnamecourse'));
        $mform->setType($elementname, PARAM_TEXT);

        $this->add_action_buttons();
    }

    /**
     * Defines forms elements
     */
    public function definition_after_data() {
        $mform = $this->_form;

        $mform->setDefault('processid', $this->processid);
        $mform->setDefault('stepid', $this->stepid);

        $process = process_manager::get_process_by_id($this->processid);
        $course = get_course($process->courseid);

        $mform->setDefault('fullname', $course->fullname);
        $mform->setDefault('shortname', $course->shortname);

    }

}
