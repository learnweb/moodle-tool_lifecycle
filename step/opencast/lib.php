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

use core_cache\cache;
use core\output\html_writer;
use lifecyclestep_opencast\process_status_helper;
use tool_lifecycle\step\interactionopencast;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;
use tool_opencast\local\settings_api;
use block_opencast\setting_helper;
use lifecyclestep_opencast\notification_helper;
use lifecyclestep_opencast\log_helper;
use lifecyclestep_opencast\report_helper;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/interactionlib.php');

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
    /** @var string Default Opencast workflow tags. */
    const DEFAULT_OPENCAST_WORKFLOW_TAGS = 'delete,archive';

    /** @var string Response message key. */
    const RESPONSE_MESSAGE = 'message';

    /** @var string Response status key. */
    const RESPONSE_STATUS = 'status';

    /**
     * Returns the string of the specific icon for this step.
     * @return string icon string
     */
    public function get_icon() {
        return 'i/messagecontentvideo';
    }

    /**
     * Process a course for this lifecycle step.
     *
     * This method reads the decision for the current process and instance,
     * handles pending and abort decisions, and forwards confirmed requests to
     * the Opencast video processing workflow.
     *
     * The returned step_response indicates whether the step is complete,
     * still waiting for administrator confirmation, or requires rollback.
     *
     * @param int $processid The lifecycle process id.
     * @param int $instanceid The step instance id.
     * @param object $course The course object being processed.
     * @return step_response
     */
    public function process_course($processid, $instanceid, $course) {
        $logtrace = new log_helper(true);
        $decision = $this->read_decision($course->id, $processid, $instanceid);

        // It is new and has no status record yet, therefore, we have to ask admin.
        if (empty($decision)) {
            // Force put the process into pending in order to wait for admins to decide.
            $decision = process_status_helper::DECISION_PENDING;
            $this->save_state_info(
                $processid,
                $instanceid,
                get_string('interaction_state_info_first_spin', 'lifecyclestep_opencast')
            );
            $this->save_status(
                $course->id,
                $processid,
                $instanceid,
                process_status_helper::map_status_by_decision($decision),
                $decision
            );
            $logtrace->print_mtrace(
                "Processing course (ID: {$course->id}) is waiting for administration decision..."
            );
            return step_response::waiting();
        }

        // Handling aborted, so that we finish this process as proceed without doing anything.
        if ($decision === process_status_helper::DECISION_ABORT) {
            $this->save_status(
                $course->id,
                $processid,
                $instanceid,
                process_status_helper::map_status_by_decision($decision),
                $decision
            );
            $logtrace->print_mtrace(
                "Processing course (ID: {$course->id}) has been aborted and it finishes its processing here."
            );
            return step_response::proceed();
        }

        // Now we handle pending decision, by simply putting this into waiting state until admin decide.
        if ($decision === process_status_helper::DECISION_PENDING) {
            $logtrace->print_mtrace(
                "Processing course (ID: {$course->id}) is still pending administrator's decision..."
            );
            return step_response::waiting();
        }

        // Now we reach here, meaning the decision is "CONFIRM" and we can proceed with processing based on status.
        $status = $this->read_status($course->id, $processid, $instanceid);

        // If status is processing, meaning it has been just confirmed by admin.
        if ($status === process_status_helper::STATUS_PROCESSING) {
            $stepresponse = $this->process_ocvideos($processid, $instanceid, $course);
            // Separating the response status and message.
            $stepresponsestatus = $stepresponse[self::RESPONSE_STATUS];
            $stepresponsemessage = $stepresponse[self::RESPONSE_MESSAGE];
            // When message is empty, we fallback to a default one.
            if (empty($stepresponsemessage)) {
                $stepresponsemessage = get_string('interaction_state_info_default', 'lifecyclestep_opencast');
            }

            // Now refresh the status to be checked in the next step.
            $status = process_status_helper::STATUS_WAITING; // Default to waiting.
            if ($stepresponsestatus === step_response::PROCEED) {
                $status = process_status_helper::STATUS_COMPLETED;
            }
        }

        // If status is completed, meaning everything went good and we close the process.
        if ($status === process_status_helper::STATUS_COMPLETED) {
            $this->save_status(
                $course->id,
                $processid,
                $instanceid,
                $status,
                $decision
            );

            return step_response::proceed();
        }

        // If we reach here, meaning the status is "WAITING" and needs the admins decision.
        $decision = process_status_helper::DECISION_PENDING;
        $this->save_state_info(
            $processid,
            $instanceid,
            $stepresponsemessage
        );
        $this->save_status(
            $course->id,
            $processid,
            $instanceid,
            process_status_helper::map_status_by_decision($decision),
            $decision
        );

        $logtrace->print_mtrace(
            "Processing course (ID: {$course->id}) requires administrator's decision..."
        );

        return step_response::waiting();
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
        // Since we are now having the admin confirmation feature,
        // processing waiting course means that admin just decided.
        return $this->process_course($processid, $instanceid, $course);
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
            // Instance setting for the 'ocworkflowtags' field.
            $settings[] = new instance_setting('ocworkflowtags' . $instance->id, PARAM_TAGLIST, false);
            // Instance setting for the 'ocworkflow' field.
            $settings[] = new instance_setting('ocworkflow_instance' . $instance->id, PARAM_ALPHANUMEXT, false);
            $settings[] = new instance_setting('ocisdelete' . $instance->id, PARAM_ALPHA, false);
            $settings[] = new instance_setting('ocremoveseriesmapping' . $instance->id, PARAM_ALPHA, false);
        }

        // Instance setting for the 'ocdryrun' field.
        $settings[] = new instance_setting('ocdryrun', PARAM_ALPHA, true);

        // Instance setting for the 'octrace' field.
        $settings[] = new instance_setting('octrace', PARAM_ALPHA, true);

        // Instance setting for the 'ocnotifyadmin' field.
        $settings[] = new instance_setting('ocnotifyadmin', PARAM_ALPHA, true);

        // Instance setting for the 'ocratelimiter' field.
        $settings[] = new instance_setting('ocratelimiter', PARAM_ALPHA, true);

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
        $yesnooption = [
            LIFECYCLESTEP_OPENCAST_SELECT_YES => get_string('yes'),
            LIFECYCLESTEP_OPENCAST_SELECT_NO => get_string('no'),
        ];

        // Get the configured OC instances.
        $ocinstances = settings_api::get_ocinstances();

        // Iterate over the instances.
        foreach ($ocinstances as $instance) {
            // Add a heading for the instance.
            $headingstring = html_writer::tag('h3', get_string(
                'mform_ocinstanceheading',
                'lifecyclestep_opencast',
                ['name' => $instance->name]
            ));
            $mform->addElement('html', $headingstring);

            // Workflow tags for this instance.
            $workflowtagsid = "ocworkflowtags{$instance->id}";
            $mform->addElement(
                'text',
                $workflowtagsid,
                get_string('mform_workflowtags', 'lifecyclestep_opencast')
            );
            $mform->setType($workflowtagsid, PARAM_RAW);
            $mform->setDefault($workflowtagsid, self::DEFAULT_OPENCAST_WORKFLOW_TAGS);
            $mform->addHelpButton(
                $workflowtagsid,
                'mform_workflowtags',
                'lifecyclestep_opencast'
            );

            $workflowchoices = [null => get_string('adminchoice_noconnection', 'block_opencast')];

            // Add the 'ocworkflow' field.
            $ocworkflowelementid = "ocworkflow_instance{$instance->id}";
            $mform->addElement(
                'select',
                $ocworkflowelementid,
                get_string('mform_ocworkflow', 'lifecyclestep_opencast'),
                $workflowchoices
            );
            $mform->addHelpButton($ocworkflowelementid, 'mform_ocworkflow', 'lifecyclestep_opencast');
            // We make the ocworkflow required!
            $mform->addRule($ocworkflowelementid, get_string('required'), 'required');

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
        $headingstring = html_writer::tag('h3', get_string('mform_generalsettingsheading', 'lifecyclestep_opencast'));
        $mform->addElement('html', $headingstring);

        // Rate Limiter for the opencast instance.
        $dryrunid = "ocdryrun";
        $mform->addElement(
            'select',
            $dryrunid,
            get_string('mform_dryrun', 'lifecyclestep_opencast'),
            $yesnooption
        );
        $mform->setDefault($dryrunid, LIFECYCLESTEP_OPENCAST_SELECT_NO);
        $mform->addHelpButton(
            $dryrunid,
            'mform_dryrun',
            'lifecyclestep_opencast'
        );

        // Add the 'octrace' field.
        $mform->addElement(
            'select',
            'octrace',
            get_string('mform_octrace', 'lifecyclestep_opencast'),
            $yesnooption
        );
        $mform->setDefault('octrace', LIFECYCLESTEP_OPENCAST_SELECT_NO);
        $mform->addHelpButton('octrace', 'mform_octrace', 'lifecyclestep_opencast');

        // Add the 'ocnotifyadmin' field.
        $mform->addElement('select', 'ocnotifyadmin', get_string('mform_ocnotifyadmin', 'lifecyclestep_opencast'), $yesnooption);
        $mform->setDefault('ocnotifyadmin', LIFECYCLESTEP_OPENCAST_SELECT_YES);
        $mform->addHelpButton('ocnotifyadmin', 'mform_ocnotifyadmin', 'lifecyclestep_opencast');

        // Rate Limiter for the opencast instance.
        $ratelimiterid = "ocratelimiter";
        $mform->addElement(
            'select',
            $ratelimiterid,
            get_string('mform_ratelimiter', 'lifecyclestep_opencast'),
            $yesnooption
        );
        $mform->setDefault($ratelimiterid, LIFECYCLESTEP_OPENCAST_SELECT_NO);
        $mform->addHelpButton(
            $ratelimiterid,
            'mform_ratelimiter',
            'lifecyclestep_opencast'
        );
    }

    /**
     * Using this method as to update the relationship between settings after the form is loaded,
     * so that in our case we can retrieve the opencast workflows based on the opencast workflow tags from each oc instance setting.
     * @param \MoodleQuickForm $mform
     * @param array $settings
     * @return void
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings): void {
        $ocinstances = settings_api::get_ocinstances();

        // Iterate over the instances.
        foreach ($ocinstances as $instance) {
            $workflowtagsid = "ocworkflowtags{$instance->id}";
            // Get workflow choices of this OC instance.
            $tags = self::DEFAULT_OPENCAST_WORKFLOW_TAGS;
            if (!empty($settings[$workflowtagsid])) {
                $tags = $settings[$workflowtagsid];
            }

            $workflowchoices = setting_helper::load_workflow_choices($instance->id, $tags);
            if (
                $workflowchoices instanceof \tool_opencast\exception\opencast_api_response_exception ||
                $workflowchoices instanceof \tool_opencast\empty_configuration_exception
            ) {
                $opencasterror = $workflowchoices->getMessage();
                $workflowchoices = [null => get_string('adminchoice_noconnection', 'block_opencast')];
            }

            $workflowelementid = "ocworkflow_instance{$instance->id}";

            $workflowselectelement = $mform->getElement($workflowelementid);
            $workflowselectelement->removeOptions();
            foreach ($workflowchoices as $optvalue => $opttext) {
                $workflowselectelement->addOption($opttext, $optvalue);
            }
        }

        // Handeling information regarding dry run.
        $ocdryrun = $settings['ocdryrun'] ?? null;
        if ($ocdryrun === LIFECYCLESTEP_OPENCAST_SELECT_YES) {
            \core\notification::info(get_string('mform_dryrun_info', 'lifecyclestep_opencast'));
        }
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
        $ocworkflowscache = cache::make('lifecyclestep_opencast', 'ocworkflows');

        // Get the step instance setting.
        $ocstepsettings = settings_manager::get_settings($instanceid, settings_type::STEP);
        // Get the step instance setting for octrace.
        $octrace = $ocstepsettings['octrace'];
        if ($octrace == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
            $octraceenabled = true;
        } else {
            $octraceenabled = false;
        }

        // Preparing dry run config.
        $ocdryrun = $ocstepsettings['ocdryrun'];
        if ($ocdryrun == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
            $ocdryrunenabled = true;
            // Force log trace in dry run.
            $octraceenabled = true;
        } else {
            $ocdryrunenabled = false;
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

        // Get step instance setting for rate limiter.
        $ratelimiter = $ocstepsettings['ocratelimiter'] ?? LIFECYCLESTEP_OPENCAST_SELECT_NO;
        if ($ratelimiter == LIFECYCLESTEP_OPENCAST_SELECT_YES) {
            $ratelimiterenabled = true;
        } else {
            $ratelimiterenabled = false;
        }

        // Trace dry run.
        if ($ocdryrunenabled) {
            $logtrace->print_mtrace(
                get_string('mtrace_start_process_with_dryrun', 'lifecyclestep_opencast'),
                '***',
                1
            );
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

                // In case there is no proper workflow, we skip the Opencast intance processing!
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
                    $ratelimiterenabled,
                    $ocdryrunenabled
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
                    $ratelimiterenabled,
                    $ocdryrunenabled
                );
                // Trace.
                $logtrace->print_mtrace(
                    get_string('mtrace_finish_process_regular', 'lifecyclestep_opencast'),
                    '...',
                    3
                );
            }

            // Evaluate step response, at this stage only waiting will be returned.
            if ($stepresponse[self::RESPONSE_STATUS] === step_response::WAITING) {
                return $stepresponse;
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
        $processedvideoscache = cache::make('lifecyclestep_opencast', 'processedvideos');
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
        if (empty($stepresponse)) {
            $stepresponse[self::RESPONSE_STATUS] = step_response::PROCEED;
        }
        return $stepresponse;
    }

    /**
     * Saves the state info process data.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @param string $value The value of the record.
     * @return void
     */
    private function save_state_info(int $processid, int $stepid, string $value) {
        $this->save_process_data(
            $processid,
            $stepid,
            interactionopencast::PROC_DATA_STATE_INFO_KEY,
            $value
        );
    }

    /**
     * Using general process data manager to save or update data for the process in the step.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @param string $key The key of the record.
     * @param string $value The value of the record.
     * @return void
     */
    private function save_process_data(int $processid, int $stepid, string $key, string $value) {
        process_data_manager::set_process_data(
            $processid,
            $stepid,
            $key,
            $value
        );
    }

    /**
     * Saves or updates opencast step process status record.
     * @param int $courseid The course ID.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @param string $status The status to be saved.
     * @param string $decision The decision to be saved.
     * @return void
     */
    private function save_status(int $courseid, int $processid, int $stepid, string $status, string $decision) {
        process_status_helper::save_or_update(
            $courseid,
            $processid,
            $stepid,
            $status,
            $decision
        );
    }

    /**
     * Reads the opencast step process status.
     * @param int $courseid The course ID.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @return mixed
     */
    private function read_status(int $courseid, int $processid, int $stepid) {
        $record = process_status_helper::read(
            $courseid,
            $processid,
            $stepid,
            'status'
        );
        return $record ? $record->status : null;
    }

    /**
     * Read the decision for the given process and step.
     *
     * If no record exists for the current process, it checks for an existing
     * aborted process for the course and step, removes it, and returns its decision.
     *
     * @param int $courseid The course ID.
     * @param int $processid The process ID.
     * @param int $stepid The step ID.
     * @return string|null The decision value or null if not found.
     */
    private function read_decision(int $courseid, int $processid, int $stepid) {
        $record = process_status_helper::read(
            $courseid,
            $processid,
            $stepid,
            'decision'
        );

        // In case, a process has been aborted, we need to pass PROCEED response,
        // therefore, the lifecycle removes the data and by next run, it tries to
        // recreate a new process for this aborted course if there are any pending
        // processes in the step for other courses.
        // We handle this scenario here, by simple looking to the aborted process
        // for this step in the course that may have different processid!
        if (empty($record)) { // So it is empty!
            $existingrecord = process_status_helper::read_by_course($courseid, $stepid, 'decision,processid');

            // Get rid of existing record, after we get the values.
            if ($existingrecord) {
                $record = $existingrecord;
                process_status_helper::remove_entry($courseid, (int) $existingrecord->processid, $stepid);
            }
        }

        return $record ? $record->decision : null;
    }

    /**
     * Return a standardized response array for step processing.
     *
     * If the report has information, it adds a completion line and notifies administrators.
     *
     * @param string $status The response status.
     * @param report_helper $report The report instance.
     * @param string $message The message.
     * @return array<string, string>
     */
    public static function return_result(string $status, report_helper $report, string $message = '') {
        if ($report->has_info()) {
            $report->add_info_line('Processing finished with status: ' . $status);
            $notificationhelper = new notification_helper(true);
            $notificationhelper->notify_report($report->get_info());
        }
        return [
            self::RESPONSE_STATUS => $status,
            self::RESPONSE_MESSAGE => $message,
        ];
    }
}
