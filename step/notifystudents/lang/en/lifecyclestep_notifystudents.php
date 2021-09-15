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
 * Lang strings for delete course step
 *
 * @package lifecyclestep_notifystudents
 * @copyright  2021 Aaron Koßler WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Notify students step';
$string['subject'] = 'Email Title';
$string['contenthtml'] = 'Email Text';
$string['subject_default'] = 'Courses are being deleted';
$string['contenthtml_default'] = '<p>' . 'Dear Student,'
    . '<br><br>' . 'the following courses are being deleted:'
    . '<br>' . '##courses-html##'
    . '<br>' . 'Please save all necessary material before deletion.'
    . '<br><br>' . 'Best Regards'
    . '<br>' . 'Your Learnweb Team'
    . '</p>';
$emailplaceholders = '<p>' . 'You can use the following placeholders:'
    . '<br>' . 'First name of recipient: ##firstname##'
    . '<br>' . 'Last name of recipient: ##lastname##'
    . '<br>' . 'Impacted courses: ##courses-html##'
    . '</p>';
$string['subject_help'] = $emailplaceholders;
$string['contenthtml_help'] = $emailplaceholders;
