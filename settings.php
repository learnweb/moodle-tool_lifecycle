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
 * @copyright  2025 Thomas Niedermaier Universität Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_lifecycle\tabs;

// Check for the moodle/site:config permission.
if ($hassiteconfig) {

    $triggers = core_component::get_plugin_list('lifecycletrigger');
    $steps = core_component::get_plugin_list('lifecyclestep');

    $ADMIN->add('tools', new admin_category('lifecycle',
        get_string('pluginname', 'tool_lifecycle')));
    $settings = new admin_settingpage('lifecycle_settings',
        get_string('general_config_header', 'tool_lifecycle'));

    if (!$ADMIN->fulltree) {
        $stepsortriggersettings = false;
        // Check if there are trigger settings pages.
        if ($triggers) {
            foreach ($triggers as $trigger => $path) {
                if (file_exists($settingsfile = $path . '/settings.php')) {
                    $stepsortriggersettings = true;
                    break;
                }
            }
        }
        // Check if there are step settings pages.
        if (!$stepsortriggersettings && $steps) {
            foreach ($steps as $step => $path) {
                if (file_exists($settingsfile = $path . '/settings.php')) {
                    $stepsortriggersettings = true;
                    break;
                }
            }
        }
        if ($stepsortriggersettings) {
            // Include settings page of each trigger subplugin, if there is one.
            if ($triggers) {
                foreach ($triggers as $trigger => $path) {
                    if (file_exists($settingsfile = $path . '/settings.php')) {
                        include($settingsfile);
                    }
                }
            }
            // Include settings page of each step subplugin, if there is one.
            if ($steps) {
                foreach ($steps as $step => $path) {
                    if (file_exists($settingsfile = $path . '/settings.php')) {
                        include($settingsfile);
                    }
                }
            }
        }
    } else {  // No fulltree, settings detail page.
        $tabrow = tabs::get_tabrow();
        $id = optional_param('id', 'settings', PARAM_TEXT);
        $tabs = [$tabrow];
        $output = print_tabs($tabs, $id, null, null, true);

        // Main config page.
        $settings->add(new admin_setting_heading('lifecycle_settings_heading',
            $output,  html_writer::span(get_string('general_settings_header', 'tool_lifecycle'), 'h3')));
        $settings->add(new admin_setting_configduration('tool_lifecycle/duration',
            get_string('config_delay_duration', 'tool_lifecycle'),
            get_string('config_delay_duration_desc', 'tool_lifecycle'),
            183 * 24 * 60 * 60)); // Dafault value is 180 days.
        $settings->add(new admin_setting_configdirectory('tool_lifecycle/backup_path',
            get_string('config_backup_path', 'tool_lifecycle'),
            get_string('config_backup_path_desc', 'tool_lifecycle'),
            $CFG->dataroot . DIRECTORY_SEPARATOR . 'lifecycle_backups'));
        $settings->add(new admin_setting_configcheckbox('tool_lifecycle/showcoursecounts',
            get_string('config_showcoursecounts', 'tool_lifecycle'),
            get_string('config_showcoursecounts_desc', 'tool_lifecycle'),
            1));

        if ($triggers) {
            $settings->add(new admin_setting_heading('lifecycletriggerheader',
                get_string('triggers_installed', 'tool_lifecycle'), ''));
            foreach ($triggers as $trigger => $path) {
                $settings->add(new admin_setting_description('lifecycletriggersetting_'.$trigger,
                    get_string('pluginname', 'lifecycletrigger_' . $trigger),
                    get_string('plugindescription', 'lifecycletrigger_' . $trigger)));
            }
        } else {
            $settings->add(new admin_setting_heading('adminsettings_notriggers',
                get_string('adminsettings_notriggers', 'tool_lifecycle'), ''));
        }

        if ($steps) {
            $settings->add(new admin_setting_heading('lifecyclestepheader',
                get_string('steps_installed', 'tool_lifecycle'), ''));
            foreach ($steps as $step => $path) {
                $settings->add(new admin_setting_description('lifecyclestepsetting_'.$step,
                    get_string('pluginname', 'lifecyclestep_' . $step),
                    get_string('plugindescription', 'lifecyclestep_' . $step)));
            }
        } else {
            $settings->add(new admin_setting_heading('adminsettings_nosteps',
                get_string('adminsettings_nosteps', 'tool_lifecycle'), ''));
        }
        $ADMIN->add('lifecycle', $settings);
    }
}
