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
 * Step subplugin to delete a backup.
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;

// Import trigger's lib
use lifecyclestep_deletebackup\local\deleteBackupLib;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Step subplugin to delete a backup.
 *
 * @package    lifecyclestep_deletebackup
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deletebackup extends libbase {

    /** @var int $numberofdeletions Deletions done so far in this php call. */
    private static $numberofdeletions = 0;

    /**
     * Processes the course and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        // Get settings
        $settings = settings_manager::get_settings($instanceid, settings_type::STEP);
        //mtrace("SETTINGS: ".print_r($settings, true));

        // Call $deleteBackup->delete_backups(...) only once
        if (self::$numberofdeletions < 1) {
            // Create deleteBackup
            $deleteBackup = new deleteBackupLib();

            // Delete course backup files older than date
            $results = $deleteBackup->delete_backups($settings['deletebackupsolderthan'], $settings['maximumdeletionspercron']);
            foreach ($results as $result) { mtrace("Record id: " . $result['recordid'] . " (deleted: " . $result['recorddeleted'] . "), file: " . $result['backupfile'] . " (deleted: " . $result['filedeleted'] . ")"); }

            // Get course backup files (of backup dir)
            //$files = $deleteBackup->get_backup_files();
            //foreach($files as $file) { mtrace($file['file']."  (parsed: ".$file['filedate'].", created: ".$file['creationdate'].", modified: ".$file['modificationdate'].")"); }

            // Wait with further deletions til the next cron execution
            //return step_response::waiting();
        }

        // Raise deletion counter
        self::$numberofdeletions++;

        return step_response::proceed();
    }

    /**
     * Processes the course in status waiting and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        return $this->process_course($processid, $instanceid, $course);
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'deletebackup';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            // Add instance for maximum deletions (per cron execution)
            new instance_setting('maximumdeletionspercron', PARAM_INT, true),
            // Add instance for deleting all backups that are older than
            new instance_setting('deletebackupsolderthan', PARAM_INT, true),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        // Add input field for maximum deletions (per cron execution)
        $mform->addElement('text', 'maximumdeletionspercron', get_string('maximumdeletionspercron', 'lifecyclestep_deletebackup'));
        $mform->setType('maximumdeletionspercron', PARAM_INT);
        $mform->setDefault('maximumdeletionspercron', 10);

        // Add a date selector element for deleting all backups that are older than this
        //$mform->addElement('date_selector', 'deletebackupsolderthan', get_string('deletebackupsolderthan','lifecyclestep_deletebackup'));
        //$mform->setDefault('deletebackupsolderthan', (time() - 365 * 24 * 60 * 60));

        // Add input field for deleting all backups that are older than X days
        $mform->addElement('text', 'deletebackupsolderthan', get_string('deletebackupsolderthan', 'lifecyclestep_deletebackup'));
        $mform->setType('deletebackupsolderthan', PARAM_INT);
        $mform->setDefault('deletebackupsolderthan', 365);
    }
}
