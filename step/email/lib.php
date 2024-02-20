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
 * Step subplugin for sending out emails to the teacher.
 * @package lifecyclestep_email
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use core_user;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

define('EMAIL_PROCDATA_KEY_KEEP', 'keep');

/**
 * Step subplugin for sending out emails to the teacher.
 * @package lifecyclestep_email
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class email extends libbase {

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
        global $DB;
        $coursecontext = \context_course::instance($course->id);
        $userstobeinformed = get_enrolled_users($coursecontext, 'lifecyclestep/email:preventdeletion', 0,
            'u.id', null, null, null, true);
        foreach ($userstobeinformed as $user) {
            $record = new \stdClass();
            $record->touser = $user->id;
            $record->courseid = $course->id;
            $record->instanceid = $instanceid;
            $DB->insert_record('lifecyclestep_email', $record);
        }
        return step_response::waiting();
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
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        // When time runs up and no one wants to keep the course, then proceed.
        $process = process_manager::get_process_by_id($processid);
        if (null !== $process && $process->timestepchanged < time() - settings_manager::get_settings(
                $instanceid,
                settings_type::STEP
                )['responsetimeout']) {
            return step_response::proceed();
        }
        return step_response::waiting();
    }

    /**
     * Send emails to all teachers, but only one mail per teacher.
     */
    public function post_processing_bulk_operation() {
        global $DB, $PAGE;
        $stepinstances = step_manager::get_step_instances_by_subpluginname($this->get_subpluginname());
        foreach ($stepinstances as $step) {
            $settings = settings_manager::get_settings($step->id, settings_type::STEP);
            // Set system context, since format_text needs a context.
            $PAGE->set_context(\context_system::instance());
            // Format the raw string in the DB to FORMAT_HTML.
            $settings['contenthtml'] = format_text($settings['contenthtml'], FORMAT_HTML);

            $userstobeinformed = $DB->get_records('lifecyclestep_email',
                ['instanceid' => $step->id], '', 'distinct touser');
            foreach ($userstobeinformed as $userrecord) {
                $user = \core_user::get_user($userrecord->touser);
                $transaction = $DB->start_delegated_transaction();
                $mailentries = $DB->get_records('lifecyclestep_email',
                    ['instanceid' => $step->id,
                        'touser' => $user->id, ]);

                    $parsedsettings = $this->replace_placeholders($settings, $user, $step->id, $mailentries);

                    $subject = $parsedsettings['subject'];
                    $content = $parsedsettings['content'];
                    $contenthtml = $parsedsettings['contenthtml'];
                    // TODO: use course info to parse content template!
                    email_to_user($user, \core_user::get_noreply_user(), $subject, $content, $contenthtml);
                    $DB->delete_records('lifecyclestep_email',
                    ['instanceid' => $step->id,
                        'touser' => $user->id, ]);
                    $transaction->allow_commit();
            }
        }

    }

    /**
     * Replaces certain placeholders within the mail template.
     * @param string[] $strings array of mail templates.
     * @param core_user $user User object.
     * @param int $stepid Id of the step instance.
     * @param array[] $mailentries Array consisting of course entries from the database.
     * @return string[] array of mail text.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function replace_placeholders($strings, $user, $stepid, $mailentries) {
        global $CFG;

        $patterns = [];
        $replacements = [];

        // Replaces firstname of the user.
        $patterns[] = '##firstname##';
        $replacements[] = $user->firstname;

        // Replaces lastname of the user.
        $patterns[] = '##lastname##';
        $replacements[] = $user->lastname;

        // Replace link to interaction page.
        $interactionlink = new \moodle_url('admin/tool/lifecycle/view.php');
        $url = $CFG->wwwroot . '/' . $interactionlink->out();
        $patterns[] = '##link##';
        $replacements[] = $url;

        // Replace html link to interaction page.
        $patterns[] = '##link-html##';
        $replacements[] = \html_writer::link($url, $url);

        // Replace courses list.
        $patterns[] = '##courses##';
        $courses = $mailentries;
        $coursesstring = '';
        $coursesstring .= $this->parse_course(array_pop($courses)->courseid);
        foreach ($courses as $entry) {
            $coursesstring .= "\n" . $this->parse_course($entry->courseid);
        }
        $replacements[] = $coursesstring;

        // Replace courses html.
        $patterns[] = '##courses-html##';
        $courses = $mailentries;
        $coursestabledata = [];
        foreach ($courses as $entry) {
            $coursestabledata[$entry->courseid] = $this->parse_course_row_data($entry->courseid);
        }
        $coursestable = new \html_table();
        $coursestable->data = $coursestabledata;
        $replacements[] = \html_writer::table($coursestable);

        return str_ireplace($patterns, $replacements, $strings);
    }

    /**
     * Parses a course for the non html format.
     * @param int $courseid id of the course
     * @return string
     * @throws \dml_exception
     */
    private function parse_course($courseid) {
        $course = get_course($courseid);
        $result = $course->fullname;
        return $result;
    }

    /**
     * Parses a course for the html format.
     * @param int $courseid id of the course
     * @return array column of a course
     * @throws \dml_exception
     */
    private function parse_course_row_data($courseid) {
        $course = get_course($courseid);
        return [$course->fullname];
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('responsetimeout', PARAM_INT, false),
            new instance_setting('subject', PARAM_TEXT, true),
            new instance_setting('content', PARAM_RAW, true),
            new instance_setting('contenthtml', PARAM_RAW, true),
        ];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'email';
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'responsetimeout';
        $mform->addElement('duration', $elementname, get_string('email_responsetimeout', 'lifecyclestep_email'));
        $mform->setType($elementname, PARAM_INT);
        $elementname = 'subject';
        $mform->addElement('text', $elementname, get_string('email_subject', 'lifecyclestep_email'));
        $mform->addHelpButton($elementname, 'email_subject', 'lifecyclestep_email');
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'content';
        $mform->addElement('textarea', $elementname, get_string('email_content', 'lifecyclestep_email'));
        $mform->addHelpButton($elementname, 'email_content', 'lifecyclestep_email');
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'contenthtml';
        $mform->addElement('editor', $elementname, get_string('email_content_html', 'lifecyclestep_email'));
        $mform->addHelpButton($elementname, 'email_content_html', 'lifecyclestep_email');
        $mform->setType($elementname, PARAM_RAW);
    }

    /**
     * This method can be overriden, to set default values to the form_step_instance.
     * It is called in definition_after_data().
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        $mform->setDefault('contenthtml',
                ['text' => isset($settings['contenthtml']) ? $settings['contenthtml'] : '', 'format' => FORMAT_HTML]);
    }
}
