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
 * @package lifecycletrigger_coursedelete
 * @copyright  2025 Gifty (ccaewan)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Select long-term archived courses for deletion';

// Description shown on the workflow configuration page.
$string['plugindescription'] =
    'Selects courses that have been archived (frozen), have not been accessed for a prolonged period, '
    . 'and were created sufficiently long ago. These courses are considered end-of-life and may be '
    . 'safely removed using a delete step in the workflow.';

$string['inactivitydelay'] = 'Inactivity threshold';
$string['inactivitydelay_help'] =
    'The minimum period since a course was last accessed by enrolled users. '
    . 'Only courses with no activity within this time window will be eligible for deletion.';

$string['creationdelay'] = 'Minimum course age';
$string['creationdelay_help'] =
    'The minimum age of a course based on its creation date. '
    . 'Courses created more recently than this threshold will not be selected for deletion.';

$string['privacy:metadata'] =
    'The Course deletion trigger does not store or process personal data.';
