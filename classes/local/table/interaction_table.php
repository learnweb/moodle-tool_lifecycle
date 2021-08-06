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
 * Table listing all courses for a specific user and a specific subplugin
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing all courses for a specific user and a specific subplugin
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class interaction_table extends \table_sql {

    /**
     * Constructor for interaction_table.
     * @param int $uniqueid Unique id of this table.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->set_attribute('class', $this->attributes['class'] . ' ' . $uniqueid);
    }

    /**
     * Initialises the columns of the table. Necessary since attention_table has extra column date.
     */
    abstract public function init();

    /**
     * Render coursefullname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_coursefullname($row) {
        $courselink = \html_writer::link(course_get_url($row->courseid), format_string($row->coursefullname));
        return $courselink . '<br><span class="secondary-info">' . $row->courseshortname . '</span>';
    }

    /**
     * Render startdate column.
     * @param object $row Row data.
     * @return string human readable date
     * @throws \coding_exception
     */
    public function col_startdate($row) {
        if ($row->startdate) {
            $dateformat = get_string('strftimedate', 'langconfig');
            return userdate($row->startdate, $dateformat);
        } else {
            return "-";
        }
    }

    /**
     * Render tools column.
     * @param object $row Row data.
     * @return string Rendered tools html
     */
    abstract public function col_tools($row);

    /**
     * Render status column.
     * @param object $row Row data.
     * @return string Rendered status html.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public function col_status($row) {
        if ($row->processid !== null) {
            $process = process_manager::get_process_by_id($row->processid);
            $workflow = workflow_manager::get_workflow($process->workflowid);
            return interaction_manager::get_process_status_message($row->processid) .
                '<br><span class="workflow_displaytitle">' . $workflow->displaytitle . '</span>';
        }

        return '';
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->box(get_string('nocoursestodisplay', 'tool_lifecycle'));
    }
}
