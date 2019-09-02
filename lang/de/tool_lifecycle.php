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
 * Life cycle langauge strings.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Kurs-Lebenszyklus';
$string['plugintitle'] = 'Kurs-Lebenszyklus';

$string['lifecycle:managecourses'] = 'Darf Kurse in tool_lifecycle verwalten.';
$string['managecourses_link'] = 'Kurse verwalten';

$string['general_config_header'] = "Allgemein & Subplugins";
$string['config_delay_duration'] = 'Standardlänge eines Kursausschlusses';
$string['config_delay_duration_desc'] = 'Diese Einstellung definiert den Standardlänge einer Kursausschlusses in einem Workflow
 falls ein Prozess des Workflows zurückgesetzt oder beendigt wird. Die Länge des Kursausschlusses besagt, wie lange es dauert, bis
 der Kurs wieder vom Workflow bearbeitet wird.';
$string['active_processes_list_header'] = 'Aktive Prozesse';
$string['adminsettings_heading'] = 'Workflow-Einstellungen';
$string['active_manual_workflows_heading'] = 'Aktive manuelle Workflows';
$string['active_automatic_workflows_heading'] = 'Aktive automatische Workflows';
$string['workflow_definition_heading'] = 'Workflowdefinitionen';
$string['adminsettings_edit_workflow_definition_heading'] = 'Workflowdefinition';
$string['adminsettings_workflow_definition_steps_heading'] = 'Workflowschritte';
$string['adminsettings_edit_trigger_instance_heading'] = 'Trigger-Instanz für Workflow \'{$a}\'';
$string['adminsettings_edit_step_instance_heading'] = 'Schritt-Instanz für Workflow \'{$a}\'';
$string['add_new_step_instance'] = 'Füge neue Schritt-Instanz hinzu...';
$string['add_new_trigger_instance'] = 'Füge neue Trigger-Instanz...';
$string['step_settings_header'] = 'Spezifische Einstellungen des Schritttypen';
$string['trigger_settings_header'] = 'Spezifische Einstellungen des Triggertypen';
$string['general_settings_header'] = 'Allgemeine Einstellungen';
$string['followedby_none'] = 'Keine';
$string['invalid_workflow'] = 'Ungültige Workflowkonfiguration';
$string['invalid_workflow_details'] = 'Gehe zur Detailanzeige, um einen Trigger für diesen Workflow zu erstellen.';
$string['active_workflow_not_changeable'] = 'Die Workflow-Instanz wurde bereits aktiviert. Es ist nicht mehr möglich, Schritte zu ändern.';
$string['active_workflow_not_removeable'] = 'Die Workflow-Instanz ist aktiv. Es ist nicht möglich, sie zu entfernen.';
$string['workflow_not_removeable'] = 'Es ist nicht möglich, diese Workflow-Instanz zu entfernen. Vielleicht hat sie noch laufende Prozesse?';
$string['invalid_workflow_cannot_be_activated'] = 'Der Workflow kann nicht aktiviert werden, da die Workflowdefinition ungültig ist';
$string['trigger_does_not_exist'] = 'Der Trigger existiert nicht.';
$string['cannot_trigger_workflow_manually'] = 'Der Workflow konnte nicht manuell ausgelöst werden.';
$string['error_wrong_trigger_selected'] = 'Sie haben einen nichtmanuellen Trigger ausgewählt.';

$string['lifecycle_task'] = 'Führt den Lifecycle-Prozess aus.';

$string['trigger_subpluginname'] = 'Subplugin Name';
$string['trigger_subpluginname_help'] = 'Name des Schritt/Trigger-Subplugins (nur für Admins sichtbar).';
$string['trigger_instancename'] = 'Instanzname';
$string['trigger_instancename_help'] = 'Titel der Trigger-Instanz (nur sichtbar für Admins).';
$string['trigger_enabled'] = 'Aktiviert';
$string['trigger_sortindex'] = 'Hoch/Runter';
$string['trigger_workflow'] = 'Workflow';

$string['workflow'] = 'Workflow';
$string['add_workflow'] = 'Workflow hinzufügen';
$string['upload_workflow'] = 'Workflow hochladen';
$string['workflow_title'] = 'Titel';
$string['workflow_title_help'] = 'Titel des Workflows (nur sichtbar für Admins).';
$string['workflow_displaytitle'] = 'Angezeigter Titel des Workflows';
$string['workflow_displaytitle_help'] = 'Dieser Titel wird Nutzern beim Verwalten ihrer Kurse angezeigt.';
$string['workflow_rollbackdelay'] = 'Kursauschluss beim Zurücksetzen';
$string['workflow_rollbackdelay_help'] = 'Dieser Wert beschreibt die Zeit, bis wieder ein Prozess für diesen Workflow und einen Kurs
 gestarted werden kann, nachdem der Kurs innerhalb eines Prozesses dieses Workflows zurückgesetzt wurde.';
$string['workflow_finishdelay'] = 'Kursauschluss bei Beendigung';
$string['workflow_finishdelay_help'] = 'Dieser Wert beschreibt die Zeit, bis wieder ein Prozess für diesen Workflow und einen Kurs
 gestarted werden kann, nachdem der Kurs einen Prozess dieses Workflows beendingt hat.';
$string['workflow_delayforallworkflows'] = 'Ausschluss für alle Workflows?';
$string['workflow_delayforallworkflows_help'] = 'Falls ja, wird ein Kurs für die oben genannte Zeit nicht nur von diesem, sondern
 von allen Workflows ausgeschlossen. Das heißt, bis die Zeit abgelaufen ist, kann kein Prozess für den Kurs gestartet werden.';
$string['workflow_active'] = 'Aktiv';
$string['workflow_processes'] = 'Aktive Prozesse';
$string['workflow_timeactive'] = 'Aktiv seit';
$string['workflow_sortindex'] = 'Hoch/RUnter';
$string['workflow_tools'] = 'Aktionen';
$string['viewsteps'] = 'Zeige Workflowschritte';
$string['editworkflow'] = 'Allgemeine Einstellungen bearbeiten';
$string['backupworkflow'] = 'Workflow sichern';
$string['duplicateworkflow'] = 'Workflow duplizieren';
$string['deleteworkflow'] = 'Workflow löschen';
$string['deleteworkflow_confirm'] = 'Sie sind dabei, den Workflow zu löschen. Das kann nicht rückgängig gemacht werden. Sind Sie sicher?';
$string['activateworkflow'] = 'Aktivieren';
$string['disableworkflow'] = 'Workflow deaktivieren (Prozesse laufen weiter)';
$string['disableworkflow_confirm'] = 'Sie sind dabei, den Workflow zu deaktivieren. Sind Sie sicher?';
$string['abortdisableworkflow'] = 'Workfow deaktivieren (Prozesse werden abgebrochen, eventuell unsicher!)';
$string['abortdisableworkflow_confirm'] = 'Sie sind dabei, den Workflow zu deaktivieren. Alle laufenden Prozesse werden abgebrochen. Sind Sie sicher?';
$string['abortprocesses'] = 'Laufende Prozesse abbrechen (eventuell unsicher!)';
$string['abortprocesses_confirm'] = 'Alle laufenden Prozesse dieses Workflows werden abgebrochen. Sind Sie sicher?';
$string['workflow_duplicate_title'] = '{$a} (Kopie)';

// Deactivated workflows.
$string['deactivated_workflows_list'] = 'Zeige deaktivierte Workflows';
$string['deactivated_workflows_list_header'] = 'Deaktivierte Workflows';
$string['workflow_timedeactive'] = 'Deaktiviert seit';
$string['active_workflows_list'] = 'Zeige aktive Workflows und Workflowdefinitionen';

$string['step_type'] = 'Typ';
$string['step_subpluginname'] = 'Subpluginname';
$string['step_subpluginname_help'] = 'Name des Schritt/Trigger-Subplugins (nur für Admins sichtbar).';
$string['step_instancename'] = 'Instanzname';
$string['step_instancename_help'] = 'Titel der Schritt/Trigger-Instanz (nur für Admins sichtbar).';
$string['step_sortindex'] = 'Hoch/Runter';
$string['step_edit'] = 'Bearbeiten';
$string['step_show'] = 'Anzeigen';
$string['step_delete'] = 'Entfernen';

$string['trigger'] = 'Trigger';
$string['step'] = 'Schritt';

$string['workflow_trigger'] = 'Trigger für den Workflow';

$string['lifecycletrigger'] = 'Trigger';
$string['lifecyclestep'] = 'Schritt';

$string['subplugintype_lifecycletrigger'] = 'Trigger zum Starten eines Lifecycle-Prozesses';
$string['subplugintype_lifecycletrigger_plural'] = 'Trigger zum Starten eines Lifecycle-Prozesses';
$string['subplugintype_lifecyclestep'] = 'Schritt eines Lifecycle-Prozesses';
$string['subplugintype_lifecyclestep_plural'] = 'Schritte eines Lifecycle-Prozesses';

$string['nointeractioninterface'] = 'Keine Interaktionsschnittstelle verfügbar!';
$string['tools'] = 'Dienstprogramme';
$string['status'] = 'Status';
$string['date'] = 'Fällligkeitsdatum';

$string['nostepfound'] = 'Es konnte kein Schritt mit der gegeben Schrittid gefunden werden!';
$string['noprocessfound'] = 'Es konnte kein Prozess mit der gegebenen Prozessid gefunden werden!';

$string['nocoursestodisplay'] = 'Es gibt derzeit keine Kurse, die Ihre Aufmerksamkeit erfordern!';

$string['course_backups_list_header'] = 'Kurssicherungen';
$string['backupcreated'] = 'Erstellt am';
$string['restore'] = 'Wiederherstellen';
$string['download'] = 'Herunterladen';

$string['workflownotfound'] = 'Es konnte kein Workflow mit der ID {$a} gefunden werden!';

// View.php.
$string['tablecoursesrequiringattention'] = 'Kurse, die Ihre Aufmerksamkeit erfordern!';
$string['tablecoursesremaining'] = 'Restliche Kurse';
$string['tablecourseslog'] = 'Vergangene Aktionen';
$string['viewheading'] = 'Kurse verwalten';
$string['interaction_success'] = 'Aktion erfolgreich gespeichert.';
$string['manual_trigger_success'] = 'Workflow erfolgreich gestartet.';
$string['manual_trigger_process_existed'] = 'Es existiert bereits ein Workflow für diesen Kurs.';

$string['coursename'] = 'Kursname';
$string['lastaction'] = 'Letzte Aktion am';

$string['workflow_started'] = 'Workflow gestartet.';
$string['workflow_is_running'] = 'Workflow läuft.';

// Backup & Restore.
$string['restore_workflow_not_found'] = 'Falsches Format der Sicherungsdatei. Der Workflow konnte nicht gefunden werden.';
$string['restore_subplugins_invalid'] = 'Falsches Format der Sicherungsdatei. Das Format der Subpluginelemente ist nicht wie erwartet.';
$string['restore_step_does_not_exist'] = 'Der Schritt {$a} ist nicht installiert, aber in der Sicherungsdatei enthalten. Bitte installieren Sie ihn zuerst und versuchen es dann erneut.';
$string['restore_trigger_does_not_exist'] = 'Der Trigger {$a} ist nicht installiert, aber in der Sicherungsdatei enthalten. Bitte installieren Sie ihn zuerst und versuchen es dann erneut.';

$string['process_triggered_event'] = 'Ein Prozess wurde ausgelöst';
$string['process_proceeded_event'] = 'Ein Prozess wurde fortgeführt';
$string['process_rollback_event'] = 'Ein Prozess wurde zurückgesetzt';