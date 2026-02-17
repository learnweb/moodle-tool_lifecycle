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
 * Admin tool "Course Life Cycle" - Subplugin "Opencast step" - Library
 *
 * @package    lifecyclestep_opencast
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V. <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\step;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;
use tool_opencast\local\settings_api;
use block_opencast\setting_helper;
use lifecyclestep_opencast\notification_helper;
use lifecyclestep_opencast\log_helper;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

// Constants which are used in the plugin settings.
define('LIFECYCLESTEP_OPENCAST_SELECT_YES', 'yes');
define('LIFECYCLESTEP_OPENCAST_SELECT_NO', 'no');

/**
 * Admin tool "Course Life Cycle" - Subplugin "Opencast step" - Opencast class
 *
 * @package    lifecyclestep_opencast
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V. <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opencast extends libbase {
    /**
     * Processes the course and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param mixed $course to be processed.
     * @return step_response
     */
    public function process_course($processid, $instanceid, $course) {
        // Call the private function to process the videos.
        // It will return the proper return values itself.
        return self::process_ocvideos($processid, $instanceid, $course);
    }

    /**
     * Processes the course in status waiting and returns a response.
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
        // Call the private function to process the videos.
        // It will return the proper return values itself.
        return self::process_ocvideos($processid, $instanceid, $course);
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'opencast';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        // Initialize settings array.
        $settings = [];

        // Get the configured OC instances.
        $ocinstances = settings_api::get_ocinstances();

        // Iterate over the instances.
        foreach ($ocinstances as $instance) {
            // Instance setting for the 'ocworkflow' field.
            $settings[] = new instance_setting('ocworkflow_instance' . $instance->id, PARAM_ALPHANUMEXT);
            $settings[] = new instance_setting('ocisdelete' . $instance->id, PARAM_ALPHA);
            $settings[] = new instance_setting('ocremoveseriesmapping' . $instance->id, PARAM_ALPHA);
        }

        // Instance setting for the 'octrace' field.
        $settings[] = new instance_setting('octrace', PARAM_ALPHA);

        // Instance setting for the 'ocnotifyadmin' field.
        $settings[] = new instance_setting('ocnotifyadmin', PARAM_ALPHA);

        // Return settings array.
        return $settings;
    }

    /**
     * This method can be overridden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     */
    public function extend_add_instance_form_definition($mform) {
        // Prepare options array for select settings.
        $yesnooption = [LIFECYCLESTEP_OPENCAST_SELECT_YES => get_string('yes'),
                LIFECYCLESTEP_OPENCAST_SELECT_NO => get_string('no')];

        // Get the configured OC instances.
        $ocinstances = settings_api::get_ocinstances();

        // Iterate over the instances.
        foreach ($ocinstances as $instance) {
            // Add a heading for the instance.
            $headingstring = \html_writer::tag('h3', get_string(
                'mform_ocinstanceheading',
                'lifecyclestep_opencast',
                ['name' => $instance->name]
            ));
            $mform->addElement('html', $headingstring);

            // Get workflow choices of this OC instance.
            $tags = get_config('lifecyclestep_opencast', 'workflowtags');
            if (empty($tags)) {
                $tags = 'delete';
            }
            $workflowchoices = setting_helper::load_workflow_choices($instance->id, $tags);
            if (
                $workflowchoices instanceof \block_opencast\opencast_connection_exception ||
                $workflowchoices instanceof \tool_opencast\empty_configuration_exception
            ) {
                $opencasterror = $workflowchoices->getMessage();
                $workflowchoices = [null => get_string('adminchoice_noconnection', 'block_opencast')];
            }

            // Add the 'ocworkflow' field.
            $mform->addElement(
                'select',
                'ocworkflow_instance' . $instance->id,
                get_string('mform_ocworkflow', 'lifecyclestep_opencast'),
                $workflowchoices
            );
            $mform->addHelpButton('ocworkflow_instance' . $instance->id, 'mform_ocworkflow', 'lifecyclestep_opencast');

            // Add the 'isdelete' field.
            $mform->addElement(
                'select',
                'ocisdelete' . $instance->id,
                get_string('mform_ocisdelete', 'lifecyclestep_opencast'),
                $yesnooption
            );
            $mform->setDefault('ocisdelete' . $instance->id, LIFECYCLESTEP_OPENCAST_SELECT_NO);
            $mform->addHelpButton('ocisdelete' . $instance->id, 'mform_ocisdelete', 'lifecyclestep_opencast');

            // Add the 'ocremoveseriesmapping' field.
            $mform->addElement(
                'select',
                'ocremoveseriesmapping' . $instance->id,
                get_string('mform_ocremoveseriesmapping', 'lifecyclestep_opencast'),
                $yesnooption
            );
            $mform->setDefault('ocremoveseriesmapping' . $instance->id, LIFECYCLESTEP_OPENCAST_SELECT_YES);
            $mform->addHelpButton('ocremoveseriesmapping' . $instance->id, 'mform_ocremoveseriesmapping', 'lifecyclestep_opencast');
        }

        // Add a heading for the general settings.
        $headingstring = \html_writer::tag('h3', get_string('mform_generalsettingsheading', 'lifecyclestep_opencast'));
        $mform->addElement('html', $headingstring);

        // Add the 'octrace' field.
        $mform->addElement('select', 'octrace', get_string('mform_octrace', 'lifecyclestep_opencast'), $yesnooption);
        $mform->setDefault('octrace', LIFECYCLESTEP_OPENCAST_SELECT_NO);
        $mform->addHelpButton('octrace', 'mform_octrace', 'lifecyclestep_opencast');

        // Add the 'ocnotifyadmin' field.
        $mform->addElement('select', 'ocnotifyadmin', get_string('mform_ocnotifyadmin', 'lifecyclestep_opencast'), $yesnooption);
        $mform->setDefault('ocnotifyadmin', LIFECYCLESTEP_OPENCAST_SELECT_YES);
        $mform->addHelpButton('ocnotifyadmin', 'mform_ocnotifyadmin', 'lifecyclestep_opencast');
    }

    /**
     * Helper function to process the Opencast videos.
     * This function processes videos across all configured Opencast instances for a given course.
     * It handles both regular processing and deletion workflows, manages caching, rate limiting,
     * and notifications.
     *
     * @param int $processid The process ID of the respective lifecycle process.
     * @param int $instanceid The step instance ID.
     * @param mixed $course The course object to be processed.
     * @return step_response indicating whether processing is complete, waiting, or requires rollback.
     */
    private function process_ocvideos($processid, $instanceid, $course) {
        // Get caches.
        $ocworkflowscache = \cache::make('lifecyclestep_opencast', 'ocworkflows');

        // Get the step instance setting.
        $ocstepsettings = settings_manager::get_settings($instanceid, settings_type::STEP);
        // Get the step instance setting for octrace.
        $octrace = $ocstepsettings['octrace'];
        if ($octrace == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
            $octraceenabled = true;
        } else {
            $octraceenabled = false;
        }

        $logtrace = new log_helper($octraceenabled);

        // Get the step instance setting for ocnotifyadmin.
        $ocnotifyadmin = $ocstepsettings['ocnotifyadmin'];
        if ($ocnotifyadmin == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
            $ocnotifyadminenabled = true;
        } else {
            $ocnotifyadminenabled = false;
        }

        $notificationhelper = new notification_helper($ocnotifyadminenabled);

        // Get the global Opencast rate limiter setting.
        $ratelimiter = get_config('lifecyclestep_opencast', 'ratelimiter');
        if ($ratelimiter == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
            $ratelimiterenabled = true;
        } else {
            $ratelimiterenabled = false;
        }

        // Trace.
        $coursefullname = get_string('coursefullnameunknown', 'lifecyclestep_opencast');
        if ($course->fullname) {
            $coursefullname = $course->fullname;
        }
        $alangobj = (object)[
            'courseid' => $course->id,
            'coursefullname' => $coursefullname,
        ];
        $logtrace->print_mtrace(
            get_string('mtrace_start_process_course', 'lifecyclestep_opencast', $alangobj),
            '...',
            1
        );

        // Get the configured OC instances.
        $ocinstances = settings_api::get_ocinstances();

        // Iterate over the instances.
        foreach ($ocinstances as $ocinstance) {
            // Trace.
            $alangobj = (object)[
                'instanceid' => $ocinstance->id,
            ];
            $logtrace->print_mtrace(
                get_string('mtrace_start_process_ocinstance', 'lifecyclestep_opencast', $alangobj),
                '...',
                2
            );

            // Get the configured OC workflow.
            $ocworkflow = $ocstepsettings['ocworkflow_instance' . $ocinstance->id];

            // Get the step instance setting for octrace.
            $ocisdelete = $ocstepsettings['ocisdelete' . $ocinstance->id];
            if ($ocisdelete == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
                $ocisdelete = true;
            } else {
                $ocisdelete = false;
            }

            // Get the remove series mapping flag.
            $ocremoveseriesmapping = $ocstepsettings['ocremoveseriesmapping' . $ocinstance->id];
            if ($ocremoveseriesmapping == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
                $ocremoveseriesmappingenabled = true;
            } else {
                $ocremoveseriesmappingenabled = false;
            }

            // Get an APIbridge instance for this OCinstance.
            $apibridge = \block_opencast\local\apibridge::get_instance($ocinstance->id);

            // Check if workflow exists.
            $ocworkflows = [];
            if ($cacheresult = $ocworkflowscache->get($ocinstance->id)) {
                if ($cacheresult->expiry > time()) {
                    $ocworkflows = $cacheresult->ocworkflows;
                }
            }
            if (empty($ocworkflows)) {
                $ocworkflows = $apibridge->get_existing_workflows();
                $cacheobj = new \stdClass();
                $cacheobj->expiry = strtotime('tomorrow midnight');
                $cacheobj->ocworkflows = $ocworkflows;
                $ocworkflowscache->set($ocinstance->id, $cacheobj);
            }
            if (
                empty($ocworkflow) ||
                count($ocworkflows) == 0 ||
                !array_key_exists($ocworkflow, $ocworkflows)
            ) {
                // Trace.
                $alangobj = (object)[
                    'ocworkflow' => !empty($ocworkflow) ? $ocworkflow : '--',
                ];
                $logtrace->print_mtrace(
                    get_string('mtrace_error_workflow_notexist', 'lifecyclestep_opencast', $alangobj),
                    '...',
                    3
                );

                // Notify admin.
                $notificationhelper->notify_workflow_not_exists(
                    $course,
                    $ocinstance->id,
                    $ocworkflow
                );

                // Waiting for the itteration to be managed.
                // return step_response::waiting();
                // TODO: Ask if that is what that should actualy happen!
                continue;
            }

            // By default, the step response should be empty, to allow the process to continue.
            $stepresponse = '';
            // Decide whether the process should be for deletion or not.
            if ($ocisdelete) {
                // Trace.
                $logtrace->print_mtrace(
                    get_string('mtrace_start_process_deletion', 'lifecyclestep_opencast'),
                    '...',
                    3
                );

                $stepresponse = \lifecyclestep_opencast\opencaststep_process_delete::process(
                    $course,
                    $ocinstance->id,
                    $ocworkflow,
                    $instanceid,
                    $ocremoveseriesmappingenabled,
                    $octraceenabled,
                    $ocnotifyadminenabled,
                    $ratelimiterenabled
                );

                // Trace.
                $logtrace->print_mtrace(
                    get_string('mtrace_finish_process_deletion', 'lifecyclestep_opencast'),
                    '...',
                    3
                );
            } else {
                // Trace.
                $logtrace->print_mtrace(
                    get_string('mtrace_start_process_regular', 'lifecyclestep_opencast'),
                    '...',
                    3
                );

                $stepresponse = \lifecyclestep_opencast\opencaststep_process_default::process(
                    $course,
                    $ocinstance->id,
                    $ocworkflow,
                    $instanceid,
                    $octraceenabled,
                    $ocnotifyadminenabled,
                    $ratelimiterenabled
                );
                // Trace.
                $logtrace->print_mtrace(
                    get_string('mtrace_finish_process_regular', 'lifecyclestep_opencast'),
                    '...',
                    3
                );
            }

            // Evaluate step response, at this stage only waiting will be returned.
            if ($stepresponse === step_response::WAITING) {
                return step_response::waiting();
            }

            // Trace.
            $alangobj = (object) [
                'instanceid' => $ocinstance->id,
            ];
            $logtrace->print_mtrace(
                get_string('mtrace_finish_process_ocinstance', 'lifecyclestep_opencast', $alangobj),
                '...',
                2
            );
        }

        // Trace.
        $alangobj = (object) [
            'courseid' => $course->id,
        ];
        $logtrace->print_mtrace(
            get_string('mtrace_finish_process_course', 'lifecyclestep_opencast', $alangobj),
            '...',
            1
        );

        // Notify admin.
        $notificationhelper->notify_course_processed(
            $course,
            $ocworkflow
        );

        // Try to clear the cache of the processed videos for this course.
        $processedvideoscache = \cache::make('lifecyclestep_opencast', 'processedvideos');
        if ($processedvideoscache->has($instanceid)) {
            $processedvideoscacheresult = $processedvideoscache->get($instanceid);
            $stepprocessedvideos = $processedvideoscacheresult->stepprocessedvideos;
            if (isset($stepprocessedvideos[$course->id])) {
                unset($stepprocessedvideos[$course->id]);
            }
            $processedvideoscacheobj = new \stdClass();
            $processedvideoscacheobj->stepprocessedvideos = $stepprocessedvideos;
            $processedvideoscache->set($instanceid, $processedvideoscacheobj);
        }

        // At this point, all videos have been processed and the step is done.
        return step_response::proceed();
    }
}
