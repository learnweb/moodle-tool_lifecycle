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
namespace tool_lifecycle\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class course_backups_table extends \table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE;
        $this->set_attribute('class', $this->attributes['class'] . ' ' . $uniqueid);
        $this->set_sql('b.*',
            '{tool_lifecycle_backups} b',
            "TRUE");
        $this->sortable(false);
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    public function init() {
        $this->define_columns(['courseid', 'courseshortname', 'coursefullname', 'backupcreated', 'download', 'restore']);
        $this->define_headers([
            get_string('course'),
            get_string('shortnamecourse'),
            get_string('fullnamecourse'),
            get_string('backupcreated', 'tool_lifecycle'),
            get_string('download', 'tool_lifecycle'),
            get_string('restore', 'tool_lifecycle')]);
        $this->setup();
    }

    /**
     * Render courseid column.
     * @param $row
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
     * Render courseshortname column.
     * @param $row
     * @return string course link
     */
    public function col_courseshortname($row) {
        try {
            return \html_writer::link(course_get_url($row->courseid), $row->shortname);
        } catch (\dml_missing_record_exception $e) {
            return $row->shortname;
        }
    }

    /**
     * Render coursefullname column.
     * @param $row
     * @return string course link
     */
    public function col_coursefullname($row) {
        try {
            return \html_writer::link(course_get_url($row->courseid), $row->fullname);
        } catch (\dml_missing_record_exception $e) {
            return $row->fullname;
        }
    }

    /**
     * Render backupcreated column.
     * @param $row
     * @return string date of the backupcreated
     */
    public function col_backupcreated($row) {
        return userdate($row->backupcreated);
    }

    /**
     * Render download column.
     * @param $row
     * @return string action buttons for downloading a backup.
     */
    public function col_download($row) {
        return \html_writer::link(
                new \moodle_url('/admin/tool/lifecycle/downloadbackup.php', array('backupid' => $row->id)),
                get_string('download', 'tool_lifecycle')
        );
    }

    /**
     * Render restore column.
     * @param $row
     * @return string action buttons for restoring a course.
     */
    public function col_restore($row) {
        return \html_writer::link(
            new \moodle_url('/admin/tool/lifecycle/restore.php', array('backupid' => $row->id)),
                get_string('restore', 'tool_lifecycle')
        );
    }
}