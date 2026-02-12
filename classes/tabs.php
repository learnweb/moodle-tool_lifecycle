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

use tool_lifecycle\local\manager\delayed_courses_manager;
use core\exception\moodle_exception;
use stdClass;

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
     * @param object $params
     * @return array of tabobjects
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function get_tabrow($params = null) {
        global $DB;

        $activelink = false;
        $deactivatedlink = false;
        $draftlink = false;
        $approvelink = false;
        if ($params !== null) {
            if (isset($params->activelink)) {
                $activelink = true;
            }
            if (isset($params->deactivatedlink)) {
                $deactivatedlink = true;
            }
            if (isset($params->draftlink)) {
                $draftlink = true;
            }
            if (isset($params->approvelink)) {
                $approvelink = true;
            }
        }

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
        $deactivatedwf = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        $time = time();
        // Get number of delayed courses.
        $sql = "select count(c.id) from {course} c LEFT JOIN
                (SELECT dw.courseid, dw.workflowid, w.title as workflow,
                        dw.delayeduntil as workflowdelay,maxtable.wfcount as workflowcount
                FROM (SELECT courseid, MAX(dw.id) AS maxid, COUNT(*) AS wfcount FROM {tool_lifecycle_delayed_workf} dw
                    JOIN {tool_lifecycle_workflow} w ON dw.workflowid = w.id
                    WHERE dw.delayeduntil >= $time AND w.timeactive IS NOT NULL GROUP BY courseid) maxtable JOIN
                    {tool_lifecycle_delayed_workf} dw ON maxtable.maxid = dw.id JOIN
                    {tool_lifecycle_workflow} w ON dw.workflowid = w.id ) wfdelay ON wfdelay.courseid = c.id LEFT JOIN
                    (SELECT * FROM {tool_lifecycle_delayed} d WHERE d.delayeduntil > $time ) d ON c.id = d.courseid JOIN
                    {course_categories} cat ON c.category = cat.id
                    where COALESCE(wfdelay.courseid, d.courseid) IS NOT NULL";
        $i = $DB->count_records_sql($sql);
        $delayedcourses = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

        // Get number of outstanding admin approvals.
        $sql = "SELECT COUNT(1) FROM ( SELECT p.workflowid, p.stepindex, COUNT(1) as courses
                    FROM {lifecyclestep_adminapprove} a JOIN {tool_lifecycle_process} p ON p.id = a.processid
                    WHERE a.status = 0 GROUP BY p.workflowid, p.stepindex ) b
                JOIN {tool_lifecycle_step} s ON s.workflowid = b.workflowid AND s.sortindex = b.stepindex
                JOIN {tool_lifecycle_workflow} w ON w.id = b.workflowid
                WHERE TRUE";
        // Get number of outstanding admin approvals.
        $i = $DB->count_records_sql($sql);
        $adminapprovals = \html_writer::span($i, $i > 0 ? $classnotnull : $classnull);

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

        // General Settings.
        $targeturl = new \moodle_url('/admin/settings.php', ['section' => 'lifecycle']);
        $tabrow[] = new \tabobject('settings', $targeturl,
            get_string('general_config_header', 'tool_lifecycle'),
            get_string('general_config_header_title', 'tool_lifecycle'));

        // Subplugins.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/subplugins.php');
        $tabrow[] = new \tabobject('subplugins', $targeturl,
            get_string('subplugins', 'tool_lifecycle'),
            get_string('subpluginsdesc', 'tool_lifecycle'));

        // Tab to the draft workflows page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/workflowdrafts.php');
        $tabrow[] = new \tabobject('workflowdrafts', $targeturl,
            get_string('workflow_drafts_header', 'tool_lifecycle').$drafts,
            get_string('workflow_drafts_header_title', 'tool_lifecycle'), $draftlink);

        // Tab to the active workflows page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/activeworkflows.php');
        $tabrow[] = new \tabobject('activeworkflows', $targeturl,
            get_string('active_workflows_header', 'tool_lifecycle').$activewf,
            get_string('active_workflows_header_title', 'tool_lifecycle'), $activelink);

        // Tab to the deactivated workflows page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php');
        $tabrow[] = new \tabobject('deactivatedworkflows', $targeturl,
            get_string('deactivated_workflows_header', 'tool_lifecycle').$deactivatedwf,
            get_string('deactivated_workflows_header_title', 'tool_lifecycle'), $deactivatedlink);

        // Tab to the delayed courses list page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/delayedcourses.php');
        $tabrow[] = new \tabobject('delayedcourses', $targeturl,
            get_string('delayed_courses_header', 'tool_lifecycle').$delayedcourses,
            get_string('delayed_courses_header_title', 'tool_lifecycle'));

        // Tab to the admin approval list page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/step/adminapprove/index.php');
        $tabrow[] = new \tabobject('adminapprove', $targeturl,
            get_string('adminapprovals_header', 'tool_lifecycle').$adminapprovals,
            get_string('adminapprovals_header_title', 'tool_lifecycle'), $approvelink);

        // Tab to the course backups list page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/coursebackups.php');
        $tabrow[] = new \tabobject('coursebackups', $targeturl,
            get_string('course_backups_list_header', 'tool_lifecycle').$coursebackups,
            get_string('course_backups_list_header_title', 'tool_lifecycle'));

        // Tab to the lifecycle errors page.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/errors.php');
        $tabrow[] = new \tabobject('errors', $targeturl,
            get_string('process_errors_header', 'tool_lifecycle').$lcerrors,
            get_string('process_errors_header_title', 'tool_lifecycle'));

        // Showcase.
        $targeturl = new \moodle_url('/admin/tool/lifecycle/workflowshowcase.php');
        $tabrow[] = new \tabobject('showcase', $targeturl,
            get_string('showcase', 'tool_lifecycle'),
            get_string('showcasedesc', 'tool_lifecycle'));

        return $tabrow;

    }
}
