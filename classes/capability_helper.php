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
 * Class to support request for capability.
 * @package    tool_lifecycle
 * @copyright  2018 Tobias Reischmann, Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

defined('MOODLE_INTERNAL') || die;

class capability_helper {
    /**
     * Looks whether managedcourses are cached and returns an array of managed courses.
     * @return array|bool
     */
    public static function has_coursesmanaged() {
        global $USER;
        $cache = \cache::make('tool_lifecycle', 'coursesmanaged');
        $cachedcourses = $cache->get(0);
        if ($cachedcourses === false) {
            $courses = get_user_capability_course('tool/lifecycle:managecourses', $USER->id, false);
            // No course with capabilities.
            if ($courses === false) {
                $cache->set(0, 0);
            } else {
                $cache->set(0, 1);
            }
            $cachedcourses = $cache->get(0);

        }
        return $cachedcourses;
    }
}