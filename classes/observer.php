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
 * @package    tool_lifecycle
 * @copyright  2018 Tobias Reischmann, Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

defined('MOODLE_INTERNAL') || die();

/**
 * Class observer - implements the function which react on changes which affect the cached value tool_lifecycle course managed.
 */
class observer {

    /**
     * Function which invalidates the tool_lifecycle course managed cache at the key for the corresponding user
     * when his/her role is changed.
     * @param \core\event\role_assigned || \core\event\role_deleted $event
     * @throws \coding_exception
     */
    public static function role_changed($event) {
        $component = 'tool_lifecycle';
        $area = 'coursesmanaged';
        $userid = $event->relateduserid;

        // FYI: Although this method is called invalidate it actually deletes the cache at the given key.
        \cache_helper::invalidate_by_definition($component, $area, array(), $userid);
    }
}