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
 * @package lifecyclestep_email
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action_prevented_deletion'] = '{$a} verhinderte Löschung';
$string['email:preventdeletion'] = 'Löschen verhindern';
$string['email_content'] = 'Vorlage für Emails in Klartext';
$string['email_content_help'] = 'Stellen Sie die Vorlage für Emails ein. (in Klartext, alternativ können Sie auch die HTML-Vorlage unten einstellen.)' . '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
        . '<br>' . 'Vorname des Empfängers: ##firstname##'
        . '<br>' . 'Nachname des Empfängers: ##lastname##'
        . '<br>' . 'Link zur Antwortseite: ##link##'
        . '<br>' . 'Betroffene Kurse: ##courses##'
        . '</p>';
$string['email_content_html'] = 'HTML-Vorlage für Emails';
$string['email_content_html_help'] = 'Stellen sie die HTML-Vorlage für Emails ein. (in HTML-Format; falls gesetzt, wird es an Stelle der Klartext-Vorlage benutzt!)' . '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
        . '<br>' . 'Vorname des Empfängers: ##firstname##'
        . '<br>' . 'Nachname des Empfängers: ##lastname##'
        . '<br>' . 'Link zur Antwortseite: ##link-html##'
        . '<br>' . 'Betroffene Kurse: ##courses-html##'
        . '</p>';
$string['email_responsetimeout'] = 'Zeit, die der Nutzer hat, um zu reagieren';
$string['email_subject'] = 'Betreffvorlage';
$string['email_subject_help'] = 'Stellen Sie die Vorlage für den Emailbetreff ein.' . '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
        . '<br>' . 'Vorname des Empfängers: ##firstname##'
        . '<br>' . 'Nachname des Empfängers: ##lastname##'
        . '<br>' . 'Link zur Antwortseite: ##link##'
        . '<br>' . 'Betroffene Kurse: ##courses##'
        . '</p>';
$string['keep_course'] = 'Kurs behalten';
$string['pluginname'] = 'Email-Schritt';
$string['privacy:metadata:lifecyclestep_email:courseid'] = 'Die ID des Kurses, zu dem E-Mail-Benachrichtigungen versandt werden';
$string['privacy:metadata:lifecyclestep_email:instanceid'] = 'Die ID der Schritt-Instanz, der E-Mails verschickt';
$string['privacy:metadata:lifecyclestep_email:summary'] = 'Informationen, welche Benutzer per E-Mail benachrichtigt werden';
$string['privacy:metadata:lifecyclestep_email:touser'] = 'Die ID des Benutzers, an den eine E-Mail verschickt wird';
$string['status_message_requiresattention'] = 'Kurs ist zum Löschen vorgemerkt';
