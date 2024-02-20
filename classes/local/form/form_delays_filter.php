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
 * A moodle form for filtering the coursedelays table
 *
 * @package    tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\form;

use tool_lifecycle\local\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * A moodle form for filtering the coursedelays table
 *
 * @package    tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_delays_filter extends \moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        $activeworkflows = workflow_manager::get_active_workflows();
        $workflowoptions = [
                '' => get_string('all_delays', 'tool_lifecycle'),
                'global' => get_string('globally', 'tool_lifecycle'),
        ];
        foreach ($activeworkflows as $activeworkflow) {
            // Only show non-static workflows.
            if (workflow_manager::is_disableable($activeworkflow->id)) {
                $workflowoptions[$activeworkflow->id] = get_string('delays_for_workflow', 'tool_lifecycle',
                    $activeworkflow->displaytitle);
            }
        }
        $mform->addElement('select', 'workflow', get_string('show_delays', 'tool_lifecycle'), $workflowoptions);

        // Use core_course_category for moodle 3.6 and higher.
        if ($CFG->version >= 2018120300) {
            $categories = \core_course_category::get_all();
        } else {
            require_once($CFG->libdir . '/coursecatlib.php');
            $categories = \coursecat::get_all();
        }

        $categoryoptions = ['' => '-'];
        foreach ($categories as $category) {
            $categoryoptions[$category->id] = $category->name;
        }
        $mform->addElement('select', 'category', get_string('category'), $categoryoptions);

        $mform->addElement('text', 'coursename', get_string('course'));
        $mform->setType('coursename', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('apply', 'tool_lifecycle'));
    }

}
