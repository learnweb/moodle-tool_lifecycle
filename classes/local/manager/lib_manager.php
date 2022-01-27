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
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

use tool_lifecycle\step\libbase;
use tool_lifecycle\trigger\base;
use tool_lifecycle\trigger\base_automatic;
use tool_lifecycle\trigger\base_manual;

/**
 * Manager to retrive the lib of each subplugin.
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib_manager {

    /**
     * Gets the trigger class of a subplugin lib.
     * @param string $subpluginname name of the subplugin
     * @return \tool_lifecycle\trigger\base
     */
    public static function get_trigger_lib($subpluginname) {
        return self::get_lib($subpluginname, 'trigger');
    }

    /**
     * Gets the lib class for an manual trigger subplugin.
     * @param string $subpluginname name of the subplugin
     * @return \tool_lifecycle\trigger\base_manual
     * @throws \coding_exception
     */
    public static function get_manual_trigger_lib($subpluginname) {
        $lib = self::get_lib($subpluginname, 'trigger');
        if (! $lib instanceof base_manual) {
            throw new \coding_exception("The requested trigger is no manual trigger.");
        }
        return $lib;
    }

    /**
     * Gets the lib class for an automatic trigger subplugin.
     * @param string $subpluginname name of the subplugin
     * @return \tool_lifecycle\trigger\base_automatic
     * @throws \coding_exception
     */
    public static function get_automatic_trigger_lib($subpluginname) {
        $lib = self::get_lib($subpluginname, 'trigger');
        if (! $lib instanceof base_automatic) {
            throw new \coding_exception("The requested trigger is no automatic trigger.");
        }
        return $lib;
    }



    /**
     * Gets the step class of a subplugin lib.
     * @param string $subpluginname name of the subplugin
     * @return \tool_lifecycle\step\libbase
     */
    public static function get_step_lib($subpluginname) {
        return self::get_lib($subpluginname, 'step');
    }

    /**
     * Gets the step class of a subplugin lib.
     * @param string $subpluginname name of the subplugin
     * @return \tool_lifecycle\step\interactionlibbase
     */
    public static function get_step_interactionlib($subpluginname) {
        return self::get_lib($subpluginname, 'step', 'interaction');
    }

    /**
     * Gets the base class of a subplugin lib with a specific type and name.
     * @param string $subpluginname name of the subplugin
     * @param string $subplugintype type of the subplugin (e.g. trigger, step)
     * @param string $libsubtype allows to query different lib classes.
     * @return null|base|libbase
     */
    private static function get_lib($subpluginname, $subplugintype, $libsubtype = '') {
        $triggerlist = \core_component::get_plugin_list('lifecycle' . $subplugintype);
        if (!array_key_exists($subpluginname, $triggerlist)) {
            return null;
        }
        $filename = $triggerlist[$subpluginname].'/'.$libsubtype.'lib.php';
        if (file_exists($filename)) {
            require_once($filename);
            $extendedclass = "tool_lifecycle\\$subplugintype\\$libsubtype$subpluginname";
            if (class_exists($extendedclass)) {
                return new $extendedclass();
            }
        }
        return null;
    }
}
