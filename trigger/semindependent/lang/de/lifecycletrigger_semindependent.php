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
 * Lang strings for semester independent trigger
 *
 * @package lifecycletrigger_semindependent
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2019 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['exclude'] = 'Exkludiere semesterunabhängige Kurse';
$string['exclude_help'] = 'Ist diese Option aktiviert werden semesterunabhängige Kurse ausgeschlossen, wenn nicht werden sie inkludiert.';
$string['nosemester'] = 'Kurse ohne Semesterzuordnung';
$string['nosemester_help'] = 'Standardmäßig werden Kurse getriggert, die kein gültiges Startdatum eingetragen haben. Wenn Sie stattdessen
Kurse auswählen möchten, die keine Semesterzuordnung aufweisen, aktivieren Sie diese Option.';
$string['plugindescription'] = 'Inkludiert oder exkludiert Kurse ohne Startdatum.';
$string['pluginname'] = 'Semesterunabhängige Kurse Trigger';
$string['privacy:metadata'] = 'Speichert keine Userdaten';
$string['setting_customfield'] = 'Kursfeld';
$string['setting_customfield_help'] = 'Wähle das Kursfeld, das diesen Trigger auslöst, wenn es nicht befüllt ist.';
$string['setting_customfield_nofield'] = 'Es wurde kein Kursfeld vom Typ Semester gefunden. Erstellen Sie ein solches <a href="{$a}">hier</a>.';
