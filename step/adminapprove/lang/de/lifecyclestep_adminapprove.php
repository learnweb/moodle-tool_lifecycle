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
 * Lang Strings for Admin Approve Step
 *
 * @package lifecyclestep_adminapprove
 * @subpackage adminapprove
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adminapprovals'] = 'Admin-Bestätigungen';
$string['all'] = 'Alle';
$string['amount_courses'] = 'Anzahl wartender Kurse';
$string['bulkactions'] = 'Massenaktionen';
$string['courseid'] = 'Kurs-ID';
$string['courses_waiting'] = 'Diese Kurse warten derzeit auf Bestätigung im "{$a->step}"-Schritt in dem "{$a->workflow}"-Workflow.';
$string['emailcontent'] = 'Es gibt {$a->amount} neue Kurse, die auf Bestätigung warten. Bitte besuchen Sie {$a->url}.';
$string['emailcontenthtml'] = 'Es gibt {$a->amount} neue Kurse, die auf Bestätigung warten. Bitte klicken Sie auf <a href="{$a->url}">diesen Link</a>.';
$string['emailsubject'] = 'Kurs-Lebenszyklus: Es gibt neue Kurse, die auf Bestätigung warten.';
$string['manage-adminapprove'] = 'Adminbestätigungs-Schritte verwalten';
$string['no_courses_waiting'] = 'Es gibt derzeit keine Kurse, die im "{$a->step}"-Schritt in dem "{$a->workflow}"-Workflow auf Bestätigung warten.';
$string['nostepstodisplay'] = 'Es gibt derzeit keine Kurse in Adminbestätigungs-Schritten, die auf Bestätigung warten.';
$string['nothingtodisplay'] = 'Es gibt keine auf Bestätigung wartenden Kurse, die auf diese Filter passen.';
$string['only_number'] = 'Es sind nur Ziffern erlaubt!';
$string['plugindescription'] = 'In diesem Schritt wird die Bestätigung eines Systemadministrators eingeholt bevor die Ausführung des Workflows fortgesetzt werden kann.';
$string['pluginname'] = 'Adminbestätigungs-Schritt';
$string['proceed'] = 'Fortführen';
$string['proceedall'] = 'Alle fortführen';
$string['proceedbutton'] = 'Beschriftung des Fortsetzen-Buttons';
$string['proceedbutton_help'] = 'ändert die Beschriftung des Fortsetzen-Buttons. Wenn das Feld leer bleibt, wird die Standardbeschriftung beibehalten.';
$string['proceedselected'] = 'Ausgewählte fortführen';
$string['rollback'] = 'Zurücksetzten';
$string['rollbackall'] = 'Alle zurücksetzten';
$string['rollbackbutton'] = 'Beschriftung des Zurücksetzen-Buttons';
$string['rollbackbutton_help'] = 'ändert die Beschriftung des Zurücksetzen-Buttons. Wenn das Feld leer bleibt, wird die Standardbeschriftung beibehalten';
$string['rollbackselected'] = 'Ausgewählte zurücksetzten';
$string['selected'] = 'Ausgewählte';
$string['statusmessage'] = 'Statusnachricht';
$string['statusmessage_help'] = 'Statusnachricht, welche dem Lehrer angezeigt wird, wenn ein Prozess eines Kurses den Adminbestätigungs-Schritt bearbeitet.';
$string['tools'] = 'Aktionen';
$string['workflow'] = 'Workflow';
