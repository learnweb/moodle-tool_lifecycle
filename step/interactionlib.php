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
 * Interface for the interactions of the subplugintype step.
 *
 * It has to be implemented by all subplugins that want to use the interaction view.
 * @package tool_lifecycle
 * @subpackage step
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use core_user;
use MongoDB\BSON\Timestamp;
use tool_lifecycle\local\entity\process;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\response\step_interactive_response;

/**
 * Interface for the interactions of the subplugintype step.
 *
 * It has to be implemented by all subplugins that want to use the interaction view.
 * @package tool_lifecycle
 * @subpackage step
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class interactionlibbase {

    /**
     * Returns the capability a user has to have to make decisions for a specific course.
     * @return string capability string.
     */
    abstract public function get_relevant_capability();

    /**
     * Returns if only the courses of a step instance should be shown or all courses for the submodule.
     * @return bool true, if only the courses of a step instance should be shown; otherwise false.
     */
    public function show_relevant_courses_instance_dependent() {
        return false;
    }

    /**
     * Returns an array of interaction tools to be displayed to be displayed on the view.php for the given process
     * Every entry is itself an array which consist of three elements:
     *  'action' => an action string, which is later passed to handle_action
     *  'alt' => a string text of the button
     * @param process $process process the action tools are requested for
     * @return array of action tools
     */
    abstract public function get_action_tools($process);


    /**
     * Returns the status message for the given process.
     * @param process $process process the status message is requested for
     * @return string status message
     */
    abstract public function get_status_message($process);

    /**
     * Returns the display name for the given action.
     * Used for the past actions table in view.php.
     * @param string $action Identifier of action
     * @param string $user html-link with username as text that refers to the user profile.
     * @return string action display name
     */
    abstract public function get_action_string($action, $user);

    /**
     * Called when a user triggered an action for a process instance.
     * @param process $process instance of the process the action was triggered upon.
     * @param step_subplugin $step instance of the step the process is currently in.
     * @param string $action action string. The function is called with 'default', during interactive processing.
     * @return step_interactive_response defines if the step still wants to process this course
     *      - proceed: the step has finished and respective controller class can take over.
     *      - stillprocessing: the step still wants to process the course and is responsible for rendering the site.
     *      - noaction: the action is not defined for the step.
     *      - rollback: the step has finished and respective controller class should rollback the process.
     */
    abstract public function handle_interaction($process, $step, $action = 'default');

    /**
     * Returns the due date.
     * @param int $processid Id of the process.
     * @param int $stepid Id of the step instance.
     * @return null | string formatted due date.
     */
    public function get_due_date($processid, $stepid) {
        return null;
    }

    /**
     * Method to limit the actions to specific users. Returns all actions by default so if not overwritten all actions are available
     * to any user. You can overwrite this method to filter the $actionstrings array according to your needs to limit the actions
     * shown to the given user.
     *
     * Care: When overwriting this method you want to make sure that at least one user can use at least one action or have a timeout
     * in your step to avoid any deadlocked workflows.
     *
     * @param array $actionstrings array of action strings. These should match the strings with the key 'action'
     *  in {@see get_action_tools} return array.
     * @param int $userid the id of the user for whom the returned actions should be visible
     * @param int $courseid the id of the currently handled course
     * @return array the (possibly) filtered $actions array containing the strings of the actions which should be available to the
     *  given user
     */
    public function get_available_actions_for_user(array $actionstrings, int $userid, int $courseid) : array {
        return $actionstrings;
    }
}
