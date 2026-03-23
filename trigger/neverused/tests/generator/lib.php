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
 * lifecycletrigger_neverused generator tests
 *
 * @package    lifecycletrigger_neverused
 * @category   test
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

/**
 * Generate workflow with neverused trigger
 *
 * @package    lifecycletrigger_neverused
 * @category   test
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_neverused_generator extends testing_module_generator {

    /**
     * Creates a trigger neverused for an artificial workflow without steps.
     * @return trigger_subplugin the created neverused trigger.
     * @throws moodle_exception
     */
    public static function create_trigger_with_workflow() {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);
        // Create trigger.
        $record = new stdClass();
        $record->subpluginname = 'neverused';
        $record->instancename = 'neverused';
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);
        // Set age setting.
        $settings = new stdClass();
        $settings->age = 365;
        settings_manager::save_settings($trigger->id, settings_type::TRIGGER, $trigger->subpluginname, $settings);

        return $trigger;
    }

}

/**
 * Generate a course with a news forum and one enrolled user with a defined start date
 *
 * @package    lifecycletrigger_neverused
 * @category   test
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_neverused_data_generator extends testing_data_generator {
    /**
     * Create a course as it would be created following a course request
     * @param $record
     * @param array|null $options
     * @return stdClass
     */
    public function create_course($record = null, ?array $options = null) {
        global $CFG;

        $course = parent::create_course($record, $options);
        $teacher = parent::create_user();
        // Add forum.
        require_once($CFG->dirroot . '/mod/forum/lib.php');
        forum_get_course_forum($course->id, 'news');
        // Enroll teacher.
        parent::enrol_user(
            $teacher->id,
            $course->id,
            'editingteacher'
        );

        return $course;
    }
}
