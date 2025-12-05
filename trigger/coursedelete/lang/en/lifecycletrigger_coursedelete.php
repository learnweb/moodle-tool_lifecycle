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
 * @copyright  2025 Gifty Wanzola (ccaewan)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 // English strings for the Course deletion trigger.


 // Short name shown in the trigger dropdown.

 $string['pluginname'] = 'Select previously archived courses to delete';


 //Description shown on the workflow configuration page.

 $string['plugindescription'] =
     'Selects courses that have already been archived/frozen and have remained in that state '
     . 'for longer than the configured retention period. These courses can then be removed by '
     . 'a delete step in the workflow.';


 //Frozen duration setting.

 $string['frozendelay'] = 'Retention period for archived courses';
 $string['frozendelay_help'] =
     'The minimum time an archived (frozen) course should be kept before it is eligible for deletion. '
     . 'For example, set this to 3 years to match the standard UCL retention period.';

 //Privacy
 $string['privacy:metadata'] =
     'The Course deletion trigger does not store any personal data.';