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

namespace tool_lifecycle\local;

defined('MOODLE_INTERNAL') || die;

/**
 * Settings API for lifecycle.
 *
 * This static class is used by the lifecycle plugins, to fetch information about the settings of
 * the installed lifecycle plugins as well as of the plugin tool_lifecycle itself.
 *
 * @package    tool_lifecycle
 * @copyright  2025 Thomas Niedermaier, University of Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_api {

    /**
     * Make this class not instantiable.
     */
    private function __construct() {
    }


    public static function get_triggers() {
        $triggerlist = \core_component::get_plugin_list('lifecycletrigger');
        return $triggers;
    }

    public static function get_steps() {
        $steplist = \core_component::get_plugin_list('lifecyclestep');
        return $steps;
    }
}
