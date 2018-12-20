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
 * Settings page which gives an overview over running lifecycle processes.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    require_once(__DIR__ . '/adminlib.php');

    $category = new admin_category('lifecycle_category',
        get_string('pluginname', 'tool_lifecycle'));
    $ADMIN->add('tools', $category);
    $settings = new admin_settingpage('tool_lifecycle',
        get_string('general_config_header', 'tool_lifecycle'));
    $ADMIN->add('lifecycle_category', $settings);

    $settings->add(new admin_setting_configduration('lifecycle_duration',
        get_string('config_delay_duration', 'tool_lifecycle'),
        get_string('config_delay_duration_desc', 'tool_lifecycle'),
        183 * 24 * 60 * 60)); // Dafault value is 180 days.

    $ADMIN->add('lifecycle_category', new tool_lifecycle\admin_page_active_processes());
    $ADMIN->add('lifecycle_category', new tool_lifecycle\admin_page_deactivated_workflows());
    $ADMIN->add('lifecycle_category', new tool_lifecycle\admin_page_course_backups());
    $ADMIN->add('lifecycle_category', new tool_lifecycle\admin_page_sublugins());

    if ($ADMIN->fulltree) {
        $triggers = core_component::get_plugin_list('lifecycletrigger');
        foreach ($triggers as $trigger => $path) {
            if (file_exists($settingsfile = $path . '/settings.php')) {
                $settings->add(new admin_setting_heading('lifecycletriggersetting'.$trigger,
                    get_string('trigger', 'tool_lifecycle') .
                    ' - ' . get_string('pluginname', 'lifecycletrigger_' . $trigger), ''));
                include($settingsfile);
            }
        }
    }
}