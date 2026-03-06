<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_samplestep\lifecycle;

use tool_lifecycle\step\interactionlibbase;

/**
 * Sample interaction class for lifecycle step plugin.
 *
 * @package    tool_lifecycle
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interaction extends interactionlibbase {

    /**
     * Mock method to get the relevant capability for the interaction.
     */
    public function get_relevant_capability() {
    }

    /**
     * Mock method to get the action tools for the interaction.
     *
     * @param \tool_lifecycle\local\entity\process $process
     */
    public function get_action_tools($process) {
    }

    /**
     * Mock method to get the status message for the interaction.
     *
     * @param \tool_lifecycle\local\entity\process $process
     * @return string status message
     */
    public function get_status_message($process) {
    }

    /**
     * Mock method to get the action string for the interaction.
     *
     * @param string $action The action being performed.
     * @param string $user html-link with username as text that refers to the user profile.
     */
    public function get_action_string($action, $user) {
    }

    /**
     * Mock method to handle the interaction.
     *
     * @param \tool_lifecycle\local\entity\process $process
     * @param \tool_lifecycle\local\entity\step_subplugin $step The lifecycle step object.
     * @param string $action The action being performed.
     */
    public function handle_interaction($process, $step, $action = 'default') {
    }
}
