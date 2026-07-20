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
 * Interaction lib for Opencast step.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V. <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use lifecyclestep_opencast\process_status_helper;
use tool_lifecycle\local\entity\process;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\response\step_interactive_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../interactionlib.php');
require_once(__DIR__ . '/lib.php');

/**
 * Interaction lib for Opencast step.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V. <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interactionopencast extends interactionlibbase {
    /** @var string Action string to get the admin to decide how to proceed. */
    const ACTION_DECIDE = 'decide';

    /** @var string Action string for when the decision is made. */
    const ACTION_RESULT = 'result';

    /** @var string The process data key for process state info. */
    const PROC_DATA_STATE_INFO_KEY = 'info';

    /**
     * Returns the capability a user has to have to make decisions for a specific course.
     * Admins with site config should only be able to make these decision for this step!
     * @return string capability string.
     */
    public function get_relevant_capability() {
        return 'block/opencast:deleteevent';
    }

    /**
     * Returns if only the courses of a step instance should be shown or all courses for the submodule.
     * We only need the relevant course to the step instance to be shown here!
     * @return bool true, if only the courses of a step instance should be shown; otherwise false.
     */
    public function show_relevant_courses_instance_dependent() {
        return true;
    }

    /**
     * Returns an array of interaction tools to be displayed to be displayed on the view.php
     * Every entry is itself an array that consists of two elements:
     *  'action' => an action string, which is later passed to handle_action
     *  'alt' => a string text of the button
     * @param process $process process the action tools are requested for
     * @return array of action tools
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_action_tools($process) {
        $actions = [];
        $decisionaction = [
            'action' => self::ACTION_DECIDE,
            'alt' => get_string('interaction_decision_action_alt', 'lifecyclestep_opencast'),
        ];
        $status = $this->read_status($process);
        if (empty($status) || $status === process_status_helper::STATUS_WAITING) {
            $actions[] = $decisionaction;
        }
        return $actions;
    }

    /**
     * Returns the status message for the given process.
     * @param process $process process the status message is requested for
     * @return string status message
     */
    public function get_status_message($process) {
        $stringidentifier = 'interaction_status_message';
        $status = $this->read_status($process);
        if (!empty($status) && $status != process_status_helper::STATUS_WAITING) {
            $stringidentifier = "interaction_status_message_{$status}";
        }
        return get_string($stringidentifier, 'lifecyclestep_opencast');
    }

    /**
     * Called when a user triggered an action for a process instance.
     * @param process $process instance of the process the action was triggered upon.
     * @param step_subplugin $step instance of the step the process is currently in.
     * @param string $action action string
     * @return step_interactive_response defines if the step still wants to process this course
     *      - proceed: the step has finished and respective controller class can take over.
     *      - stillprocessing: the step still wants to process the course and is responsible for rendering the site.
     *      - noaction: the action is not defined for the step.
     *      - rollback: the step has finished and respective controller class should rollback the process.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function handle_interaction($process, $step, $action = 'default') {
        global $PAGE;
        $stateinfo = $this->get_process_data_by_key($process, self::PROC_DATA_STATE_INFO_KEY);
        if (empty($stateinfo)) {
            $stateinfo = get_string('interaction_state_info_default', 'lifecyclestep_opencast');
        }
        $courseid = $process->courseid;
        $form = new \lifecyclestep_opencast\interaction_form(
            $PAGE->url,
            $courseid,
            $process->id,
            $step->id,
            $stateinfo
        );
        if ($form->is_cancelled()) {
            return step_interactive_response::no_action();
        }
        if ($data = $form->get_submitted_data()) {
            $processid = $data->processid;
            $courseid = $data->courseid;
            $stepid = $data->stepid;
            $decision = $data->decision;
            if (!in_array($decision, process_status_helper::DECISION_VALUES)) {
                $decision = process_status_helper::DECISION_PENDING;
            }
            $status = process_status_helper::map_status_by_decision($decision);
            $this->save_status($courseid, $processid, $stepid, $status, $decision);
            return step_interactive_response::no_action();
        }

        if ($action == self::ACTION_DECIDE || $action == 'default') {
            $this->save_status(
                $courseid,
                $process->id,
                $step->id,
                process_status_helper::STATUS_WAITING,
                process_status_helper::DECISION_PENDING
            );
            $this->render_form($form);
            return step_interactive_response::still_processing();
        }

        return step_interactive_response::no_action();
    }

    /**
     * Renders the decision making form including respective headers and footers.
     * @param \moodleform $mform Form to be rendered.
     */
    private function render_form($mform) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('tool_lifecycle');

        echo $renderer->header();
        $mform->display();
        echo $renderer->footer();
    }

    /**
     * Returns the display name for the given action.
     * Used for the past actions table in view.php.
     * @param string $action Identifier of action
     * @param string $user Html-link with username as text that refers to the user profile.
     * @return string action display name
     * @throws \coding_exception
     */
    public function get_action_string($action, $user) {
        return get_string('interaction_give_action_decided', 'lifecyclestep_opencast', $user);
    }

    /**
     * Finds and returns the process data value by key.
     * @param process $process
     * @param string $key
     * @return string|null
     */
    private function get_process_data_by_key(process $process, string $key) {
        $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex);
        $value = process_data_manager::get_process_data($process->id, $step->id, $key);
        return $value;
    }

    /**
     * Saves or updates the opencast step process status record.
     * @param int $courseid
     * @param int $processid
     * @param int $stepid
     * @param string $status
     * @param string $decision
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
     * Reads the opencast step process status record for the process.
     * @param process $process
     * @return mixed
     */
    private function read_status(process $process) {
        $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex);
        $record = process_status_helper::read(
            $process->courseid,
            $process->id,
            $step->id,
            'status'
        );
        return $record ? $record->status : null;
    }
}
