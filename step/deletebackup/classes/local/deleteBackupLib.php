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
 * Life Cycle Delete Backup Step Library
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecyclestep_deletebackup\local;

defined('MOODLE_INTERNAL') || die();

class deleteBackupLib {
    // Test code
    public function test() {
        echo("<br>Test step: <strong>deletebackup</strong> (for <u>tool lifecycle</u>)<br>");
        echo("<hr><br><strong>Show course backup records:</strong>");
        $results = $this->delete_backups((time() - 365 * 24 * 60 * 60), 10);
        echo("<br>Course backup records: ".count($results)."<br>");
        foreach($results as $result) { echo("<br>Record id: ".$result['recordid']." (deleted: ".$result['recorddeleted']."), file: ".$result['backupfile']." (deleted: ".$result['filedeleted'].")"); }
        echo("<hr><br><strong>Show course backup files:</strong>");
        $files = $this->get_backup_files();
        echo("<br>Course backup files: ".count($files)."<br>");
        foreach($files as $file) { echo("<br>".$file['file']."&nbsp;&nbsp;(parsed: ".$file['filedate'].", created: ".$file['creationdate'].", modified: ".$file['modificationdate'].")"); }
    }

    // Delete course backup files older than date
    public function delete_backups($timestamp = null, $maxdeletions = 10) {
        global $DB;

        // If date is null, use now - 1 year
        if(is_null($timestamp) || empty($timestamp) || !is_int($timestamp) /*|| !ctype_digit($timestamp)*/ || $timestamp < -1 /*|| $timestamp <= 2147483647*/) {
            $timestamp = time() - 365 * 24 * 60 * 60;
        // Info: Needed because of the switch from date_selector (= unix timestamp) to text (int value of days)
        } else { $timestamp = time() - $timestamp * 24 * 60 * 60; }
        //$now = new \DateTime(); $interval = $now->diff(new \DateTime("@$timestamp"));
        //echo("Check 'border date': ".date('Y-m-d, H:i:s', $timestamp)." -> ".$interval->days." (".((time() - $timestamp) / (60 * 60 * 24)).") days ago");

        // Get all course backups older than timestamp from db
        $sql = "SELECT id, backupfile FROM {tool_lifecycle_backups} WHERE backupcreated < ?";
        $records = $DB->get_records_sql($sql, [$timestamp]);
        //echo("<br>Course backup records: ".count($records)."<br>"); foreach($records as $record) { echo("<br>id: ".$record->id.", backupfile: ".$record->backupfile); }

        /* Get backup ids from db by filenames
        $files = ['2024-10-15-ID-238-COURSE-6657.mbz', '2024-10-15-ID-239-COURSE-6658.mbz', '2024-10-15-ID-240-COURSE-6659.mbz'];
        list($insql, $inparams) = $DB->get_in_or_equal($files);
        $sql = "SELECT id FROM {tool_lifecycle_backups} WHERE backupfile $insql ORDER BY backupcreated ASC";
        $backupids = $DB->get_fieldset_sql($sql, $inparams);
        echo "<br>Course backup ids: "; foreach($backupids as $id) { echo $id.", "; }

        // Get backup records from db by filenames
        $sql = "SELECT * FROM {tool_lifecycle_backups} WHERE backupfile $insql ORDER BY backupcreated ASC";
        $records = $DB->get_records_sql($sql, $inparams);
        echo"<br>Course backup records: "; foreach($records as $record) { echo "<br>".print_r($record, true); }
        */

        // Get path of backup folder
        $path = get_config('tool_lifecycle', 'backup_path');
        // Define file extension of course backups
        $extension = "mbz";
        // Define result array
        $results = [];
        $counter = 0;

        // Check if backup path exists
        if (is_dir($path)) {
            foreach($records as $record) {
                $deleted_file = -1;
                $deleted_record = -1;

                if($counter < $maxdeletions) {
                    // If file exists
                    if(is_file("$path/$record->backupfile")) {
                        // If file extension = mbz
                        if(pathinfo($record->backupfile, PATHINFO_EXTENSION) === $extension
                                // If creation time of file <= timestamp & modification time of file <= timestamp
                                /* && filectime("$path/$record->backupfile") < $timestamp && filemtime("$path/$record->backupfile") < $timestamp*/
                                // If parsed time of file name <= timestamp
                                && preg_match('/(\d{4}-\d{2}-\d{2})/', $record->backupfile, $matches) && \DateTime::createFromFormat('Y-m-d', $matches[1])->getTimestamp() < $timestamp
                        ) {
                            // Set permissions to 777 of course backup file
                            chmod("$path/$record->backupfile", 0777);
                            // Clears file status cache
                            clearstatcache();
                            // Delete course backup file first
                            $deleted_file = unlink("$path/$record->backupfile");
                            // If deletion of course backup file was unsuccessful
                            if (!$deleted_file) {
                                // Delete course backup file with system command
                                exec("rm -f " . escapeshellarg("$path/$record->backupfile"), $output, $result_code);
                                //mtrace("  Output: ".$result_code." - ".print_r($output, true));
                                if ($result_code == 0) {
                                    $deleted_file = 1;
                                }
                            }

                            // If file was deleted, delete record too
                            if($deleted_file) { $deleted_record = $DB->delete_records('tool_lifecycle_backups', array('backupfile' => $record->backupfile)); }
                            $counter++;
                        }
                    // If file not exists, delete just record
                    } else { $deleted_record = $DB->delete_records('tool_lifecycle_backups', array('backupfile' => $record->backupfile)); }

                    $results[] = ['recordid' => $record->id, 'backupfile' => $path.'/'.$record->backupfile, 'recorddeleted' => $deleted_record, 'filedeleted' => $deleted_file];
                    //echo("<br>record id: ".$record->id." -> deleted: ".$deleted_record.", file: $path/$record->backupfile -> deleted: ".$deleted_file);
                }
            }
        } else { echo("<br>Backup path: $path does not exist!"); }

        return $results;
    }

    // Get course backup files (of backup dir)
    public function get_backup_files() {
        // Get path of backup folder
        $path = get_config('tool_lifecycle', 'backup_path');
        // Define file extension of course backups
        $extension = "mbz";
        $results = [];

        // Check if backup path exists
        if (is_dir($path)) {
            //echo("<br>Course backup files: ".count($files)."<br>");
            // Get files of backup folder
            $files = array_diff(scandir($path), array('.', '..'));
            // Filter files by extension
            $files = array_filter($files, function($file) use ($path, $extension) {
                return is_file("$path/$file") && pathinfo($file, PATHINFO_EXTENSION) === $extension;
            });

            // If files exist
            if (!empty($files)) {
                //echo("<br>Course backup files: ".count($files)."<br>");
                // For each file
                foreach ($files as $file) {
                    // Parse creation date of file name
                    //$archivefile = date("Y-m-d")."-ID-{$recordid}-COURSE-{$courseid}.mbz";
                    $file_date = false;
                    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
                        // Create date of first match and reformat it
                        $file_date = \DateTime::createFromFormat('Y-m-d', $matches[1])->format('d.m.Y');
                    }
                    // Get creation date of file
                    $creation_date = date("d.m.Y", filectime("$path/$file"));
                    // Get modification date of file
                    $modification_date = date("d.m.Y", filemtime("$path/$file"));

                    $results[] = ['file' => $file, 'filedate' => $file_date,'creationdate' => $creation_date, 'modificationdate' => $modification_date];
                    //echo("<br>".$file."&nbsp;&nbsp;(parsed: ".$file_date.", created: ".$creation_date.", modified: ".$modification_date.")");
                }
            } else { echo("<br>No files found in the backup directory"); }
        } else { echo("<br>Backup path: $path does not exist!"); }

        return $results;
    }
}
