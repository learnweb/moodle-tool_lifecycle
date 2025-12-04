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
 * Helper functions for tool_lifecycle.
 * @package    tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Check if a plugin is installed.
 * @param string $plugin name of the plugin
 * @param string $plugintype name of the plugin's plugin type
 * @return bool
 */
function lifecycle_is_plugin_installed($plugin, $plugintype) {
    $pluginsinstalled = core_component::get_plugin_list($plugintype);
    $found = false;
    foreach ($pluginsinstalled as $installed => $path) {
        if ($plugin == $installed) {
            $found = true;
            break;
        }
    }
    return $found;
}
