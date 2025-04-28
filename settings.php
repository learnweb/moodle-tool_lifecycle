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
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_lifecycle\tabs;

// Check for the moodle/site:config permission.
if ($hassiteconfig) {

    $settings = new admin_settingpage('lifecycle',
        get_string('pluginname', 'tool_lifecycle').' / '.
        get_string('general_settings_header', 'tool_lifecycle'));

    $tabrow = tabs::get_tabrow();
    $id = optional_param('id', 'settings', PARAM_TEXT);
    $tabs = [$tabrow];
    $tabsoutput = print_tabs($tabs, $id, null, null, true);

    // Main config page.
    $settings->add(new admin_setting_heading('lifecycle_settings_heading',
        '', $tabsoutput));
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
    $settingenablehierachy = new admin_setting_configcheckbox('tool_lifecycle/enablecategoryhierachy',
        get_string('config_enablecategoryhierachy', 'tool_lifecycle'),
        get_string('config_enablecategoryhierachy_desc', 'tool_lifecycle'),
        false);
    $settings->add($settingenablehierachy);
    $coursehierachysetting = new admin_setting_configtext('tool_lifecycle/coursecategorydepth',
        get_string('config_coursecategorydepth', 'tool_lifecycle'),
        get_string('config_coursecategorydepth_desc', 'tool_lifecycle'),
        0, PARAM_INT);
    $coursehierachysetting->add_dependent_on('tool_lifecycle/enablecategoryhierachy');
    $settings->add($coursehierachysetting);
    $settings->hide_if('tool_lifecycle/coursecategorydepth', 'tool_lifecycle/enablecategoryhierachy', 'notchecked');
    $settings->add(new admin_setting_configcheckbox('tool_lifecycle/logreceivedmails',
        get_string('config_logreceivedmails', 'tool_lifecycle'),
        get_string('config_logreceivedmails_desc', 'tool_lifecycle'),
        0));

    $triggers = core_component::get_plugin_list('lifecycletrigger');
    if ($triggers) {
        $settings->add(new admin_setting_heading('lifecycletriggerheader',
            get_string('triggers_installed', 'tool_lifecycle'), ''));
        foreach ($triggers as $trigger => $path) {
            $triggername = html_writer::span(get_string('pluginname', 'lifecycletrigger_' . $trigger),
                "font-weight-bold");
            $uninstall = '';
            if ($trigger == 'sitecourse' || $trigger == 'delayedcourses') {
                $uninstall = html_writer::span(' Depracated. Will be removed with version 5.0.', 'text-danger');
            }
            if ($trigger == 'customfieldsemester') {
                $settings->add(new admin_setting_description('lifecycletriggersetting_'.$trigger,
                    $triggername,
                    get_string('customfieldsemesterdescription', 'tool_lifecycle')));
            } else {
                $settings->add(new admin_setting_description('lifecycletriggersetting_'.$trigger,
                    $triggername,
                    get_string('plugindescription', 'lifecycletrigger_' . $trigger).$uninstall));
            }
        }
    } else {
        $settings->add(new admin_setting_heading('adminsettings_notriggers',
            get_string('adminsettings_notriggers', 'tool_lifecycle'), ''));
    }

    $steps = core_component::get_plugin_list('lifecyclestep');
    if ($steps) {
        $settings->add(new admin_setting_heading('lifecyclestepheader',
            get_string('steps_installed', 'tool_lifecycle'), ''));
        foreach ($steps as $step => $path) {
            $stepname = html_writer::span(get_string('pluginname', 'lifecyclestep_' . $step),
                "font-weight-bold");
            $settings->add(new admin_setting_description('lifecyclestepsetting_'.$step,
                $stepname,
                get_string('plugindescription', 'lifecyclestep_' . $step)));
        }
    } else {
        $settings->add(new admin_setting_heading('adminsettings_nosteps',
            get_string('adminsettings_nosteps', 'tool_lifecycle'), ''));
    }
    $settings->add(new admin_setting_description('spacer', "", "&nbsp;"));

    $ADMIN->add('tools', $settings);
}
