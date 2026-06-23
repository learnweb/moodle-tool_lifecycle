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
 * Lang strings for course freeze trigger
 *
 * @package lifecycletrigger_coursefreeze
 * @copyright  2025 Gifty (ccaewan)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Short name shown in the trigger dropdown
$string['pluginname'] = 'Select courses to archive';


// Description shown on the workflow config page

$string['plugindescription'] =
    'Selects courses for archiving based on how long ago they were last accessed '
    . 'and when they were created. Only courses that are older than both thresholds '
    . 'will be passed to the next step.';


// Last access threshold setting

$string['lastaccessdelay'] = 'Time since last access';
$string['lastaccessdelay_help'] =
    'Only include courses where the most recent user activity is older than this period. '
    . 'For example, set this to 12 months to target courses with no access for at least 1 year.';


// Creation date setting

$string['creationdelay'] = 'Course age';
$string['creationdelay_help'] =
    'Only include courses that were created earlier than this period. '
    . 'For example, set this to 24 months so that only courses at least 2 years old are selected.';


// Privacy

$string['privacy:metadata'] =
    'The Course freeze trigger does not store any personal data.';