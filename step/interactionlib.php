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
 * Interface for the interactions of the subplugintype step
 * It has to be implemented by all subplugins that want to use the interaction view.
 *
 * @package tool_cleanupcourses
 * @subpackage step
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\step;

use tool_cleanupcourses\entity\process;
use tool_cleanupcourses\entity\step_subplugin;

defined('MOODLE_INTERNAL') || die();

abstract class interactionlibbase {

    /**
     * Returns the capability a user has to have to make decisions for a specific course.
     * @return string capability string.
     */
    public abstract function get_relevant_capability();

    /**
     * Returns if only the courses of a step instance should be shown or all courses for the submodule.
     * @return bool true, if only the courses of a step instance should be shown; otherwise false.
     */
    public function show_relevant_courses_instance_dependent() {
        return false;
    }

    /**
     * Returns an array of interaction tools to be displayed to be displayed on the view.php
     * Every entry is itself an array which consist of three elements:
     *  'action' => an action string, which is later passed to handle_action
     *  'icon' => an icon string
     *  'alt' => a string description of the link
     * @return array of action tools
     */
    public abstract function get_action_tools();

    /**
     * Called when a user triggered an action for a process instance.
     * @param process $process instance of the process the action was triggered upon.
     * @param step_subplugin $step instance of the step the process is currently in.
     * @param string $action action string
     */
    public abstract function handle_interaction($process, $step, $action);
}