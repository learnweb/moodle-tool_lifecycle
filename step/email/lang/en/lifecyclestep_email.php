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

$string['action_prevented_deletion'] = '{$a} prevented deletion';
$string['email:preventdeletion'] = 'Prevent deletion';
$string['email_content'] = 'Content plain text template';
$string['email_content_help'] = 'Set the template for the content of the email (plain text, alternatively you can use HTML template for HTML email below)' . '<p>' . 'You can use the following placeholders:'
        . '<br>' . 'First name of recipient: ##firstname##'
        . '<br>' . 'Last name of recipient: ##lastname##'
        . '<br>' . 'Link to response page: ##link##'
        . '<br>' . 'Impacted courses: ##courses##'
        . '</p>';
$string['email_content_html'] = 'Content HTML Template';
$string['email_content_html_help'] = 'Set the html template for the content of the email (HTML email, will be used instead of plaintext field if not empty!)' . '<p>' .  'You can use the following placeholders:'
        . '<br>' . 'First name of recipient: ##firstname##'
        . '<br>' . 'Last name of recipient: ##lastname##'
        . '<br>' . 'Link to response page: ##link-html##'
        . '<br>' . 'Impacted courses: ##courses-html##'
        . '</p>';
$string['email_responsetimeout'] = 'Time the user has for the response';
$string['email_subject'] = 'Subject template';
$string['email_subject_help'] = 'Set the template for the subject of the email.' . '<p>' . 'You can use the following placeholders:'
        . '<br>' . 'First name of recipient: ##firstname##'
        . '<br>' . 'Last name of recipient: ##lastname##'
        . '<br>' . 'Link to response page: ##link##'
        . '<br>' . 'Impacted courses: ##courses##'
        . '</p>';
$string['keep_course'] = 'Keep course';
$string['pluginname'] = 'Email step';
$string['privacy:metadata:lifecyclestep_email:courseid'] = 'ID of the course, emails will be sent for';
$string['privacy:metadata:lifecyclestep_email:instanceid'] = 'ID of the step instance sending emails';
$string['privacy:metadata:lifecyclestep_email:summary'] = 'Information about whhich users will be informed by email';
$string['privacy:metadata:lifecyclestep_email:touser'] = 'ID of the user who is being notified via email';
$string['status_message_requiresattention'] = 'Course is marked for deletion';
