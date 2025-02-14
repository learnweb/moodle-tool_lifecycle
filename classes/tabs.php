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

/**
 * Get HTML of the tab row
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class tabs {
    public static function get_tabrow() {

        $targeturl = new \moodle_url('/admin/settings.php', ['section' => 'tool_lifecycle', 'id' => 'settings']);
        $tabrow[] = new \tabobject('settings', $targeturl,
            get_string('general_config_header', 'tool_lifecycle'));

        $targeturl = new \moodle_url('/admin/tool/lifecycle/workflowdrafts.php', ['id' => 'workflowdrafts']);
        $tabrow[] = new \tabobject('workflowdrafts', $targeturl,
            get_string('workflow_drafts_header', 'tool_lifecycle'));

        $targeturl = new \moodle_url('/admin/tool/lifecycle/activeworkflows.php', ['id' => 'activeworkflows']);
        $tabrow[] = new \tabobject('activeworkflows', $targeturl,
            get_string('active_workflows_header', 'tool_lifecycle'));

        $targeturl = new \moodle_url('/admin/tool/lifecycle/deactivatedworkflows.php', ['id' => 'deactivatedworkflows']);
        $tabrow[] = new \tabobject('deactivatedworkflows', $targeturl,
            get_string('deactivated_workflows_header', 'tool_lifecycle'));

        $targeturl = new \moodle_url('/admin/tool/lifecycle/coursebackups.php', ['id' => 'coursebackups']);
        $tabrow[] = new \tabobject('coursebackups', $targeturl,
            get_string('course_backups_list_header', 'tool_lifecycle'));

        $targeturl = new \moodle_url('/admin/tool/lifecycle/delayedcourses.php', ['id' => 'delayedcourses']);
        $tabrow[] = new \tabobject('delayedcourses', $targeturl,
            get_string('delayed_courses_header', 'tool_lifecycle'));

        $targeturl = new \moodle_url('/admin/tool/lifecycle/errors.php', ['id' => 'errors']);
        $tabrow[] = new \tabobject('errors', $targeturl,
            get_string('process_errors_header', 'tool_lifecycle'));

        return $tabrow;

    }
}
