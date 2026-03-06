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

namespace tool_sampletrigger\lifecycle;

use tool_lifecycle\trigger\base_automatic;

/**
 * Sample trigger class for lifecycle trigger plugin.
 *
 * @package    tool_lifecycle
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trigger extends base_automatic {

    /**
     * Returns the subplugin name.
     *
     * @return string
     */
    public function get_subpluginname() {
        return 'tool_sampletrigger';
    }

    /**
     * Returns the plugin name.
     *
     * @return string
     */
    public function get_plugin_name() {
        return "Sample trigger";
    }

    /**
     * Returns the plugin description.
     *
     * @return string
     */
    public function get_plugin_description() {
        return "Sample trigger";
    }

    /**
     * Checks the course for this trigger.
     *
     * @param object $course The course object.
     * @param int $triggerid The trigger ID.
     * @return null No action for this sample trigger.
     */
    public function check_course($course, $triggerid) {
        return null;
    }

}
