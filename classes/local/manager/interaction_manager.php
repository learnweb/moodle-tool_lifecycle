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
 * Manager to handle interactions by users
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

use tool_lifecycle\local\entity\process;
use tool_lifecycle\processor;
use tool_lifecycle\local\response\step_interactive_response;

/**
 * Manager to handle interactions by users
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interaction_manager {

    /**
     * Tells if the subplugin specifies an interaction interface.
     * @param string $subpluginname name of the subplugin
     * @return true, if the subplugin specifies an interaction interface; otherwise false.
     */
    public static function interaction_available($subpluginname) {
        if (lib_manager::get_step_interactionlib($subpluginname) !== null) {
            return true;
        }
        return false;
    }

    /**
     * Called by the view.php for redirecting the interactions to the respective subplugin.
     * @param int $stepid id of the step instance
     * @param int $processid id of the process, the triggered action belongs to.
     * @param string $action action string
     * @return boolean if true, interaction finished.
     *      If false, the current step is still processing and cares for displaying the view.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function handle_interaction($stepid, $processid, $action) {
        $step = step_manager::get_step_instance($stepid);
        $process = process_manager::get_process_by_id($processid);
        if (!$step) {
            throw new \invalid_parameter_exception(get_string('nostepfound', 'tool_lifecycle'));
        }
        if (!$process) {
            throw new \invalid_parameter_exception(get_string('noprocessfound', 'tool_lifecycle'));
        }
        $interactionlib = lib_manager::get_step_interactionlib($step->subpluginname);
        $response = $interactionlib->handle_interaction($process, $step, $action);

        self::save_interaction($process, $action);

        switch ($response) {
            case step_interactive_response::still_processing():
                return false;
                break;
            case step_interactive_response::no_action():
                break;
            case step_interactive_response::proceed():
                $processor = new processor();
                return $processor->process_course_interactive($processid);
                break;
            case step_interactive_response::rollback():
                delayed_courses_manager::set_course_delayed_for_workflow($process->courseid, true, $process->workflowid);
                process_manager::rollback_process($process);
                break;
        }
        return true;
    }

    /**
     * Save the interaction that happened in the database.
     * @param process $process
     * @param string $action Action name.
     * @throws \dml_exception
     */
    public static function save_interaction($process, $action) {
        global $DB, $USER;
        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->time = time();
        $record->courseid = $process->courseid;
        $record->processid = $process->id;
        $record->workflowid = $process->workflowid;
        $record->stepindex = $process->stepindex;
        $record->action = $action;
        $DB->insert_record('tool_lifecycle_action_log', $record);
    }

    /**
     * Returns the capability a user has to have to make decisions for a specific course within the step.
     * @param string $subpluginname name of the step
     * @return string|false name of the capability or false if the step has no interaction defined.
     */
    public static function get_relevant_capability($subpluginname) {
        $interactionlib = lib_manager::get_step_interactionlib($subpluginname);
        if ($interactionlib) {
            return $interactionlib->get_relevant_capability();
        }
        return false;
    }

    /**
     * Returns for this submodule if only the courses of a step instance should be shown
     * or all courses for the submodule.
     * @param string $subpluginname name of the step
     * @return bool true, if only the courses of a step instance should be shown; otherwise false.
     */
    public static function show_relevant_courses_instance_dependent($subpluginname) {
        $interactionlib = lib_manager::get_step_interactionlib($subpluginname);
        return $interactionlib->show_relevant_courses_instance_dependent();
    }

    /**
     * Returns an array of interaction tools to be displayed to be displayed on the view.php
     * Every entry is itself an array which consist of three elements:
     *  'action' => an action string, which is later passed to handle_action
     *  'alt' => a string text of the button
     * @param string $subpluginname name of the step
     * @param int $processid if of the process the action tools are requested for
     * @return array of action tools
     * @throws \invalid_parameter_exception
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_action_tools($subpluginname, $processid) {
        $interactionlib = lib_manager::get_step_interactionlib($subpluginname);
        $process = process_manager::get_process_by_id($processid);
        if (!$process) {
            throw new \invalid_parameter_exception(get_string('noprocessfound', 'tool_lifecycle'));
        }
        return $interactionlib->get_action_tools($process);
    }

    /**
     * Returns the status message for the given process.
     * @param int $processid id of the process the status message is requested for
     * @return string status message
     * @throws \invalid_parameter_exception
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_process_status_message($processid) {
        $process = process_manager::get_process_by_id($processid);
        if (!$process) {
            throw new \invalid_parameter_exception(get_string('noprocessfound', 'tool_lifecycle'));
        }

        if ($process->stepindex == 0) {
            // TODO: Rethink behaviour for multiple triggers.
            $trigger = trigger_manager::get_triggers_for_workflow($process->workflowid)[0];
            $triggerlib = lib_manager::get_trigger_lib($trigger->subpluginname);
            return $triggerlib->get_status_message();
        } else {
            $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex);
            $interactionlib = lib_manager::get_step_interactionlib($step->subpluginname);
            if ($interactionlib === null) {
                return get_string("workflow_is_running", "tool_lifecycle");
            }

            return $interactionlib->get_status_message($process);
        }
    }

}
