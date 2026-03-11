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
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Language pack
 *
 * @package    lifecycletrigger_customfieldsemester
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['error_delaypositive'] = 'The amount of months must be a positive integer value between 1 and 999.';
$string['error_missingfield'] = 'The configured custom field \'{$a->missingfield}\' is missing.';
$string['plugindescription'] = 'Trigger courses by the course custom field \'semester\'';
$string['pluginname'] = 'Customfield semester trigger';
$string['privacy:metadata'] = 'The "Customfield Semester Trigger" subplugin of the admin tool "Course Life Cycle" does not store any personal data.';
$string['setting_customfield'] = 'Custom field';
$string['setting_customfield_help'] = "With this setting, you define the custom field which holds the term of a course. The value of this field will be the basis of this trigger.";
$string['setting_customfield_nofield'] = 'There isn\'t any custom course field which could be used by this trigger. Please create a custom course field of type "Semester" on the <a href="{$a}">custom course fields administration page</a> first.';
$string['setting_delay'] = 'Trigger x months after term start';
$string['setting_delay_help'] = "With this setting, you define the delay until a process is started.\n\nThe trigger will take the term of a course, get the start month of the term, add the configured amount of months as delay and check if this delay period has already passed. If yes, the trigger will be invoked.\n\nCourses which have the \'term-independent\' value in the custom course field will never be triggered.";
