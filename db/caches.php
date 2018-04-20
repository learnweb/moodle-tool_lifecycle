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

defined('MOODLE_INTERNAL') || die();

/**
 * For values for default settings look at https://docs.moodle.org/dev/Cache_API#Ad-hoc_Caches.
 *
 * The cache has a key for each user (userid). The key stores a 1 in case the user has at least one course where he/she can
 * manage the lifecycle of the course. Otherwise a 0 is stored.
 * You might wonder why this is a application cache and not rather a session cache with only one key. The problem is that
 * certain events require to invalidate the cache. E.g. when a new role is assigned or a course is created.
 * When using session caches events triggered in a different session can not invalidated the caches in the current session.
 * Therefore, an application cache is used which can be invalidated across different sessions.
 * The main disadvantage is that the space in the application cache is occupied. However, since only one key is invalidated
 * per event, at least sessions from other users are not affected since they do not request the key.
 * */
$definitions = array(
    'coursesmanaged' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true)
);
