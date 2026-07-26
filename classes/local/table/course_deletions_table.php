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
 * Table listing course backups
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use core\exception\moodle_exception;
use core\output\single_button;
use core_date;
use tool_lifecycle\urls;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing course deletions by tool lifecycle
 *
 * @package tool_lifecycle
 * @copyright  2026 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_deletions_table extends \table_sql {

    /**
     * Constructor for course_deletions_table.
     * @param int $uniqueid Unique id of this table.
     * @param \stdClass|null $filterdata
     * @throws \coding_exception
     */
    public function __construct($uniqueid, $filterdata) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_attribute('class', $this->attributes['class'] . ' ' . $uniqueid);

        $where = ['TRUE'];
        $params = [];

        if ($filterdata) {

            if ($filterdata->courseid) {
                $where[] = 'd.courseid = :courseid';
                $params['courseid'] = $filterdata->courseid;
            }
        }

        $this->set_sql('d.courseid, s.instancename as step, s.workflowid, d.modules,
        d.participants, d.timedeleted',
            '{lifecyclestep_deletecourse} d LEFT JOIN
                   {course} c ON c.id = d.courseid LEFT JOIN
                   {tool_lifecycle_step} s ON s.id = d.stepid',
            join(" AND ", $where), $params);
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialize the table.
     */
    public function init() {
        $this->define_columns(['courseid',  'step', 'timedeleted', 'modules', 'participants']);
        $this->define_headers([
            get_string('courseid', 'tool_lifecycle'),
            get_string('step', 'tool_lifecycle'),
            get_string('timedeleted', 'tool_lifecycle'),
            get_string('modules', 'tool_lifecycle'),
            get_string('participants'), ]);
        $this->setup();
    }

    /**
     * Render step column.
     * @param object $row Row data.
     * @return string link to the workflow details
     * @throws moodle_exception
     */
    public function col_step($row) {
        $out = \html_writer::link(new \moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $row->workflowid]), $row->step);
        return $row->step ? $out : '--';
    }

    /**
     * Render timedeleted column.
     * @param object $row Row data.
     * @return string date of the course deletion
     */
    public function col_timedeleted($row) {
        global $USER;
        return userdate($row->timedeleted, '',
            core_date::get_user_timezone($USER));
    }
}
