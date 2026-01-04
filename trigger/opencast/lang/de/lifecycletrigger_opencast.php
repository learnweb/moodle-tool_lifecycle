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
 * Lang strings for opencast trigger
 *
 * @package     lifecycletrigger_opencast
 * @copyright   2025 Thomas Niedermaier University Münster
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activity'] = 'Aktivität';
$string['activity_help'] = 'Hier geben Sie an, ob Kurse getriggert werden sollen, die mindestens eine Episode oder Serie enthalten, die über die Opencast-Aktivität eingebunden ist.';
$string['exclude'] = 'Ausschließen';
$string['exclude_help'] = 'Falls ausgewählt, werden Kurse mit den definierten Opencast-Videos NICHT ausgelöst.';
$string['lti'] = 'LTI';
$string['ltitools'] = 'LTI-Tools';
$string['ltitools_help'] = 'Wählen Sie die LTI-Tools, welche die Episoden und/oder Serien aus Opencast zur Verfügung stellen.';
$string['lti_help'] = 'Inkludiere Kurse, die mindestens eine Opencast Episode und/oder Serie via LTI-Tool eingebunden haben.';
$string['lti_do_not_exist'] = 'Es gibt keine LTI-Tools mit den folgenden IDs: {$a}.';
$string['lti_noselection'] = 'Bitte wählen Sie mindestens einen LTI-Typ aus.';
$string['plugindescription'] = 'Selektiert alle Kurse mit mindestens einer Opencast Video-Einbindung.';
$string['pluginname'] = 'Opencast-Trigger';
$string['privacy:metadata'] = 'Dieses Subplugin speichert keine persönlichen Daten.';
