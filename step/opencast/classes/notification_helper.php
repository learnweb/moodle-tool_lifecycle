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
 * Helper class to handle notifications in Opencast Step
 *
 * @package    lifecyclestep_opencast
 * @copyright  2023 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_opencast;

/**
 * Helper class to handle notifications in Opencast Step
 */
class notification_helper {
    /** @var bool $notificationenabled Whether notifying feature is On. */
    private bool $notificationenabled = true;
    /**
     * Constructor function.
     * @param bool $notificationenabled A flag to make sure that the settings is enabled.
     */
    public function __construct(bool $notificationenabled) {
        $this->notificationenabled = $notificationenabled;
    }
    /**
     * Notifies admins when a course is processed.
     *
     * @param stdClass $course the course object.
     * @param string $ocworkflow the workflow.
     */
    public function notify_course_processed($course, $ocworkflow) {
        if (!$this->notificationenabled) {
            return;
        }
        $coursefullname = get_string('coursefullnameunknown', 'lifecyclestep_opencast');
        if ($course->fullname) {
            $coursefullname = $course->fullname;
        }
        $a = (object)[
            'courseid' => $course->id,
            'coursefullname' => $coursefullname,
            'ocworkflow' => $ocworkflow,
        ];

        $subject = get_string('notifycourseprocessed_subj', 'lifecyclestep_opencast');
        $body = get_string('notifycourseprocessed_body', 'lifecyclestep_opencast', $a);

        $admin = get_admin();
        $this->send_message('error', $admin, $subject, $body);
    }

    /**
     * Notifies admins upon failied workflow start on an event.
     *
     * @param stdClass $course the course object.
     * @param int $ocinstanceid the opencast instance id.
     * @param stdClass $video the video object.
     * @param string $ocworkflow the workflow.
     */
    public function notify_failed_workflow($course, $ocinstanceid, $video, $ocworkflow) {
        if (!$this->notificationenabled) {
            return;
        }
        $coursefullname = get_string('coursefullnameunknown', 'lifecyclestep_opencast');
        if ($course->fullname) {
            $coursefullname = $course->fullname;
        }
        $a = (object)[
            'courseid' => $course->id,
            'coursefullname' => $coursefullname,
            'ocworkflow' => $ocworkflow,
            'videotitle' => $video->title,
            'videoidentifier' => $video->identifier,
            'ocinstanceid' => $ocinstanceid,
        ];

        $subject = get_string('errorfailedworkflow_subj', 'lifecyclestep_opencast');
        $body = get_string('errorfailedworkflow_body', 'lifecyclestep_opencast', $a);

        $admin = get_admin();
        $this->send_message('error', $admin, $subject, $body);
    }

    /**
     * Notifies admins upon fatal error.
     * It is a complimentary function to use anywhere that fits, by default try catch in cron job is handled by moodle itself.
     *
     * @param stdClass $course the course object.
     * @param int $ocinstanceid the opencast instance id.
     * @param string $ocworkflow the workflow.
     * @param string $error error details.
     */
    public function notify_error($course, $ocinstanceid, $ocworkflow, $error) {
        if (!$this->notificationenabled) {
            return;
        }
        $coursefullname = get_string('coursefullnameunknown', 'lifecyclestep_opencast');
        if ($course->fullname) {
            $coursefullname = $course->fullname;
        }
        $a = (object)[
            'courseid' => $course->id,
            'coursefullname' => $coursefullname,
            'ocworkflow' => $ocworkflow,
            'error' => $error,
            'ocinstanceid' => $ocinstanceid,
        ];

        $subject = get_string('errorexception_subj', 'lifecyclestep_opencast');
        $body = get_string('errorexception_body', 'lifecyclestep_opencast', $a);

        $admin = get_admin();
        $this->send_message('error', $admin, $subject, $body);
    }

    /**
     * Notifies admins upon fatal error.
     * It is a complimentary function to use anywhere that fits, by default try catch in cron job is handled by moodle itself.
     *
     * @param stdClass $course the course object.
     * @param int $ocinstanceid the opencast instance id.
     * @param string $ocworkflow the workflow.
     */
    public function notify_workflow_not_exists($course, $ocinstanceid, $ocworkflow) {
        if (!$this->notificationenabled) {
            return;
        }
        $coursefullname = get_string('coursefullnameunknown', 'lifecyclestep_opencast');
        if ($course->fullname) {
            $coursefullname = $course->fullname;
        }
        $a = (object)[
            'courseid' => $course->id,
            'coursefullname' => $coursefullname,
            'ocworkflow' => $ocworkflow,
            'ocinstanceid' => $ocinstanceid,
        ];

        $subject = get_string('errorworkflownotexists_subj', 'lifecyclestep_opencast');
        $body = get_string('errorworkflownotexists_body', 'lifecyclestep_opencast', $a);

        $admin = get_admin();
        $this->send_message('error', $admin, $subject, $body);
    }

    /**
     * Sends moodle internal message.
     *
     * @param string $messagetype Message type
     * @param object $touser User to which notification is sent
     * @param string $subject Subject
     * @param string $body Body
     * @param string $format Format
     */
    private function send_message($messagetype, $touser, $subject, $body, $format = FORMAT_PLAIN) {
        $message = new \core\message\message();
        $message->courseid = SITEID;
        $message->component = 'block_opencast';
        $message->name = $messagetype;
        $message->userfrom = \core_user::get_user(\core_user::NOREPLY_USER);
        $message->userto = $touser;
        $message->subject = $subject;
        $message->fullmessage = html_to_text($body);
        $message->fullmessageformat = $format;
        $message->fullmessagehtml = $body;
        $message->smallmessage = '';
        $message->notification = 1;

        message_send($message);
    }
}
