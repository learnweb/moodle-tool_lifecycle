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
 * Life cycle language strings.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['abortdisableworkflow'] = 'Workfow deaktivieren (Prozesse werden abgebrochen, eventuell unsicher!)';
$string['abortdisableworkflow_confirm'] = 'Sie sind dabei, den Workflow zu deaktivieren. Alle laufenden Prozesse werden abgebrochen. Sind Sie sicher?';
$string['abortprocesses'] = 'Laufende Prozesse abbrechen (eventuell unsicher!)';
$string['abortprocesses_confirm'] = 'Alle laufenden Prozesse dieses Workflows werden abgebrochen. Sind Sie sicher?';
$string['activateworkflow'] = 'Aktivieren';
$string['active'] = 'Aktiv';
$string['active_automatic_workflows_heading'] = 'Aktive automatische Workflows';
$string['active_manual_workflows_heading'] = 'Aktive manuelle Workflows';
$string['active_workflow_not_changeable'] = 'Die Workflow-Instanz wurde bereits aktiviert. Je nach Schritt-Typ können dessen Einstellungen eventuell noch geändert werden. Änderungen an Trigger-Instanzen wirken sich nicht auf bereits getriggerte Kurse aus.';
$string['active_workflow_not_removeable'] = 'Die Workflow-Instanz ist aktiv. Es ist nicht möglich, sie zu entfernen.';
$string['active_workflows_header'] = 'Aktive';
$string['active_workflows_header_title'] = 'Liste der derzeit aktiven Workflows';
$string['active_workflows_list'] = 'Zeige aktive Workflows';
$string['add_new_step_instance'] = 'Füge neue Schritt-Instanz hinzu...';
$string['add_new_trigger_instance'] = 'Füge neue Trigger-Instanz...';
$string['add_workflow'] = 'Neuen Workflow hinzufügen';
$string['adminapprovals_header'] = 'Sysadmin';
$string['adminapprovals_header_title'] = 'Liste der offenen Sysadmin-Bestätigungen';
$string['adminsettings_edit_step_instance_heading'] = 'Schritt-Instanz für Workflow \'{$a}\'';
$string['adminsettings_edit_trigger_instance_heading'] = 'Trigger-Instanz für Workflow \'{$a}\'';
$string['adminsettings_edit_workflow_definition_heading'] = 'Workflowdefinition';
$string['adminsettings_heading'] = 'Workflow-Einstellungen';
$string['adminsettings_nosteps'] = 'Keine Schritt-Subplugins installiert';
$string['adminsettings_notriggers'] = 'Keine Trigger-Subplugins installiert';
$string['adminsettings_workflow_definition_steps_heading'] = 'Workflowschritte';
$string['all_delays'] = 'Alle Verzögerungen';
$string['anonymous_user'] = 'Anonyme:r Nutzer:in';
$string['apply'] = 'Anwenden';
$string['backupcreated'] = 'Erstellt am';
$string['backupworkflow'] = 'Workflow sichern';
$string['cachedef_application'] = 'Cache für das Kurs-Menü wenn es aktivierte Workflows gibt.';
$string['cachedef_mformdata'] = 'Cached die mform-Daten.';
$string['cannot_trigger_workflow_manually'] = 'Der Workflow konnte nicht manuell ausgelöst werden.';
$string['config_backup_path'] = 'Pfad des Lifecycle Backup Verzeichnisses';
$string['config_backup_path_desc'] = 'Diese Einstellung bestimmt den Ort, an dem die Lifecycle-Kurssicherungen des Kurssicherungs-Steps abgelegt werden.
Es ist der absolute Server-Pfad anzugeben.';
$string['config_coursecategorydepth'] = 'Tiefe der Kurskategorien, die in der Interaktionstabelle angezeigt werden sollen';
$string['config_coursecategorydepth_desc'] = 'Standardmäßig wird die erste Kategorie angezeigt, wenn Lehrkräfte den Status ihrer Kurse in der view.php verwalten. Die Einstellung ermöglicht es, nicht die erste Ebene der Kategorien, sondern Unterkategorien anzuzeigen.';
$string['config_delay_duration'] = 'Standardlänge eines Kursausschlusses';
$string['config_delay_duration_desc'] = 'Diese Einstellung definiert den Standardlänge einer Kursausschlusses in einem Workflow
 falls ein Prozess des Workflows zurückgesetzt oder beendigt wird. Die Länge des Kursausschlusses besagt, wie lange es dauert, bis
 der Kurs wieder vom Workflow bearbeitet wird.';
$string['config_enablecategoryhierachy'] = 'Kurskategorienhierarchie in der Interaktionstabelle spezifizieren';
$string['config_enablecategoryhierachy_desc'] = 'Standardmäßig wird die direkt zugewiesene Kurskategorie angezeigt, wenn Lehrkräfte den Status ihrer Kurse in der view.php verwalten. Die Einstellung ermöglicht es, eine bestimmte Ebene des Kurskategorienbaums anzuzeigen.';
$string['config_logreceivedmails'] = 'Zusätzliches Logging von E-mails zu Nutzern.';
$string['config_logreceivedmails_desc'] = 'Das Schreiben in die Datenbank hat den Vorteil, dass es explizit nachgeguckt werden kann, allerdings verbraucht es Speicher.';
$string['config_showcoursecounts'] = 'Zeige Anzahl der Kurse, die getriggert werden';
$string['config_showcoursecounts_desc'] = 'Die Workflow-Konfigurationsseite zeigt normalerweise die Anzahl an Kursen, die durch
die konfigurierten Trigger getriggert werden, was Performance-Probleme verursachen kann. Bei Performance-Problemen kann dies hiermit
deaktiviert werden';
$string['course_backups_list_header'] = 'Sicherungen';
$string['course_backups_list_header_title'] = 'Liste der lifecycle-Kurssicherungen';
$string['courseid'] = 'Kurs-ID';
$string['coursename'] = 'Kursname';
$string['courses_are_delayed'] = '{$a} Kurse sind verzögert';
$string['courses_are_delayed_total'] = '{$a} Kurse sind insgesamt verzögert';
$string['courses_are_used_total'] = '{$a} Kurse befinden sich schon in einem Prozess';
$string['courses_excluded'] = 'Kurse insgesamt ausgeschlossen: {$a}';
$string['courses_size'] = 'Kurse insgesamt genauer betrachtet: {$a}';
$string['courses_triggered'] = 'Kurse insgesamt getriggered: {$a}';
$string['courses_will_be_excluded'] = '{$a} Kurse werden ausgeschlossen';
$string['courses_will_be_excluded_total'] = '{$a} Kurse werden insgesamt ausgeschlossen';
$string['courses_will_be_triggered'] = '{$a} Kurse werden getriggert';
$string['courses_will_be_triggered_total'] = '{$a} Kurse werden insgesamt getriggert';
$string['coursesdelayed'] = 'Verzögerte Kurse des Workflows \'{$a}\'';
$string['coursesexcluded'] = 'Ausgeschlossene Kurse für Trigger \'{$a}\'';
$string['coursesinstep'] = 'Kurse im Schritt \'{$a}\'';
$string['coursestriggered'] = 'Getriggerte Kurse für Trigger \'{$a}\'';
$string['coursesused'] = 'Kurse schon in einem Prozess für Workflow \'{$a}\'';
$string['create_copy'] = 'Kopie erstellen';
$string['create_step'] = 'Step erstellen';
$string['create_trigger'] = 'Trigger erstellen';
$string['create_workflow_from_existing'] = 'Kopie von bestehendem Workflow erstellen';
$string['date'] = 'Fällligkeitsdatum';
$string['deactivated'] = 'Deaktiviert';
$string['deactivated_workflows_header'] = 'Inaktive';
$string['deactivated_workflows_header_title'] = 'Liste der deaktivierten Workflows';
$string['deactivated_workflows_list'] = 'Zeige deaktivierte Workflows';
$string['deactivated_workflows_list_header'] = 'Deaktivierte Workflows';
$string['delayed_courses_header'] = 'Verzögerungen';
$string['delayed_courses_header_title'] = 'Liste von allen verzögerten Kursen';
$string['delayed_for_workflow_until'] = 'Verzögert für "{$a->name}" bis {$a->date}';
$string['delayed_for_workflows'] = 'Verzögert für {$a} Workflows';
$string['delayed_globally'] = 'Global verzögert bis {$a}';
$string['delayed_globally_and_seperately'] = 'Global verzögert und für {$a} Workflows';
$string['delayed_globally_and_seperately_for_one'] = 'Global verzögert und für 1 Workflow';
$string['delayeduntil'] = 'Verzögert bis';
$string['delays'] = 'Verzögerungen';
$string['delays_for_workflow'] = 'Verzögerungen für "{$a}"';
$string['delete_all_delays'] = 'Alle Verzögerungen löschen';
$string['delete_all_delays_confirmation'] = 'Sie sind dabei, alle Verzögerungen zu löschen. Sind sie sicher?';
$string['delete_delay'] = 'Verzögerung löschen';
$string['deleteworkflow'] = 'Workflow löschen';
$string['deleteworkflow_confirm'] = 'Sie sind dabei, den Workflow zu löschen. Das kann nicht rückgängig gemacht werden. Sind Sie sicher?';
$string['details:finishdelay'] = 'Verzögerung nach Abschluß';
$string['details:finishdelay_help'] = 'Nachdem ein Kurs einen Workflow beendet, wird er für den angegebenen Zeitraum verzögert.';
$string['details:globaldelay_no'] = 'Diese Verzögerungen gelten nur für diesen Workflow.';
$string['details:globaldelay_yes'] = 'Diese Verzögerungen gelten für alle Workflows.';
$string['details:rollbackdelay'] = 'Verzögerung nach Zurücksetzen';
$string['details:rollbackdelay_help'] = 'Nachdem ein Kurs zurückgesetzt wird, wird er für den angegebenen Zeitraum verzögert.';
$string['disableworkflow'] = 'Workflow deaktivieren (Prozesse laufen weiter)';
$string['disableworkflow_confirm'] = 'Sie sind dabei, den Workflow zu deaktivieren. Sind Sie sicher?';
$string['dontshowextendeddetails'] = 'Zeige detaillierte Workflow-Informationen nicht an';
$string['download'] = 'Herunterladen';
$string['draft'] = 'Entwurf';
$string['duplicateworkflow'] = 'Workflow duplizieren';
$string['edit_step'] = 'Step bearbeiten';
$string['edit_trigger'] = 'Trigger bearbeiten';
$string['editworkflow'] = 'Workflow bearbeiten';
$string['error_wrong_trigger_selected'] = 'Sie haben einen nichtmanuellen Trigger ausgewählt.';
$string['errorbackuppath'] = "Ein Fehler ist aufgetreten beim Versuchen das Backup Verzeichnis zu erstellen.
Ihnen fehlen wahrscheinlich die Berechtigung dazu. Bitte überprüfen Sie den Pfad unter
Seitenadministration/Plugins/Dienstprogramme/Kurs-Lebenszyklus/Allgemein & Subplugins.";
$string['errornobackup'] = "Es wurde kein Backup in dem angegebenen Pfad erstellt.";
$string['find_course_list_header'] = 'Kurse finden';
$string['finished'] = 'Beendet';
$string['followedby_none'] = 'Keine';
$string['force_import'] = 'Die Fehler ignorieren und den Workflow trotzdem importieren. Das kann zu unerwünschten Effekten führen.';
$string['forselected'] = 'Für alle ausgewählten Prozesse';
$string['general_config_header'] = 'Allgemein';
$string['general_config_header_title'] = "Allgemeine Einstellungen & Subplugins";
$string['general_settings_header'] = 'Allgemeine Einstellungen';
$string['globally'] = 'Globale Verzögerungen';
$string['globally_until_date'] = 'Global bis {$a}';
$string['includedelayedcourses'] = 'Verzögerte Kurse einschliessen';
$string['includedelayedcourses_help'] = 'Mit dieser Option werden verzögerte Kurse (ob global oder für den Workflow) in die Verarbeitung dieses Workflows eingeschlossen.';
$string['includesitecourse'] = 'Den Site-Kurs einschliessen';
$string['includesitecourse_help'] = 'Mit dieser Option wird der Startkurs (Kurs mit der ID=1, "Home") in die Verarbeitung dieses Workflows eingeschlossen.';
$string['interaction_success'] = 'Aktion erfolgreich gespeichert.';
$string['invalid_workflow'] = 'Workflowkonfiguration noch nicht aktivierbar';
$string['invalid_workflow_cannot_be_activated'] = 'Der Workflow kann noch nicht aktiviert werden, da die Workflowdefinition ungültig ist.';
$string['invalid_workflow_details'] = 'Erstelle mindestens einen Trigger für diesen Workflow.';
$string['lastaction'] = 'Letzte Aktion am';
$string['lastrun'] = 'Letze Durchführung: {$a}';
$string['lifecycle:managecourses'] = 'Darf Kurse in tool_lifecycle verwalten.';
$string['lifecycle_error_notify_task'] = 'Benachrichtigt die Administratoren bei Fehlern in tool_lifecycle-Prozessen.';
$string['lifecycle_task'] = 'Führt den Lifecycle-Prozess aus.';
$string['lifecyclestep'] = 'Schritt';
$string['lifecycletrigger'] = 'Trigger';
$string['managecourses_link'] = 'Kurse verwalten';
$string['manual_trigger_process_existed'] = 'Es existiert bereits ein Workflow für diesen Kurs.';
$string['manual_trigger_success'] = 'Workflow erfolgreich gestartet.';
$string['manualtriggerenvolved'] = 'Ein manueller Trigger ist vorhanden.';
$string['manualtriggerenvolved_help'] = 'Kurse können erst getriggert werden wenn der Workflow manuell gestgartet wurde.';
$string['move_down'] = 'Nach unten bewegen';
$string['move_up'] = 'Nach oben bewegen';
$string['name_until_date'] = '"{$a->name}" bis {$a->date}';
$string['noactiontools'] = 'Keine Aktionen verfügbar';
$string['nocoursestodisplay'] = 'Es gibt derzeit keine Kurse, die Ihre Aufmerksamkeit erfordern!';
$string['nointeractioninterface'] = 'Keine Interaktionsschnittstelle verfügbar!';
$string['noprocesserrors'] = 'Es gibt keine fehlerhaften Prozesse, die behandelt werden müssen!';
$string['noprocessfound'] = 'Es konnte kein Prozess mit der gegebenen Prozessid gefunden werden!';
$string['noremainingcoursestodisplay'] = 'Es gibt derzeit keine verbleibenden Kurse!';
$string['nostepfound'] = 'Es konnte kein Schritt mit der gegeben Schrittid gefunden werden!';
$string['notifyerrorsemailcontent'] = '{$a->amount} neue fehlerhafte tool_lifecycle Prozesse warten darauf, behandelt zu werden!' . "\n" . 'Bitte besuchen Sie {$a->url}.';
$string['notifyerrorsemailcontenthtml'] = '{$a->amount} neue fehlerhafte tool_lifecycle Prozesse warten darauf, behandelt zu werden!<br>Bitte besuchen Sie <a href="{$a->url}">die Übersichtsseite</a>.';
$string['notifyerrorsemailsubject'] = '{$a->amount} neue fehlerhafte tool_lifecycle Prozesse warten darauf, behandelt zu werden!';
$string['overview:add_trigger'] = 'Trigger hinzufügen';
$string['overview:add_trigger_help'] = 'Es kann nur eine Instanz jedes Triggertyps hinzugefügt werden.';
$string['overview:trigger'] = 'Trigger';
$string['overview:trigger_help'] = 'Ein Kurs fängt nur dann an, einen Workflow zu durchlaufen, wenn alle Trigger des Workflows dies übereinstimmend (UND-Verknüpfung) aussagen.<br><br>
In den hier genannten Zahlen werden Kurse, die verzögert werden oder sich bereits in anderen Workflows befinden, nicht mitgezählt.<br>
Trotzdem sind die Zahlen nur approximiert, da es sein könnte, dass die Kurse vor diesem einen anderen Workflow auslösen.';
$string['pluginname'] = 'Kurs-Lebenszyklus';
$string['plugintitle'] = 'Kurs-Lebenszyklus';
$string['privacy:metadata:tool_lifecycle_action_log'] = 'Ein Log von Aktionen des Kursmanagers.';
$string['privacy:metadata:tool_lifecycle_action_log:action'] = 'Bezeichnung der Aktion';
$string['privacy:metadata:tool_lifecycle_action_log:courseid'] = 'ID des von der Aktion betroffenen Kurses';
$string['privacy:metadata:tool_lifecycle_action_log:processid'] = 'ID des Prozesses, innerhalb dessen die Aktion durchgeführt wurde.';
$string['privacy:metadata:tool_lifecycle_action_log:stepindex'] = 'Der Index des Workflow-Steps, für den die Aktion durchgeführt wurde.';
$string['privacy:metadata:tool_lifecycle_action_log:time'] = 'Die Durchführungszeit der Aktion';
$string['privacy:metadata:tool_lifecycle_action_log:userid'] = 'ID der/des Benutzer:in, der/die die Aktion angestoßen hat.';
$string['privacy:metadata:tool_lifecycle_action_log:workflowid'] = 'ID des Worflows innerhalb dessen die Aktion durchgeführt wurde.';
$string['proceed'] = 'Fortfahren';
$string['process_errors_header'] = 'Fehler';
$string['process_errors_header_title'] = 'Liste der Bearbeitungsfehler';
$string['process_proceeded_event'] = 'Ein Prozess wurde fortgeführt';
$string['process_rollback_event'] = 'Ein Prozess wurde zurückgesetzt';
$string['process_triggered_event'] = 'Ein Prozess wurde ausgelöst';
$string['restore'] = 'Wiederherstellen';
$string['restore_error_in_step'] = 'Beim Importieren des Steps \'{$a}\' ist ein Fehler aufgetreten: ';
$string['restore_error_in_trigger'] = 'Beim Importieren des Triggers \'{$a}\' ist ein Fehler aufgetreten: ';
$string['restore_step_does_not_exist'] = 'Der Schritt {$a} ist nicht installiert, aber in der Sicherungsdatei enthalten. Bitte installieren Sie ihn zuerst und versuchen es dann erneut.';
$string['restore_subplugins_invalid'] = 'Falsches Format der Sicherungsdatei. Das Format der Subpluginelemente ist nicht wie erwartet.';
$string['restore_trigger_does_not_exist'] = 'Der Trigger {$a} ist nicht installiert, aber in der Sicherungsdatei enthalten. Bitte installieren Sie ihn zuerst und versuchen es dann erneut.';
$string['restore_workflow_not_found'] = 'Falsches Format der Sicherungsdatei. Der Workflow konnte nicht gefunden werden.';
$string['rollback'] = 'Zurücksetzung';
$string['rolledback'] = 'Zurückgesetzt';
$string['searchcourses'] = 'Kurs-Suche';
$string['see_in_workflow'] = 'In Workflow ansehen';
$string['show_delays'] = 'Wähle die Ansicht';
$string['showextendeddetails'] = 'Zeige detaillierte Workflow-Informationen';
$string['status'] = 'Status';
$string['step'] = 'Schritt';
$string['step_delete'] = 'Entfernen';
$string['step_edit'] = 'Bearbeiten';
$string['step_instancename'] = 'Instanzname';
$string['step_instancename_help'] = 'Titel der Schritt/Trigger-Instanz (nur für Admins sichtbar).';
$string['step_settings_header'] = 'Einstellungen des Schritts';
$string['step_show'] = 'Anzeigen';
$string['step_sortindex'] = 'Hoch/Runter';
$string['step_subpluginname'] = 'Schritt Typ';
$string['step_subpluginname_help'] = 'Name des Schritt/Trigger-Subplugins (nur für Admins sichtbar).';
$string['step_type'] = 'Typ';
$string['steps_installed'] = 'Installierte Schritt-Subplugins';
$string['steptype_settings_header'] = 'Spezifische Einstellungen des Schritttypen';
$string['subplugins'] = 'Subplugins';
$string['subpluginsdesc'] = 'Subplugins';
$string['subplugintype_lifecyclestep'] = 'Schritt eines Lifecycle-Prozesses';
$string['subplugintype_lifecyclestep_plural'] = 'Schritte eines Lifecycle-Prozesses';
$string['subplugintype_lifecycletrigger'] = 'Trigger zum Starten eines Lifecycle-Prozesses';
$string['subplugintype_lifecycletrigger_plural'] = 'Trigger zum Starten eines Lifecycle-Prozesses';
$string['tablecourseslog'] = 'Vergangene Aktionen';
$string['tablecoursesremaining'] = 'Restliche Kurse';
$string['tablecoursesrequiringattention'] = 'Kurse, die Ihre Aufmerksamkeit erfordern!';
$string['tools'] = 'Aktionen';
$string['trigger'] = 'Trigger';
$string['trigger_does_not_exist'] = 'Der Trigger existiert nicht.';
$string['trigger_enabled'] = 'Aktiviert';
$string['trigger_instancename'] = 'Instanzname';
$string['trigger_instancename_help'] = 'Titel der Trigger-Instanz (nur sichtbar für Admins).';
$string['trigger_settings_header'] = 'Einstellungen des Triggers';
$string['trigger_sortindex'] = 'Hoch/Runter';
$string['trigger_subpluginname'] = 'Trigger Typ';
$string['trigger_subpluginname_help'] = 'Name des Schritt/Trigger-Subplugins (nur für Admins sichtbar).';
$string['trigger_workflow'] = 'Workflow';
$string['triggers_installed'] = 'Installierte Trigger';
$string['triggertype_settings_header'] = 'Spezifische Einstellungen des Triggertypen';
$string['type'] = 'Typ';
$string['upload_workflow'] = 'Workflow hochladen';
$string['viewheading'] = 'Kurse verwalten';
$string['viewsteps'] = 'Zeige Workflowschritte';
$string['workflow'] = 'Workflow';
$string['workflow_active'] = 'Aktiv';
$string['workflow_definition_heading'] = 'Workflowdefinitionen';
$string['workflow_delayforallworkflows'] = 'Ausschluss für alle Workflows?';
$string['workflow_delayforallworkflows_help'] = 'Falls ja, wird ein Kurs für die oben genannte Zeit nicht nur von diesem, sondern
 von allen Workflows ausgeschlossen. Das heißt, bis die Zeit abgelaufen ist, kann kein Prozess für den Kurs gestartet werden.';
$string['workflow_displaytitle'] = 'Angezeigter Titel des Workflows';
$string['workflow_displaytitle_help'] = 'Dieser Titel wird Nutzern beim Verwalten ihrer Kurse angezeigt.';
$string['workflow_drafts_header'] = 'Entwürfe';
$string['workflow_drafts_header_title'] = 'Liste von Workflow-Entwürfen';
$string['workflow_drafts_list'] = 'Zeige Workflow-Entwürfe';
$string['workflow_duplicate_title'] = '{$a} (Kopie)';
$string['workflow_finishdelay'] = 'Kursauschluss bei Beendigung';
$string['workflow_finishdelay_help'] = 'Dieser Wert beschreibt die Zeit, bis wieder ein Prozess für diesen Workflow und einen Kurs
 gestartet werden kann, nachdem der Kurs einen Prozess dieses Workflows beendingt hat.';
$string['workflow_is_running'] = 'Workflow läuft.';
$string['workflow_not_removeable'] = 'Es ist nicht möglich, diese Workflow-Instanz zu entfernen. Vielleicht hat sie noch laufende Prozesse?';
$string['workflow_processes'] = 'Aktive Prozesse';
$string['workflow_rollbackdelay'] = 'Kursauschluss beim Zurücksetzen';
$string['workflow_rollbackdelay_help'] = 'Dieser Wert beschreibt die Zeit, bis wieder ein Prozess für diesen Workflow und einen Kurs
 gestartet werden kann, nachdem der Kurs innerhalb eines Prozesses dieses Workflows zurückgesetzt wurde.';
$string['workflow_sortindex'] = 'Hoch/RUnter';
$string['workflow_started'] = 'Workflow gestartet.';
$string['workflow_timeactive'] = 'Aktiv seit';
$string['workflow_timedeactive'] = 'Deaktiviert seit';
$string['workflow_title'] = 'Titel';
$string['workflow_title_help'] = 'Titel des Workflows (nur sichtbar für Admins).';
$string['workflow_tools'] = 'Aktionen';
$string['workflow_trigger'] = 'Trigger für den Workflow';
$string['workflow_was_not_imported'] = 'Der Workflow wurde nicht importiert!';
$string['workflownotfound'] = 'Es konnte kein Workflow mit der ID {$a} gefunden werden!';
$string['workflowoverview'] = 'Workflow ansehen';
$string['workflowoverview_list_header'] = 'Details zu Workflows';
$string['workflowsettings'] = 'Workflow Einstellungen';
