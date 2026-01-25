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

use core\output\single_button;
use core_date;
use tool_lifecycle\urls;

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
     * @var array "cached" lang strings
     */
    private $strings;

    /**
     * @var int timestamp of delete date => delete backup files which were created before that date
     */
    private $deletedate;

    /**
     * Constructor for course_backups_table.
     * @param int $uniqueid Unique id of this table.
     * @param \stdClass|null $filterdata
     * @throws \coding_exception
     */
    public function __construct($uniqueid, $filterdata) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;
        $this->set_attribute('class', $this->attributes['class'] . ' ' . $uniqueid);

        $this->strings['deleteselectedbuttonlabel'] = get_string('deleteselectedbuttonlabel',
            'lifecyclestep_createbackup');
        $this->strings['deleteallbuttonlabel'] = get_string('deleteallbuttonlabel',
            'lifecyclestep_createbackup');

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

            if ($filterdata->deletedate) {
                $where[] = 'b.backupcreated < :deletedate';
                $params['deletedate'] = $filterdata->deletedate;
                $deletedate = $filterdata->deletedate;
                if (!is_int($deletedate)) {
                    $deletedate = make_timestamp($deletedate['year'], $deletedate['month'], $deletedate['day'],
                        $deletedate['hour'], $deletedate['minute']);
                }
                $this->deletedate = $deletedate;
            }
        }

        $this->set_sql('b.id, b.courseid, COALESCE(c.shortname, b.shortname) as courseshortname,
                COALESCE(c.fullname, b.fullname) as coursefullname, s.instancename as step, s.workflowid,
                b.backupcreated',
            '{tool_lifecycle_backups} b LEFT JOIN
                   {course} c ON c.id = b.courseid LEFT JOIN
                   {tool_lifecycle_step} s ON s.id = b.step',
            join(" AND ", $where), $params);
        $this->no_sorting('checkbox');
        $this->no_sorting('download');
        $this->no_sorting('restore');
        $this->define_baseurl($PAGE->url);
        $this->init();
    }

    /**
     * Initialize the table.
     */
    public function init() {
        $checked = false;
        if ($this->deletedate ?? false) {
            $checked = true;
        }
        $this->define_columns(['checkbox', 'courseid', 'coursename', 'step', 'backupcreated', 'download', 'restore']);
        $this->define_headers([
            \html_writer::checkbox('checkall', null, $checked),
            get_string('courseid', 'tool_lifecycle'),
            get_string('coursename', 'tool_lifecycle'),
            get_string('step', 'tool_lifecycle'),
            get_string('backupcreated', 'tool_lifecycle'),
            get_string('download', 'tool_lifecycle'),
            get_string('restore', 'tool_lifecycle'), ]);
        $this->setup();
    }

    /**
     * Column of checkboxes.
     * @param object $row
     * @return string
     */
    public function col_checkbox($row) {
        $checked = false;
        if ($this->deletedate ?? false) {
            $checked = true;
        }
        return \html_writer::checkbox('c[]', $row->id, $checked);
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
     * Render backupcreated column.
     * @param object $row Row data.
     * @return string date of the backupcreated
     */
    public function col_step($row) {
        $out = \html_writer::link(new \moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $row->workflowid]), $row->step);
        return $row->step ? $out : '--';
    }

    /**
     * Render backupcreated column.
     * @param object $row Row data.
     * @return string date of the backupcreated
     */
    public function col_backupcreated($row) {
        global $USER;
        return userdate($row->backupcreated, '',
            core_date::get_user_timezone($USER));
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

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_start() {
        global $OUTPUT, $PAGE;

        parent::wrap_html_start();

        $output = \html_writer::empty_tag('input',
            [
                'type' => 'button',
                'action' => 'deleteselected',
                'sesskey' => sesskey(),
                'name' => 'button_delete_selected',
                'value' => $this->strings['deleteselectedbuttonlabel'],
                'class' => 'selectedbutton btn btn-secondary mr-2 mb-1',
            ]
        );

        if ($this->deletedate ?? false) {
            $button = new \single_button(
                new \moodle_url($PAGE->url, [
                    'action' => 'deleteall',
                    'deletedate' => $this->deletedate,
                    'sesskey' => sesskey(),
                ]),
                $this->strings['deleteallbuttonlabel'],
                'post',
                single_button::BUTTON_PRIMARY
            );
            $button->add_confirm_action(get_string('delete_all_confirmation_text', 'tool_lifecycle'));
            $output .= $OUTPUT->render($button);
            $output .= \html_writer::span(get_string('deletealldescription', 'tool_lifecycle'), "ml-1");
        }

        echo $output;

    }
}
