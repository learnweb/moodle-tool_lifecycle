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
 * Provides download for backup
 *
 * @package tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$backupid = required_param('backupid', PARAM_INT);

$backuprecord = $DB->get_record('tool_lifecycle_backups', ['id' => $backupid], 'backupfile', MUST_EXIST);
$source = get_config('tool_lifecycle', 'backup_path') . DIRECTORY_SEPARATOR . $backuprecord->backupfile;

if (!file_exists($source)) {
    throw new \moodle_exception('errorbackupfiledoesnotexist', 'tool_lifecycle', $source);
}

header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"backup-$backuprecord->backupfile\"");
readfile($source);
