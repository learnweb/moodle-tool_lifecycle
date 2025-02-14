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
 * @copyright  2025 Thomas Niedermaier WWU
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_lifecycle\settings\admin_settings_builder;
use tool_lifecycle\tabs;

// Check for the moodle/site:config permission.
if ($hassiteconfig) {
//    admin_settings_builder::create_settings();
}

/*if ($hassiteconfig) {

    $triggers = core_component::get_plugin_list('lifecycletrigger');
    $steps = core_component::get_plugin_list('lifecyclestep');
    $subpluginsinstalled = $triggers || $steps;

    if ($ADMIN->fulltree) {
        if ($subpluginsinstalled) {
            // Admin category for subplugins.
            $ADMIN->add('tools', new admin_category('tool_lifecycle',
                get_string('pluginname', 'tool_lifecycle', null, true)));
            // Triggers list.
            if ($triggers) {
                foreach ($triggers as $trigger => $path) {
                    if (file_exists($settingsfile = $path . '/settings.php')) {
                        include($settingsfile);
                    }
                }
            }
            // Steps list.
            if ($steps) {
                foreach ($steps as $step => $path) {
                    if (file_exists($settingsfile = $path . '/settings.php')) {
                        include($settingsfile);
                    }
                }
            }
        } else {  // Site administration page no subplugins.
            // Just admin setting page.
            $ADMIN->add(admin_settingpage('tool_lifecycle',
                get_string('pluginname', 'tool_lifecycle')));
        }

    } else {
        $tabrow = tabs::get_tabrow();
        $id = optional_param('id', 'settings', PARAM_TEXT);
        $tabs = array($tabrow);
        $output = print_tabs($tabs, $id, null, null, true);

        // Main config page.
        $page = new admin_settingpage('tool_lifecycle',
            get_string('pluginname', 'tool_lifecycle'));
        $page->add(new admin_setting_heading('tool_lifecycle',
            $output,  html_writer::span(get_string('general_settings_header', 'tool_lifecycle'), 'h3')));
        $page->add(new admin_setting_configduration('tool_lifecycle/duration',
            get_string('config_delay_duration', 'tool_lifecycle'),
            get_string('config_delay_duration_desc', 'tool_lifecycle'),
            183 * 24 * 60 * 60)); // Dafault value is 180 days.
        $page->add(new admin_setting_configdirectory('tool_lifecycle/backup_path',
            get_string('config_backup_path', 'tool_lifecycle'),
            get_string('config_backup_path_desc', 'tool_lifecycle'),
            $CFG->dataroot . DIRECTORY_SEPARATOR . 'lifecycle_backups'));
        $page->add(new admin_setting_configcheckbox('tool_lifecycle/showcoursecounts',
            get_string('config_showcoursecounts', 'tool_lifecycle'),
            get_string('config_showcoursecounts_desc', 'tool_lifecycle'),
            1));

        if ($triggers) {
            foreach ($triggers as $trigger => $path) {
                if (file_exists($settingsfile = $path . '/settings.php')) {
                    $page->add(new admin_setting_heading('lifecycletriggersetting'.$trigger,
                        get_string('trigger', 'tool_lifecycle') .
                        ' - ' . get_string('pluginname', 'lifecycletrigger_' . $trigger), ''));
//                    include($settingsfile);
                }
            }
        } else {
            $page->add(new admin_setting_heading('adminsettings_notriggers',
                get_string('adminsettings_notriggers', 'tool_lifecycle'), ''));
        }

        if ($steps) {
            foreach ($steps as $step => $path) {
                if (file_exists($settingsfile = $path . '/settings.php')) {
                    $page->add(new admin_setting_heading('lifecyclestepsetting'.$step,
                        get_string('step', 'tool_lifecycle') .
                        ' - ' . get_string('pluginname', 'lifecyclestep_' . $step), ''));
//                    include($settingsfile);
                }
            }
        } else {
            $page->add(new admin_setting_heading('adminsettings_nosteps',
                get_string('adminsettings_nosteps', 'tool_lifecycle'), ''));
        }

        $ADMIN->add('tools', $page);
    }

}*/
