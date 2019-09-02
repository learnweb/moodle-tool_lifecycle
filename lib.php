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
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds a tool_lifecycle link to the course admin menu.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the tool
 * @param context $context The context of the course
 * @return void|null return null if we don't want to display the node.
 */
function tool_lifecycle_extend_navigation_course($navigation, $course, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course || $PAGE->course->id == SITEID) {
        return null;
    }

    if (!has_capability('tool/lifecycle:managecourses', $context)) {
        return null;
    }

    $url = null;
    $settingnode = null;

    $url = new moodle_url('/admin/tool/lifecycle/view.php', array(
        'contextid' => $context->id
    ));

    // Add the course life cycle link.
    $linktext = get_string('managecourses_link', 'tool_lifecycle');

    $node = navigation_node::create(
        $linktext,
        $url,
        navigation_node::NODETYPE_LEAF,
        'tool_lifecycle',
        'tool_lifecycle',
        new pix_icon('icon', $linktext, 'tool_lifecycle')
    );

    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
        $node->make_active();
    }

    $navigation->add_node($node);
}

/**
 * Map icons for font-awesome themes.
 */
function tool_lifecycle_get_fontawesome_icon_map() {
    return [
        'tool_lifecycle:icon' => 'fa-recycle',
        'tool_lifecycle:t/disable' => 'fa-hand-paper-o',
    ];
}