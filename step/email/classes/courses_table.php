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

namespace lifecyclestep_email;

use tool_lifecycle\local\table\interaction_table;

/**
 * Static HTML table of courses for the ##courses-table## email placeholder.
 *
 * Reuses the column renderers (course name, start date, category) of the course table
 * on admin/tool/lifecycle/view.php, but outputs a plain html table without paging,
 * sorting or JavaScript, so it can be embedded in an email.
 *
 * @package    lifecyclestep_email
 * @copyright  2026 Dustin Schiele <dustin.schiele@fhnw.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_table extends interaction_table {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('lifecyclestep_email_courses_table');
        $this->init();
    }

    /**
     * Defines the columns: the first three columns of the table on view.php.
     */
    public function init() {
        $this->define_columns(['coursefullname', 'startdate', 'category']);
        $this->define_headers([
            get_string('coursename', 'tool_lifecycle'),
            get_string('startdate'),
            get_string('category'),
        ]);
    }

    /**
     * Not used, the tools column is not part of this table.
     * @param object $row Row data.
     * @return string
     */
    public function col_tools($row) {
        return '';
    }

    /**
     * Renders the table for the given courses.
     * @param int[] $courseids ids of the courses to list.
     * @return string html table, or an empty string if there are no courses.
     */
    public function render(array $courseids): string {
        global $DB;

        if (empty($courseids)) {
            return '';
        }

        // Same field aliases as interaction_attention_table, which the column renderers expect.
        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $rows = $DB->get_records_sql(
            "SELECT c.id AS courseid, c.fullname AS coursefullname, c.shortname AS courseshortname,
                    c.startdate, cc.name AS category, cc.path AS categorypath
               FROM {course} c
          LEFT JOIN {course_categories} cc ON cc.id = c.category
              WHERE c.id $insql
           ORDER BY c.fullname, c.id",
            $params
        );

        $table = new \html_table();
        // Email clients ignore Moodle's CSS, so give the table visible borders.
        $table->attributes = ['border' => '1', 'style' => 'border-collapse: collapse;'];
        $table->cellpadding = 4;
        $table->head = $this->headers;
        foreach ($rows as $row) {
            $table->data[] = array_values($this->format_row($row));
        }
        return \html_writer::table($table);
    }
}
