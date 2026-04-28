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
 * Admin tool "Course Life Cycle" - Subplugin "Opencast step" - Language pack
 *
 * @package    lifecyclestep_opencast
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachedef_ocworkflows'] = 'Gecachte Opencast-Workflow-Definitionen für den Lifecycle-Opencast-Schritt';
$string['cachedef_processedvideos'] = 'Gecachte Opencast-Videos, deren Verarbeitung im Lifecycle-Opencast-Schritt abgeschlossen ist';
$string['cachedef_seriesvideos'] = 'Gecachte Opencast-Serienvideos für den Lifecycle-Opencast-Schritt';
$string['coursefullnameunknown'] = 'Unbekannter Kursname';
$string['error_removeseriesmapping'] = 'Der Serien-Zuordnungsdatensatz konnte nicht entfernt werden.';
$string['error_removeseriestacl'] = 'Die Kurs-ACLs konnten nicht ordnungsgemäß aus der Serie und ihren Ereignissen entfernt werden.';
$string['errorexception_body'] = 'Beim Opencast-Schrittverarbeitungsprozess für {$a->coursefullname} (ID: {$a->courseid}) mit dem Workflow ($a->ocworkflow) der Opencast-Instanz (ID: {$a->ocinstanceid}) ist ein schwerwiegender Fehler aufgetreten.';
$string['errorexception_subj'] = 'Kurs-Lebenszyklus – Opencast-Schritt: Schwerwiegender Fehler';
$string['errorfailedworkflow_body'] = 'Der Workflow ({$a->ocworkflow}) der Opencast-Instanz (ID: {$a->ocinstanceid}) konnte für das Ereignis „{$a->videotitle}" (ID: {$a->videoidentifier}) im Kurs {$a->coursefullname} (ID: {$a->courseid}) nicht gestartet werden.';
$string['errorfailedworkflow_subj'] = 'Kurs-Lebenszyklus – Opencast-Schritt: Workflow fehlgeschlagen';
$string['errorworkflownotexists_body'] = 'Der Workflow ({$a->ocworkflow}) wurde in der Opencast-Instanz (ID: {$a->ocinstanceid}) im Kurs {$a->coursefullname} (ID: {$a->courseid}) nicht gefunden.';
$string['errorworkflownotexists_subj'] = 'Kurs-Lebenszyklus – Opencast-Schritt: Workflow nicht gefunden';
$string['interaction_decision_action_alt'] = 'Aktion ausführen';
$string['interaction_form_header'] = 'Opencast-Schritt – Ausstehende Entscheidung';
$string['interaction_form_option_abort'] = 'Abbrechen';
$string['interaction_form_option_accept'] = 'Akzeptieren';
$string['interaction_form_option_confirm'] = 'Bestätigen';
$string['interaction_form_option_pending'] = 'Ausstehend';
$string['interaction_form_select_decision'] = 'Ihre Entscheidung';
$string['interaction_form_select_decision_help'] = 'Folgende Entscheidungen stehen zur Auswahl:<br>Akzeptieren: Sie stimmen zu und möchten, dass der Schritt fortgesetzt wird.<br>Abbrechen: Die Verarbeitung wird abgebrochen.<br>Ausstehend: Der Prozess bleibt im Wartezustand, damit Sie ihn später erneut prüfen können.';
$string['interaction_give_action_decided'] = '{$a} hat eine Entscheidung für diesen Schritt getroffen.';
$string['interaction_state_info_aborted'] = 'Die Verarbeitung dieses Schritts wurde abgebrochen!';
$string['interaction_state_info_default'] = 'Der Schritt befindet sich derzeit im Wartezustand. Wie möchten Sie fortfahren?';
$string['interaction_state_info_first_spin'] = 'Der Schritt wird die Serien und Videos in diesem Kurs verarbeiten. Möchten Sie fortfahren?';
$string['interaction_state_info_rate_limiter'] = 'Das Durchsatzlimit wurde erreicht. Wie möchten Sie fortfahren?';
$string['interaction_state_info_remove_mapping_error'] = 'Die Kurs-Serien-Zuordnung für die Serie (ID: {$a->sid}) aus dem Kurs (ID: {$a->cid}) konnte nicht entfernt werden. Wie möchten Sie fortfahren?';
$string['interaction_state_info_unlink_series_error'] = 'Beim Entfernen der ACLs für die Serie (ID: {$a}) ist ein Problem aufgetreten. Wie möchten Sie fortfahren?';
$string['interaction_state_info_workflow_error'] = 'Beim Starten des Workflows „{$a->wf}" für das Ereignis (ID: {$a->eid}) ist ein Problem aufgetreten. Wie möchten Sie fortfahren?';
$string['interaction_status_message'] = 'Der Opencast-Schritt erfordert Ihre Aufmerksamkeit';
$string['interaction_status_message_aborted'] = 'Die Verarbeitung des Opencast-Schritts wurde abgebrochen';
$string['interaction_status_message_completed'] = 'Dieser Schritt ist abgeschlossen.';
$string['interaction_status_message_confirm'] = 'Der Opencast-Schritt wird verarbeitet';
$string['interaction_status_message_processing'] = 'Der Opencast-Schritt wird verarbeitet';
$string['mform_dryrun'] = 'Testlauf aktivieren';
$string['mform_dryrun_help'] = 'Wenn diese Option aktiviert ist, verarbeitet der Schritt die Opencast-Serien und -Ereignisse und gibt lediglich aus, was ausgeführt werden würde – ohne tatsächlich einen Workflow auf die Ereignisse anzuwenden.';
$string['mform_dryrun_info'] = 'Für diesen Opencast-Schritt ist der Testlaufmodus aktiviert. Es werden keine Änderungen vorgenommen. Diese Ausführung simuliert den Prozess lediglich und zeigt, was bei einem echten Durchlauf geschehen würde.';
$string['mform_generalsettingsheading'] = 'Allgemeine Einstellungen';
$string['mform_ocinstanceheading'] = 'Opencast-Instanz: {$a->name}';
$string['mform_ocisdelete'] = 'Löschvorgang aktivieren';
$string['mform_ocisdelete_help'] = 'Wenn aktiviert, werden alle zugehörigen Verfahren zum Löschen von Serien und Videos verarbeitet und angewendet.';
$string['mform_ocnotifyadmin'] = 'Admin-Benachrichtigung aktivieren';
$string['mform_ocnotifyadmin_help'] = 'Wenn aktiviert, werden Administratoren benachrichtigt, falls etwas nicht wie erwartet funktioniert, z.B. bei Fehlern oder Ausfällen.';
$string['mform_ocremoveseriesmapping'] = 'Serienzuordnung beim Löschen entfernen';
$string['mform_ocremoveseriesmapping_help'] = 'Wenn aktiviert und der Schritt zum Löschen von Videos im Kurs dient, wird die Kurs-Serien-Zuordnung ebenfalls entfernt, sofern alle Serienvideos gelöscht oder die Serie getrennt wurde.';
$string['mform_octrace'] = 'Ablaufverfolgung aktivieren';
$string['mform_octrace_help'] = 'Wenn aktiviert, werden zusätzliche detaillierte Protokolle erstellt.<br>Hinweis: Diese Einstellung wird ignoriert, wenn der Testlaufmodus aktiviert ist.';
$string['mform_ocworkflow'] = 'Opencast-Workflow';
$string['mform_ocworkflow_help'] = 'Der Opencast-Workflow, der ausgelöst wird, wenn während des Schrittverarbeitungsprozesses ein Ereignis einer Serie gefunden wird. Ist kein Workflow festgelegt, wird die Opencast-Instanz nicht verarbeitet und übersprungen.<br>HINWEIS: Neue Opencast-Workflows, die auf Tags basieren, werden erst nach dem Speichern der Änderungen an den Opencast-Workflow-Tags abgerufen.';
$string['mform_ratelimiter'] = 'Opencast-Durchsatzbegrenzung';
$string['mform_ratelimiter_help'] = 'Diese Option bewirkt, dass der Schritt pro Opencast-Ereignis nur einmal ausgeführt wird. Wenn diese Option deaktiviert ist, werden alle Ereignisse einer Serie in einem Durchgang verarbeitet.';
$string['mform_workflowtags'] = 'Opencast-Workflow-Tags';
$string['mform_workflowtags_help'] = 'Eine kommagetrennte Liste von Workflow-Tags, mit denen die zugehörigen Workflows aus Opencast abgerufen werden. Diese können dann für jeden Schritt ausgewählt werden, der auf vorhandene Ereignisse angewendet wird.<br>Aufgrund von Einschränkungen bei der Verarbeitung von Schritteinstellungen werden Änderungen an diesem Feld erst nach dem Speichern wirksam.<br>HINWEIS: Wenn das Feld leer ist, wird der Tag „delete" verwendet.';
$string['mtrace_error_cannot_remove_acl'] = 'FEHLER: Die Kurs-ACLs konnten nicht ordnungsgemäß aus der Serie und ihren Ereignissen entfernt werden.';
$string['mtrace_error_get_series_videos'] = 'FEHLER: Beim Abrufen der Serienvideos ist ein Fehler aufgetreten. Die Serie wird übersprungen.';
$string['mtrace_error_remove_series_mapping'] = 'FEHLER: Die Serienzuordnung konnte nicht entfernt werden.';
$string['mtrace_error_workflow_cannot_start'] = 'FEHLER: Der Workflow konnte für dieses Video nicht ordnungsgemäß gestartet werden.';
$string['mtrace_error_workflow_notexist'] = 'FEHLER: Der Workflow ({$a->ocworkflow}) existiert nicht.';
$string['mtrace_finish_process_course'] = 'Verarbeitung der Videos im Kurs (ID: {$a->courseid}) abgeschlossen';
$string['mtrace_finish_process_deletion'] = 'Löschvorgang für Serien und Videos abgeschlossen.';
$string['mtrace_finish_process_ocinstance'] = 'Verarbeitung der Videos in der Opencast-Instanz (ID: {$a->instanceid}) abgeschlossen';
$string['mtrace_finish_process_regular'] = 'Reguläre Verarbeitung für Serien und Videos abgeschlossen.';
$string['mtrace_finish_process_series'] = 'Verarbeitung der Videos in der Opencast-Serie (ID: {$a->series}) abgeschlossen';
$string['mtrace_finish_process_unlinking_series_course'] = 'Trennung der Opencast-Serie (ID: {$a->series}) vom Kurs abgeschlossen.';
$string['mtrace_notice_no_remove_mapping'] = 'HINWEIS: Da noch nicht verarbeitete Videos in der Serie (ID: {$a->series}) vorhanden waren, wurde die Serienzuordnung nicht entfernt!';
$string['mtrace_notice_rate_limiter'] = 'HINWEIS: Da die Opencast-Durchsatzbegrenzung in den Schritteinstellungen aktiviert ist, wird die Videoverarbeitung für diesen Kurs jetzt gestoppt und beim nächsten Durchlauf dieser geplanten Aufgabe fortgesetzt.';
$string['mtrace_notice_video_is_processing'] = 'HINWEIS: Das Video wird derzeit bereits verarbeitet und wird daher übersprungen.';
$string['mtrace_start_process_course'] = 'Starte Verarbeitung der Videos im Kurs „{$a->coursefullname}" (ID: {$a->courseid})';
$string['mtrace_start_process_deletion'] = 'Starte Löschvorgang für Serien und Videos.';
$string['mtrace_start_process_ocinstance'] = 'Starte Verarbeitung der Videos in der Opencast-Instanz (ID: {$a->instanceid})';
$string['mtrace_start_process_regular'] = 'Starte reguläre Verarbeitung für Serien und Videos.';
$string['mtrace_start_process_series'] = 'Starte Verarbeitung der Videos in der Opencast-Serie (ID: {$a->series})';
$string['mtrace_start_process_video'] = 'Starte Verarbeitung des Opencast-Videos (ID: {$a->identifier})';
$string['mtrace_start_process_with_dryrun'] = '[INFO] Die Ausführung erfolgt im Testlaufmodus. Alle Vorgänge werden nur simuliert und dienen ausschließlich der Vorschau.';
$string['mtrace_success_delete_workflow_started'] = 'ERFOLG: Der Workflow wurde für dieses Video gestartet. Der Löschvorgang ist in den Opencast-Löschaufträgen des Cron registriert.';
$string['mtrace_success_series_course_unlinked'] = 'ERFOLG: Die Serie wurde vom Kurs getrennt.';
$string['mtrace_success_workflow_started'] = 'ERFOLG: Der Workflow wurde für dieses Video gestartet.';
$string['notifycourseprocessed_body'] = 'Der Kurs „{$a->coursefullname}" (ID: {$a->courseid}) wurde erfolgreich mit dem Workflow ({$a->ocworkflow}) verarbeitet.';
$string['notifycourseprocessed_subj'] = 'Kurs-Lebenszyklus – Opencast-Schritt: Kurs erfolgreich verarbeitet';
$string['opencaststep_cleanup_task'] = 'Löscht veraltete Einträge aus den Prozessstatusdatensätzen.';
$string['plugindescription'] = 'Verwaltet, was mit Opencast-Videos geschehen soll, wenn die Bedingungen des Schritts erfüllt sind.';
$string['pluginname'] = 'Opencast-Schritt';
$string['privacy:metadata'] = 'Das Subplugin „Opencast-Schritt" des Admin-Tools „Kurs-Lebenszyklus" speichert keine personenbezogenen Daten.';
$string['report_body'] = 'Bericht: <br><br> {$a}';
$string['report_subj'] = 'Opencast-Schritt – Verarbeitungsbericht';
