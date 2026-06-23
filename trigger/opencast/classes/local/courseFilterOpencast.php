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
 * Trigger subplugin, which triggers on specific jku needs only.
 *
 * @package    lifecycletrigger_opencast
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecycletrigger_opencast\local;

defined('MOODLE_INTERNAL') || die();

class courseFilterOpencast {
    // Test code
    public function test() {
        // Start Test
        echo("<br>Test class: <strong>courseFilter</strong> (of <u>lifecycle trigger</u>: <strong>opencast</strong>)<br>");

        // Get all courses & reduce them to an array with course ids
        $course_ids = array_map(function($course) { return $course->id; }, get_courses());
        $tmp = count($course_ids);
        echo("<hr><br>All Courses: <strong>" . $tmp . "</strong>");

        // Get courses with opencast block
        $courses_with_opencast = $this->get_opencast_courses();
        echo("<hr><br>Courses with opencast block: " . count($courses_with_opencast));
        foreach ($courses_with_opencast as $courseid) {
            echo("<br>&nbsp;&nbsp;id: " . $courseid);
        }

        // Get courses without opencast block
        $courses_without_opencast = $this->get_opencast_courses(true);
        // Intersect course ids
        $tmp = count($course_ids);
        $course_ids = array_intersect($course_ids, $courses_without_opencast);
        echo("<br><br><strong>Courses without opencast block:</strong> " . count($courses_without_opencast)." (<strong>".count($course_ids)."</strong> of ".$tmp." courses filtered)");
        foreach ($courses_without_opencast as $courseid) {
            if(in_array($courseid, $course_ids)) { echo("<br>&nbsp;&nbsp;id: <strong>" . $courseid . "</strong>"); }
            else { echo("<br>&nbsp;&nbsp;id: " . $courseid); }
        }

        // Unique & sorted course ids
        //$course_ids = array_unique(array_intersect($course_ids, $no_meta_courses, $courses_with_roles, $courses_without_opencast, $kusss_courses_M, $visble_courses));
        $course_ids = array_unique($course_ids);
        sort($course_ids);
        echo("
            <hr><br>
            <strong>Opencast Block Filter:</strong><br>
            &middot; Courses without opencast block<br>
            Intersect unique course ids: <strong>".count($course_ids)."</strong>
        ");
        foreach ($course_ids as $courseid) {
            echo("<br>&nbsp;&nbsp;id: <strong>" . $courseid ."</strong>");
        }
    }

    // ###################################### Courses without OPENCAST ######################################

    // Get courses with (or without) opencast block
    public function get_opencast_courses($without = false) {
        global $DB;

        // Get all courses
        $courses = get_courses();

        // Filter course
        $courses = array_filter($courses, function($course) use($DB, $without) {
            // Get context of course (by id)
            $context = \context_course::instance($course->id, IGNORE_MISSING);

            // Get block instances of opencast
            $block_instances = $DB->get_records('block_instances', ['parentcontextid' => $context->id, 'blockname' => 'opencast']);
            if(count($block_instances)) {
                if(count($block_instances) > 1) { mtrace('Error: Multiple opencast block instances found in course: '.$course->fullname.' (id: '.$course->id.', shortname: '.$course->shortname.').'); }
                // Opencast block found in course
                if (!$without) { return true; }
                else { return false; }
            } else {
                // No opencast block in course
                if (!$without) { return false; }
                else { return true; }
            }
        });

        // Reduce courses to an array with course ids (& sort it)
        $courses = array_map(function($course) { return $course->id; }, $courses);
        sort($courses);

        return $courses;
    }
}
