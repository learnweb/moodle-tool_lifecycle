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
 * Lang strings for email step
 *
 * @package tool_lifecycle_step
 * @subpackage email
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Email-Schritt';

$string['email_responsetimeout'] = 'Zeit, die der Nutzer hat, um zu reagieren';
$string['email_subject'] = 'Betreffvorlage';
$emailplaceholdersnohtml = '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
    . '<br>' . 'Vorname des Empfängers: ##firstname##'
    . '<br>' . 'Nachname des Empfängers: ##lastname##'
    . '<br>' . 'Link zur Antwortseite: ##link##'
    . '<br>' . 'Betroffene Kurse: ##courses##'
    . '</p>';
$string['email_subject_help'] = 'Stellen Sie die Vorlage für den Emailbetreff ein.' . $emailplaceholdersnohtml;
$string['email_content'] = 'Vorlage für Emails in Klartext';
$string['email_content_help'] = 'Stellen Sie die Vorlage für Emails ein. (in Klartext, alternativ können Sie auch die HTML-Vorlage unten einstellen.)' . $emailplaceholdersnohtml;
$emailplaceholdershtml = '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
        . '<br>' . 'Vorname des Empfängers: ##firstname##'
        . '<br>' . 'Nachname des Empfängers: ##lastname##'
        . '<br>' . 'Link zur Antwortseite: ##link-html##'
        . '<br>' . 'Betroffene Kurse: ##courses-html##'
        . '</p>';
$string['email_content_html'] = 'HTML-Vorlage für Emails';
$string['email_content_html_help'] = 'Stellen sie die HTML-Vorlage für Emails ein. (in HTML-Format; falls gesetzt, wird es an Stelle der Klartext-Vorlage benutzt!)' . $emailplaceholdershtml;

$string['email:preventdeletion'] = 'Löschen verhindern';

$string['keep_course'] = 'Kurs behalten';
$string['status_message_requiresattention'] = 'Kurs ist zum Löschen vorgemerkt';
$string['action_prevented_deletion'] = '{$a} verhinderte Löschung';
