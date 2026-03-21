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

namespace tool_lifecycle\trigger;

use moodle_url;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Triggers courses that have not been modified since they were created and which are x days old.
 * @package lifecycletrigger_neverused
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class neverused extends base_automatic {

    /**
     * If check_course_code() returns true, code to check the given course is placed here
     * @param object $course
     * @param int $triggerid
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        return trigger_response::trigger();
    }

    /**
     * Add SQL for to trigger a course. The age is determined by days.
     * @param int $triggerid ID of the trigger.
     * @return array A list containing the constructed SQL fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_course_recordset_where($triggerid) {
        $age = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['age'];
        // Just a single news forum, no forum posts, only one participant, older than age days.
        $where = "  NOT EXISTS (
                        SELECT 1
                        FROM {course_modules} cm
                        JOIN {modules} m ON m.id = cm.module
                        WHERE cm.course = c.id
                        AND NOT (
                            m.name = 'forum'
                            AND cm.instance IN (
                                SELECT f.id
                                FROM {forum} f
                                WHERE f.type = 'news'
                            )
                        )
                    )
                    AND NOT EXISTS (
                        SELECT 1
                        FROM {forum_posts} fp
                        JOIN {forum_discussions} fd ON fd.id = fp.discussion
                        WHERE fd.course = c.id
                    )

                    AND NOT EXISTS (
                        SELECT 1
                        FROM {enrol} e
                        JOIN {user_enrolments} ue ON ue.enrolid = e.id
                        WHERE e.courseid = c.id
                        GROUP BY c.id
                        HAVING COUNT(DISTINCT ue.userid) > 1
                    )

                    AND c.timecreated < EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - INTERVAL '$age days'))::bigint
        ";
        $params = ["age" => $age];
        return [$where, $params];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'neverused';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('age', PARAM_INT),
        ];
    }

    /**
     * Get the days passed since the start date of a course.
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $mform->addElement('text', 'age',
            get_string('age', 'lifecycletrigger_neverused'), ['maxlength' => 4, 'size' => 3]);
        $mform->setType('age', PARAM_INT);
        $mform->addHelpButton('age', 'age', 'lifecycletrigger_neverused');
    }

    /**
     * Reset the age at the add instance form initialization.
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings) && array_key_exists('age', $settings)) {
            $default = $settings['age'];
        } else {
            $default = 365;
        }
        $mform->setDefault('age', $default);
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'i/loading';
    }
}
