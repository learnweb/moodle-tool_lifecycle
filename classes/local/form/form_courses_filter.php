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
 * A moodle form for filtering the course backups table
 *
 * @package    tool_lifecycle
 * @copyright  2021 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\form;

use DateTime;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * A moodle form for filtering the course backups table
 *
 * @package    tool_lifecycle
 * @copyright  2021 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_courses_filter extends \moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;

        $bulkedit = $this->_customdata['bulkedit'];

        $mform->addElement('text', 'courseid', get_string('courseid', 'tool_lifecycle'));
        $mform->setType('courseid', PARAM_ALPHANUM);
        $mform->addRule('courseid', null, 'numeric', null, 'client');

        $mform->addElement('text', 'shortname', get_string('shortname'));
        $mform->setType('shortname', PARAM_TEXT);

        $mform->addElement('text', 'fullname', get_string('fullname'));
        $mform->setType('fullname', PARAM_TEXT);

        $mform->addElement('date_time_selector', 'deletedate', get_string('deletedate', 'tool_lifecycle'));
        $mform->addHelpButton('deletedate', 'deletedate', 'tool_lifecycle');
        $date = (new DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('+1 day');
        $mform->setDefault('startdate', $date->getTimestamp());

        $mform->addElement('hidden', 'bulkedit', $bulkedit);
        $mform->setType('bulkedit', PARAM_INT);

        // Edited from $this->add_action_buttons to allow custom cancel text.
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton',
            get_string('apply', 'tool_lifecycle'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('reset'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

}
