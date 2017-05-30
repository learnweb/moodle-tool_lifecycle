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
 * Manager to retrive the lib of each subplugin.
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

defined('MOODLE_INTERNAL') || die();

class lib_manager {

    /**
     * Gets the trigger class of a subplugin lib.
     * @param string $subpluginname name of the subplugin
     * @return \tool_cleanupcourses\trigger\base
     */
    public static function get_trigger_lib($subpluginname) {
        return self::get_lib($subpluginname, 'trigger');
    }

    /**
     * Gets the step class of a subplugin lib.
     * @param string $subpluginname name of the subplugin
     * @return \tool_cleanupcourses\step\libbase
     */
    public static function get_step_lib($subpluginname) {
        return self::get_lib($subpluginname, 'step');
    }

    /**
     * Gets the base class of a subplugin lib with a specific type and name.
     * @param string $subpluginname name of the subplugin
     * @param string $subplugintype type of the subplugin (e.g. trigger, step)
     * @return
     */
    private static function get_lib($subpluginname, $subplugintype) {
        $triggerlist = \core_component::get_plugin_list('cleanupcourses' . $subplugintype);
        if (!array_key_exists($subpluginname, $triggerlist)) {
            return null;
        }
        require_once($triggerlist[$subpluginname].'/lib.php');
        $extendedclass = "tool_cleanupcourses\\$subplugintype\\$subpluginname";
        return new $extendedclass();
    }
}
