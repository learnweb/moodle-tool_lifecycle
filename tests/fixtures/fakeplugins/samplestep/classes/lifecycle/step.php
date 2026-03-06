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

use tool_lifecycle\step\libbase;


/**
 * Sample step class for lifecycle step plugin.
 *
 * @package    tool_lifecycle
 * @copyright  2026 Scott Verbeek <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step extends libbase {
    /**
     * Returns the subplugin name.
     *
     * @return string
     */
    public function get_subpluginname() {
        return 'tool_samplestep';
    }

    /**
     * Returns the plugin name.
     *
     * @return string
     */
    public function get_plugin_name() {
        return "Sample step";
    }

    /**
     * Returns the plugin description.
     *
     * @return string
     */
    public function get_plugin_description() {
        return "Sample step plugin";
    }

    /**
     * Processes the course for this step.
     *
     * @param int $processid The process ID.
     * @param int $instanceid The instance ID.
     * @param object $course The course object.
     * @return null No action for this sample step.
     */
    public function process_course($processid, $instanceid, $course) {
        return null;
    }

}
