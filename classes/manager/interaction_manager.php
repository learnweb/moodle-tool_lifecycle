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
     * Called by the view.php for redirecting the interactions to the respective subplugin.
     * @param int $subpluginid id of the step instance
     * @param string $action action string
     */
    public static function handle_interaction($subpluginid, $action) {

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

}
