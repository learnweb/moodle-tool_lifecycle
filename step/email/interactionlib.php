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
 * Implementation for the interactions of the email step.
 *
 * @package lifecyclestep_email
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use tool_lifecycle\local\entity\process;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\response\step_interactive_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../interactionlib.php');
require_once(__DIR__ . '/lib.php');

/**
 * Implementation for the interactions of the email step.
 *
 * @package lifecyclestep_email
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interactionemail extends interactionlibbase {

    /** @var string Action string for triggering to keep a course. */
    const ACTION_KEEP = 'keep';

    /**
     * Returns the capability a user has to have to make decisions for a specific course.
     * @return string capability string.
     */
    public function get_relevant_capability() {
        return 'lifecyclestep/email:preventdeletion';
    }

    /**
     * Returns an array of interaction tools to be displayed to be displayed on the view.php
     * Every entry is itself an array which consist of three elements:
     *  'action' => an action string, which is later passed to handle_action
     *  'alt' => a string text of the button
     * @param process $process process the action tools are requested for
     * @return array of action tools
     * @throws \coding_exception
     */
    public function get_action_tools($process) {
        return [
            ['action' => self::ACTION_KEEP,
                'alt' => get_string('keep_course', 'lifecyclestep_email'),
            ],
        ];
    }

    /**
     * Returns the status message for the given process.
     * @param process $process process the status message is requested for
     * @return string status message
     * @throws \coding_exception
     */
    public function get_status_message($process) {
        return get_string('status_message_requiresattention', 'lifecyclestep_email');
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
     */
    public function handle_interaction($process, $step, $action = 'default') {
        if ($action == self::ACTION_KEEP) {
            return step_interactive_response::rollback();
        }
        return step_interactive_response::no_action();
    }

    /**
     * Returns the due date.
     * @param int $processid Id of the process.
     * @param int $stepid Id of the step instance.
     * @return string formatted date.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_due_date($processid, $stepid) {
        $process = process_manager::get_process_by_id($processid);
        $steps = step_manager::get_step_instances($process->workflowid);
        // Necessary to access steps through counting variable.
        $steps = array_values($steps);
        // Keep track of the delays for future email steps and sum them up.
        $date = $process->timestepchanged;
        for ($i = $process->stepindex; $i <= count($steps); $i++) {
            // The variable $i represents the stepindex. The index of $steps starts at 0.
            /* @var $step step_subplugin class entry of the subplugin step */
            $step = $steps[$i - 1];
            if ($step->subpluginname == 'email') {
                $settings = settings_manager::get_settings($step->id, settings_type::STEP);
                $date += $settings['responsetimeout'];
            }
        }
        // TODO default format -- seconds -> not in this class !
        return date('d.m.Y', $date);
    }

    /**
     * Returns the display name for the given action.
     * Used for the past actions table in view.php.
     * @param string $action Identifier of action
     * @param string $user html-link with username as text that refers to the user profile.
     * @return string action display name
     * @throws \coding_exception
     */
    public function get_action_string($action, $user) {
        return get_string('action_prevented_deletion', 'lifecyclestep_email', $user);
    }
}
