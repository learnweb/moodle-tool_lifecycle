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
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

defined('MOODLE_INTERNAL') || die();

class interaction_manager {

    /**
     * Tells if the subplugin specifies an interaction interface.
     * @param string $subpluginname name of the subplugin
     * @return true, if the subplugin specifies an interaction interface; otherwise false.
     */
    public static function interaction_available($subpluginname) {
        if (lib_manager::get_step_interactionlib($subpluginname)) {
            return true;
        }
        return false;
    }

    /**
     * Called by the view.php for redirecting the interactions to the respective subplugin.
     * @param int $stepid id of the step instance
     * @param int $processid id of the process, the triggered action belongs to.
     * @param string $action action string
     */
    public static function handle_interaction($stepid, $processid, $action) {
        $step = step_manager::get_step_instance($stepid);
        $process = process_manager::get_process_by_id($processid);
        if (!$step) {
            throw new \invalid_parameter_exception(get_string('nostepfound', 'tool_cleanupcourses'));
        }
        if (!$process) {
            throw new \invalid_parameter_exception(get_string('noprocessfound', 'tool_cleanupcourses'));
        }
        $interactionlib = lib_manager::get_step_interactionlib($step->subpluginname);
        return $interactionlib->handle_interaction($process, $step, $action);
    }

    /**
     * Returns the capability a user has to have to make decisions for a specific course within the step.
     * @param string $subpluginname name of the step
     * @return string capability.
     */
    public static function get_relevant_capability($subpluginname) {
        $interactionlib = lib_manager::get_step_interactionlib($subpluginname);
        return $interactionlib->get_relevant_capability();
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
     *  'icon' => an icon string
     *  'alt' => a string description of the link
     * @param string $subpluginname name of the step
     * @return array of action tools
     */
    public static function get_action_tools($subpluginname) {
        $interactionlib = lib_manager::get_step_interactionlib($subpluginname);
        return $interactionlib->get_action_tools();
    }

}
