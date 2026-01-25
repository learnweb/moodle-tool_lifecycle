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

function lifecycle_select_change_workflow($activewf) {
    global $OUTPUT, $DB, $PAGE;

    $records = $DB->get_records_sql(
        'SELECT id, title, timeactive FROM {tool_lifecycle_workflow} ORDER BY title ASC');

    $url = $PAGE->url;
    $actionmenu = new \action_menu();
    foreach ($records as $record) {
        if ($record->id == $activewf) {
            continue;
        }
        $pix = $record->timeactive ? 'i/hide' : 'i/show';
        $actionmenu->add_secondary_action(
            new \action_menu_link_secondary(
                new \moodle_url($url, ['wf' => $record->id]),
                new \pix_icon($pix, $record->title),
                $record->title
            )
        );
    }

    $actionmenu->set_menu_trigger(get_string('changeworkflow', 'tool_lifecycle'));
    echo $OUTPUT->render_action_menu($actionmenu);

}