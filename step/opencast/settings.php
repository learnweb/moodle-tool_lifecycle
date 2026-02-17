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
 * Admin tool "Course Life Cycle" - Subplugin "Opencast step" - Settings
 *
 * @package    lifecyclestep_opencast
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Require the necessary libraries.
require_once(__DIR__ . '/lib.php');

if ($ADMIN->fulltree) {
        // Prepare options array for select settings.
        $yesnooption = [LIFECYCLESTEP_OPENCAST_SELECT_YES => get_string('yes'),
                LIFECYCLESTEP_OPENCAST_SELECT_NO => get_string('no')];

        // Create workflow tag setting.
        $settings->add(new admin_setting_configtext(
            'lifecyclestep_opencast/workflowtags',
            get_string('setting_workflowtags', 'lifecyclestep_opencast'),
            get_string('setting_workflowtags_desc', 'lifecyclestep_opencast'),
            'delete'
        ));

        // Create rate limiter setting.
        $settings->add(new admin_setting_configselect(
            'lifecyclestep_opencast/ratelimiter',
            get_string('setting_ratelimiter', 'lifecyclestep_opencast'),
            get_string('setting_ratelimiter_desc', 'lifecyclestep_opencast'),
            LIFECYCLESTEP_OPENCAST_SELECT_NO,
            $yesnooption
        ));
}
