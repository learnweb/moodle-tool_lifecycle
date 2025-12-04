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

 $string['pluginname'] = 'Course freeze trigger';
 $string['plugindescription'] = 'Triggers when last course access and creation date exceed configured thresholds.';
 
 $string['lastaccessdelay'] = 'Trigger when last access is older than';
 $string['lastaccessdelay_help'] = 'The course will be selected if the most recent student access is older than this duration.';
 
 $string['creationdelay'] = 'AND creation date is older than';
 $string['creationdelay_help'] = 'The course must also be older than this creation date threshold.';
 
 $string['privacy:metadata'] = 'The course freeze trigger does not store personal data.';

