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
 * Manager for Subplugins
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses;

defined('MOODLE_INTERNAL') || die();

abstract class subplugin_manager {

    /**
     * Registers a subplugin.
     * This has to be called, when installing a subplugins!
     * @param string $subpluginname name of the subplugin
     */
    abstract function register($subpluginname);

    /**
     * Deregisters a trigger subplugin.
     * This has to be called, when uninstalling a subplugins!
     * @param string $subpluginname name of the subplugin
     */
    abstract function deregister($subpluginname);

    /**
     * Determines if there exists a subplugin for the given name and type
     * @param $subpluginname
     * @param $subplugintype
     * @return bool
     */
    protected function is_subplugin($subpluginname, $subplugintype) {
        $subplugintypes = \core_component::get_subplugins('tool_cleanupcourses');
        if (array_key_exists($subplugintype, $subplugintypes)) {
            $subplugins = $subplugintypes[$subplugintype];
            if (in_array($subpluginname, $subplugins)) {
                return true;
            }
        }
        return false;
    }

}
