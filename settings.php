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

    $category = new admin_category('lifecycle_category',
        get_string('pluginname', 'tool_lifecycle'));
    $ADMIN->add('tools', $category);
    $settings = new admin_settingpage('tool_lifecycle',
        get_string('general_config_header', 'tool_lifecycle'));
    $ADMIN->add('lifecycle_category', $settings);

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

    $ADMIN->add('lifecycle_category', new admin_externalpage('tool_lifecycle_workflow_drafts',
        get_string('workflow_drafts_header', 'tool_lifecycle'),
        new moodle_url(\tool_lifecycle\urls::WORKFLOW_DRAFTS)));

    $ADMIN->add('lifecycle_category', new admin_externalpage('tool_lifecycle_active_workflows',
        get_string('active_workflows_header', 'tool_lifecycle'),
        new moodle_url(\tool_lifecycle\urls::ACTIVE_WORKFLOWS)));

    $ADMIN->add('lifecycle_category', new admin_externalpage('tool_lifecycle_coursebackups',
        get_string('course_backups_list_header', 'tool_lifecycle'),
        new \moodle_url('/admin/tool/lifecycle/coursebackups.php')));

    $ADMIN->add('lifecycle_category', new admin_externalpage('tool_lifecycle_delayed_courses',
        get_string('delayed_courses_header', 'tool_lifecycle'),
        new moodle_url('/admin/tool/lifecycle/delayedcourses.php')));

    $ADMIN->add('lifecycle_category', new admin_externalpage('tool_lifecycle_process_errors',
        get_string('process_errors_header', 'tool_lifecycle'),
        new moodle_url('/admin/tool/lifecycle/errors.php')));

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

    $steps = core_component::get_plugin_list('lifecyclestep');
    foreach ($steps as $step => $path) {
        if (file_exists($settingsfile = $path . '/settings.php')) {
            include($settingsfile);
        }
    }
}
