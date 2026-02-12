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
 * Lang strings for delete backup step
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['backupdeleted'] = 'Löschzeitpunkt';
$string['backupdeletionlogtable'] = 'Kurssicherungslöschungs-Log von Schritt {$a}';
$string['deletebackupsolderthan'] = 'Lösche alle Backups, die älter sind als x Tage';
$string['files'] = 'Sicherungsdateien gelöscht';
$string['log'] = 'Log-Einträge schreiben';
$string['log_help'] = 'Mit dieser Option bestimmen Sie, ob Log-Einträge pro gelöschtem Backup geschrieben werden sollen. Diese können Sie im Workflow-Übersicht bei diesem Schritt sehen.';
$string['maximumdeletionspercron'] = 'Maximale Anzahl an Backuplöschungen per Cron Job';
$string['mtracebackupdeleted'] = 'Kurssicherung ID {$a->recordid}. Datensatz erfolgreich gelöscht: {$a->recorddeleted}. Sicherungsdatei {$a->backupfile} erfolgreich gelöscht: {$a->filedeleted}';
$string['plugindescription'] = 'Backuplöschen-Schritt';
$string['pluginname'] = 'Backuplöschen-Schritt';
$string['privacy:metadata'] = 'Dieses Subplugin speichert keine persönlichen Daten.';
