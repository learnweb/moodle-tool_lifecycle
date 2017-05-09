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
 * Settings page which gives an overview over running deprovision processes.
 *
 * @package local
 * @subpackage course_deprovision
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    require_once(__DIR__ . '/adminlib.php');

    $ADMIN->add('localplugins', new local_course_deprovision\admin_page_active_processes());
    $settings = new admin_settingpage('local_course_deprovision',
        get_string('general_config_header', 'local_course_deprovision'));
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $triggers = core_component::get_plugin_list('coursedeprovisiontrigger');
        foreach ($triggers as $trigger => $path) {
            if (file_exists($settingsfile = $path . '/settings.php')) {
                $settings->add(new admin_setting_heading('coursedeprovisiontriggersetting'.$trigger,
                    get_string('trigger', 'local_course_deprovision') .
                    ' - ' . get_string('pluginname', 'coursedeprovisiontrigger_' . $trigger), ''));
                include($settingsfile);
            }
        }
    }
}