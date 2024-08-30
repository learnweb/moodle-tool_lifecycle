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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Table listing course backups
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_backups_table extends \table_sql {

    /**
     * Constructor for course_backups_table.
     * @param int $uniqueid Unique id of this table.
     * @param \stdClass|null $filterdata
     */
    public function __construct($uniqueid, $filterdata) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;
        $this->set_attribute('class', $this->attributes['class'] . ' ' . $uniqueid);

        $where = ['TRUE'];
        $params = [];

        if ($filterdata) {
            if ($filterdata->shortname) {
                $where[] = $DB->sql_like('b.shortname', ':shortname', false, false);
                $params['shortname'] = '%' . $DB->sql_like_escape($filterdata->shortname) . '%';
            }

            if ($filterdata->fullname) {
                $where[] = $DB->sql_like('b.fullname', ':fullname', false, false);
                $params['fullname'] = '%' . $DB->sql_like_escape($filterdata->fullname) . '%';
            }

            if ($filterdata->courseid) {
                $where[] = 'b.courseid = :courseid';
                $params['courseid'] = $filterdata->courseid;
            }
        }

        $this->set_sql('b.id, b.courseid, b.shortname as courseshortname, b.fullname as coursefullname, b.backupcreated',
            '{tool_lifecycle_backups} b',
            join(" AND ", $where), $params);
        $this->no_sorting('download');
        $this->no_sorting('restore');
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialize the table.
     */
    public function init() {
        $this->define_columns(['courseid', 'courseshortname', 'coursefullname', 'backupcreated', 'download', 'restore']);
        $this->define_headers([
            get_string('courseid', 'tool_lifecycle'),
            get_string('shortnamecourse'),
            get_string('fullnamecourse'),
            get_string('backupcreated', 'tool_lifecycle'),
            get_string('download', 'tool_lifecycle'),
            get_string('restore', 'tool_lifecycle'), ]);
        $this->setup();
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
     * Render courseshortname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_courseshortname($row) {
        try {
            return \html_writer::link(course_get_url($row->courseid), $row->courseshortname);
        } catch (\dml_missing_record_exception $e) {
            return $row->courseshortname;
        }
    }

    /**
     * Render coursefullname column.
     * @param object $row Row data.
     * @return string course link
     */
    public function col_coursefullname($row) {
        try {
            return \html_writer::link(course_get_url($row->courseid), format_string($row->coursefullname));
        } catch (\dml_missing_record_exception $e) {
            return format_string($row->coursefullname);
        }
    }

    /**
     * Render backupcreated column.
     * @param object $row Row data.
     * @return string date of the backupcreated
     */
    public function col_backupcreated($row) {
        return userdate($row->backupcreated);
    }

    /**
     * Render download column.
     * @param object $row Row data.
     * @return string action buttons for downloading a backup.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_download($row) {
        return \html_writer::link(
                new \moodle_url('/admin/tool/lifecycle/downloadbackup.php', ['backupid' => $row->id]),
                get_string('download', 'tool_lifecycle')
        );
    }

    /**
     * Render restore column.
     * @param object $row Row data.
     * @return string action buttons for restoring a course.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_restore($row) {
        return \html_writer::link(
            new \moodle_url('/admin/tool/lifecycle/restore.php', ['backupid' => $row->id]),
                get_string('restore', 'tool_lifecycle')
        );
    }
}
