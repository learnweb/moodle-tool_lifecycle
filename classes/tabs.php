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
 * Tab row to jump to other pages within this plugin.
 * @package    tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle;

use core\exception\moodle_exception;

/**
 * Class to generate a tab row for navigation within this plugin
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tabs {
    /**
     * Generates a Moodle tabrow i.e. an array of tabs
     *
     * @param bool $activelink display active workflows tab as link
     * @param bool $deactivatelink display deactivated workflows tab as link
     * @param bool $draftlink display draft workflows tab as link
     * @return array of tabobjects
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function get_tabrow($activelink = false, $deactivatelink = false, $draftlink = false) {
        global $DB;

        $classnotnull = 'badge badge-primary badge-pill ml-1';
        $classnull = 'badge badge-secondary badge-pill ml-1';

        // Get number of drafts.
        $sql = "select count(id)
        from {tool_lifecycle_workflow}
        where timeactive IS NULL AND timedeactive IS NULL";
        $i = $DB->count_records_sql($sql);
        $drafts = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        // Get number of active workflows.
        $sql = "select count(id)
        from {tool_lifecycle_workflow}
        where timeactive IS NOT NULL";
        $i = $DB->count_records_sql($sql);
        $activewf = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        // Get number of deactivated workflows.
        $sql = "select count(id)
        from {tool_lifecycle_workflow}
        where timeactive IS NULL AND timedeactive IS NOT NULL";
        $i = $DB->count_records_sql($sql);
        $deactivatedewf = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        $time = time();
        // Get number of delayed courses.
        $sql = "select count(c.id) from {course} c LEFT JOIN
        (SELECT dw.courseid, dw.workflowid, w.title as workflow, dw.delayeduntil as workflowdelay,maxtable.wfcount as workflowcount
         FROM ( SELECT courseid, MAX(dw.id) AS maxid, COUNT(*) AS wfcount FROM {tool_lifecycle_delayed_workf} dw
            JOIN {tool_lifecycle_workflow} w ON dw.workflowid = w.id
            WHERE dw.delayeduntil >= $time AND w.timeactive IS NOT NULL GROUP BY courseid ) maxtable JOIN
             {tool_lifecycle_delayed_workf} dw ON maxtable.maxid = dw.id JOIN
             {tool_lifecycle_workflow} w ON dw.workflowid = w.id ) wfdelay ON wfdelay.courseid = c.id LEFT JOIN
            (SELECT * FROM {tool_lifecycle_delayed} d WHERE d.delayeduntil > $time ) d ON c.id = d.courseid JOIN
            {course_categories} cat ON c.category = cat.id
        where COALESCE(wfdelay.courseid, d.courseid) IS NOT NULL";
        $i = $DB->count_records_sql($sql);
        $delayedcourses = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        // Get number of lifecycle course backups.
        $sql = "select count(id)
        from {tool_lifecycle_backups}";
        $i = $DB->count_records_sql($sql);
        $coursebackups = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        // Get number of stores lifecycle errors.
        $sql = "select count(id)
        from {tool_lifecycle_proc_error}";
        $i = $DB->count_records_sql($sql);
        $lcerrors = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        // General Settings and Subplugins.
        $targeturl = new \moodle_url('/admin/category.php', ['category' => 'lifecycle']);
        $tabrow[] = new \tabobject('settings', $targeturl,
            get_string('general_config_header', 'tool_lifecycle'));

        // Tab to the draft workflows page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/workflowdrafts.php', ['id' => 'workflowdrafts']);
        $tabrow[] = new \tabobject('workflowdrafts', $targeturl,
            get_string('workflow_drafts_header', 'tool_lifecycle').$drafts,
            get_string('workflow_drafts_header', 'tool_lifecycle'), $draftlink);

        // Tab to the active workflows page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/activeworkflows.php', ['id' => 'activeworkflows']);
        $tabrow[] = new \tabobject('activeworkflows', $targeturl,
            get_string('active_workflows_header', 'tool_lifecycle').$activewf,
            get_string('active_workflows_header', 'tool_lifecycle'), $activelink);

        // Tab to the deactivated workflows page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php', ['id' => 'deactivatedworkflows']);
        $tabrow[] = new \tabobject('deactivatedworkflows', $targeturl,
            get_string('deactivated_workflows_header', 'tool_lifecycle').$deactivatedewf,
            get_string('deactivated_workflows_header', 'tool_lifecycle'), $deactivatelink);

        // Tab to the delayed courses list page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/delayedcourses.php', ['id' => 'delayedcourses']);
        $tabrow[] = new \tabobject('delayedcourses', $targeturl,
            get_string('delayed_courses_header', 'tool_lifecycle').$delayedcourses,
            get_string('delayed_courses_header', 'tool_lifecycle'));

        // Tab to the course backups list page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/coursebackups.php', ['id' => 'coursebackups']);
        $tabrow[] = new \tabobject('coursebackups', $targeturl,
            get_string('course_backups_list_header', 'tool_lifecycle').$coursebackups,
            get_string('course_backups_list_header', 'tool_lifecycle'));

        // Tab to the lifecycle errors page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/errors.php', ['id' => 'errors']);
        $tabrow[] = new \tabobject('errors', $targeturl,
            get_string('process_errors_header', 'tool_lifecycle').$lcerrors,
            get_string('process_errors_header', 'tool_lifecycle'));

        return $tabrow;

    }
}
