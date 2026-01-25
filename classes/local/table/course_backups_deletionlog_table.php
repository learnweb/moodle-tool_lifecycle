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
 * @copyright 2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\table;

use core_date;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing of course backup deletions' log entries for a given step
 *
 * @package tool_lifecycle
 * @copyright 2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_backups_deletionlog_table extends \table_sql {

    /** @var int $tablerows number of table rows effectively written */
    public $tablerows = 0;

    /**
     * Constructor for course_backup_deletionlog_table.
     * @param int $stepid id of the step
     * @throws \coding_exception
     */
    public function __construct($stepid) {
        parent::__construct('tool_lifecycle-backupdeletionlog-list');
        global $PAGE, $DB;

        $stepname = $DB->get_field('tool_lifecycle_step', 'instancename', ['id' => $stepid]);
        $this->caption = get_string('backupdeletionlogtable', 'lifecyclestep_deletebackup', $stepname);
        $this->captionattributes = ['class' => 'ml-3'];

        $this->set_sql('b.id, b.courseid, c.shortname as courseshortname,
                c.fullname as coursefullname, b.files as filesdeleted, b.timestampdeleted',
            '{tool_lifecycle_deletebackup_log} b LEFT JOIN 
                   {course} c ON c.id = b.courseid LEFT JOIN 
                   {tool_lifecycle_step} s ON s.id = b.stepid',
            'b.stepid = :stepid', ['stepid' => $stepid]);
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialize the table.
     */
    public function init() {
        $this->define_columns(['courseid', 'coursename', 'backupdeleted', 'filesdeleted']);
        $this->define_headers([
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('backupdeleted', 'lifecyclestep_deletebackup'),
            get_string('files', 'lifecyclestep_deletebackup'), ]);
        $this->setup();
    }

    /**
     * Build the table from the fetched data.
     *
     * Take the data returned from the db_query and go through all the rows
     * processing each col using either col_{columnname} method or other_cols
     * method or if other_cols return NULL, then put the data straight into the
     * table.
     *
     * After calling this function, remember to call close_recordset.
     */
    public function build_table() {
        if (!$this->rawdata) {
            return;
        }
        foreach ($this->rawdata as $row) {
            $formattedrow = $this->format_row($row);
            $this->add_data_keyed($formattedrow, $this->get_row_class($row));
            $this->tablerows++;
        }
    }

    /**
     * Render courseid column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_courseid($row) {
        try {
            return \html_writer::link(course_get_url($row->courseid), $row->courseid);
        } catch (\dml_missing_record_exception $e) {
            return $row->courseid;
        }
    }

    /**
     * Render coursename column.
     * @param object $row Row data.
     * @return string course name + link
     */
    public function col_coursename($row) {
        $out = \html_writer::link(course_get_url($row->courseid), format_string($row->coursefullname));
        if ($row->coursefullname != $row->courseshortname) {
            $out .= \html_writer::div($row->courseshortname, 'text-info');
        }
        return $out;
    }

    /**
     * Render backupdeleted column.
     * @param object $row Row data.
     * @return string date of the backupdeleted
     */
    public function col_backupdeleted($row) {
        global $USER;
        return userdate($row->timestampdeleted, '',
            core_date::get_user_timezone($USER));
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_finish() {
        echo \html_writer::div(get_string('total')." ".get_string('page').": ".$this->tablerows." ".
            get_string('courses'), 'm-3');
    }
}
