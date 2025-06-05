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
 * Lang strings for site course trigger
 *
 * @package lifecycletrigger_byrole
 * @copyright  2017 Tobias Reischmann WWU Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['delay'] = 'Days of delay for triggering';
$string['delay_help'] = 'Days a course has to remain without the mandatory role until the course is finally triggered';
$string['invert'] = 'Invert role selection for triggering';
$string['invert_help'] = 'If ticked, any of the selected roles have to be present for a course to be triggered.';
$string['plugindescription'] = 'Triggers if a specified role is misssing in a course for a certain timespan.';
$string['pluginname'] = 'Trigger courses by roles missing';
$string['privacy:metadata'] = 'Does not store user specific data';
$string['responsibleroles'] = 'Responsible Roles in courses';
$string['responsibleroles_help'] = 'Select the roles that have to be present in the course. If one of these roles is present the course is not triggered.';
