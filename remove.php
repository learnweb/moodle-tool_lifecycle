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
 * Remove trigger subplugins sitecourse and delayedcourses.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$PAGE->set_context(context_system::instance());

require_login(null, false);
require_capability('moodle/site:config', context_system::instance());

echo "<div>Start ".userdate(time())."</div>";

// Remove sitecourse subplugin if there is one, otherwise set all workflows to includesitecourse = 1.
$purgecaches = false;
$pluginmanager = core_plugin_manager::instance();
if ($plugininfo = $pluginmanager->get_plugin_info('lifecycletrigger_sitecourse')) {
    echo "<div>Sitecourse Start ".userdate(time())."</div>";
    $trace = new \null_progress_trace();
    $plugininfo->uninstall($trace);
    echo "<div>Sitecourse After Plugin uninstall ".userdate(time())."</div>";
    if ($pluginmanager->is_plugin_folder_removable($plugininfo->component)) {
        $pluginmanager->uninstall_plugin($plugininfo->component, $trace);
        $pluginmanager->remove_plugin_folder($plugininfo);
        echo "<div>Sitecourse Removed ".userdate(time())."</div>";
    }
    $purgecaches = true;
    echo "<div>Sitecourse End ".userdate(time())."</div>";
} else {
    echo "<div>Sitecourse No Plugin Found ".userdate(time())."</div>";
    $DB->set_field('tool_lifecycle_workflow', 'includesitecourse', 1);
}

// Remove delayedcourses subplugin if there is one, otherwise set all workflows to includedelayedcourses = 1.
$pluginmanager = core_plugin_manager::instance();
if ($plugininfo = $pluginmanager->get_plugin_info('lifecycletrigger_delayedcourses')) {
    echo "<div>Delayedcourses Start ".userdate(time())."</div>";
    $trace = new \null_progress_trace();
    $plugininfo->uninstall($trace);
    echo "<div>Delayedcourses After Plugin Uninstall ".userdate(time())."</div>";
    if ($pluginmanager->is_plugin_folder_removable($plugininfo->component)) {
        $pluginmanager->uninstall_plugin($plugininfo->component, $trace);
        $pluginmanager->remove_plugin_folder($plugininfo);
        echo "<div>Delayedcourses Removed ".userdate(time())."</div>";
    }
    $purgecaches = true;
    echo "<div>Delayedcourses End ".userdate(time())."</div>";
} else {
    echo "<div>Delayedcourses No Plugin Found ".userdate(time())."</div>";
    $DB->set_field('tool_lifecycle_workflow', 'includedelayedcourses', 1);
}

if ($purgecaches) {
    echo "<div>Purge Caches ".userdate(time())."</div>";
    purge_all_caches();
}

echo "<div>End ".userdate(time())."</div>";
