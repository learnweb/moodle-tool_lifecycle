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

    $settings = new admin_settingpage('lifecycle', get_string('pluginname', 'tool_lifecycle'));

    $tabrow = tabs::get_tabrow();
    $tabs = [$tabrow];
    $tabsoutput = print_tabs($tabs, 'settings', null, null, true);

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

    $admins = get_admins();
    $candidates = [];
    $candidates[0] = get_string('none');
    foreach ($admins as $admin) {
        $candidates[$admin->id] = $admin->firstname.' '.$admin->lastname.' ('.$admin->email.')';
    }
    $settings->add(new admin_setting_configmultiselect('tool_lifecycle/adminapproveuserstonotify',
        get_string('config_adminapproveuserstonotify', 'tool_lifecycle'),
        get_string('config_adminapproveuserstonotify_desc', 'tool_lifecycle'),
        [2], $candidates));

    $settings->add(new admin_setting_configtext('tool_lifecycle/deletebackupsafterdays',
        get_string('config_deletebackupsafterdays', 'tool_lifecycle'),
        get_string('config_deletebackupsafterdays_desc', 'tool_lifecycle'),
        365, PARAM_INT));

    $settings->add(new admin_setting_configtext('tool_lifecycle/forum',
        get_string('config_forum', 'tool_lifecycle'),
        get_string('config_forum_desc', 'tool_lifecycle'),
        null, PARAM_INT));

    $ADMIN->add('tools', $settings);
}
