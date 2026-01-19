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
 * Controller for view.php
 * @package    tool_lifecycle
 * @copyright  2018 Tamara Gunkel, Jan Dageförde (WWU)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle;

use core\notification;
use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\table\interaction_remaining_table;
use tool_lifecycle\local\table\interaction_attention_table;;

/**
 * Controller for view.php
 * @package    tool_lifecycle
 * @copyright  2018 Tamara Gunkel, Jan Dageförde (WWU)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_controller {

    /**
     * Handles actions triggered in the view.php and displays the view.
     *
     * @param \renderer_base $renderer
     * @param object $filterdata
     * @param bool $bulk switch whether bulk editing is active
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public function handle_view($renderer, $filterdata, $bulk) {
        global $DB, $USER;

        // Get all courses where current user is manager. Sysadmin rights ignored.
        $coursesasmanager = get_user_capability_course('tool/lifecycle:managecourses', null, false);
        $courseids1 = array_column($coursesasmanager, 'id');
        // Get all courses where current user is enrolled.
        $usercourses = enrol_get_users_courses($USER->id);
        $courseids2 = array_column($usercourses, 'id');
        // Only take courses where both is the case (enrolled and manager).
        $courses = array_intersect($courseids1, $courseids2);
        if (!$courses) {
            echo 'no courses';
            // Software enhancement show error.
            return;
        }

        // Select all processes of these courses.
        [$insql, $inparams] = $DB->get_in_or_equal($courses);
        $sql = "SELECT p.id as processid, c.id as courseid, c.fullname as coursefullname, " .
            "c.shortname as courseshortname, s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname " .
            "FROM {tool_lifecycle_process} p join " .
            "{course} c on p.courseid = c.id join " .
            "{tool_lifecycle_step} s ".
            "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex " .
            "WHERE p.courseid $insql";
        $processes = $DB->get_recordset_sql($sql, $inparams);

        $requiresinteractioncourses = [];
        $remainingcourses = $inparams; // All courses.

        // Check for every process whether the current user has the required rights and if there are actions.
        foreach ($processes as $process) {
            $step = step_manager::get_step_instance($process->stepinstanceid);
            if ($capability = (interaction_manager::get_relevant_capability($step->subpluginname) ?? false)) {
                $capabilityok = has_capability($capability, \context_course::instance($process->courseid),
                    null, false);
            } else {
                $capabilityok = true;
            }
            // Does the step interaction lib returns one or more actions for the user? (I.e. "Keep" in the email step).
            $actions = !empty(interaction_manager::get_action_tools($step->subpluginname, $process->processid));
            if ($capabilityok && $actions) {
                $requiresinteractioncourses[] = $process->courseid;
            }
        }
        // Remove interaction courses from remaining courses.
        $remainingcourses = array_diff($remainingcourses, $requiresinteractioncourses);

        // List of courses where an interaction is required.
        echo $renderer->heading(get_string('tablecoursesrequiringattention', 'tool_lifecycle'), 3);
        $table1 = new interaction_attention_table('tool_lifecycle_interaction',
            $requiresinteractioncourses, $filterdata);
        echo $renderer->box_start("managing_courses_tables");
        $table1->out(50, false);
        echo $renderer->box_end();

        // List of courses the user is enrolled to and has the required lifecycle rights.
        echo $renderer->box("");
        echo $renderer->heading(get_string('tablecoursesremaining', 'tool_lifecycle'), 3);
        $table2 = new interaction_remaining_table('tool_lifecycle_remaining',
            $remainingcourses, $filterdata, $bulk);
        echo $renderer->box_start("lifecycle-enable-overflow lifecycle-table");
        $table2->out(50, false);
        echo $renderer->box_end();

    }

    /**
     * Handle the case that the user requested interaction.
     *
     * @param string $action triggered action code.
     * @param int $processid id of the process the action was triggered for.
     * @param int $stepid id of the step the action was triggered for.
     * @return bool true for success
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public function handle_interaction($action, $processid, $stepid) {
        global $PAGE;

        $process = process_manager::get_process_by_id($processid);
        $step = step_manager::get_step_instance($stepid);
        $capability = interaction_manager::get_relevant_capability($step->subpluginname);
        require_capability($capability, \context_course::instance($process->courseid), null, false);

        if (interaction_manager::handle_interaction($stepid, $processid, $action)) {
            redirect($PAGE->url, get_string('interaction_success', 'tool_lifecycle'), null, notification::SUCCESS);
        }
    }

    /**
     * Handle the case that the user manually triggered a workflow.
     *
     * @param int $triggerid id of the trigger whose workflow was requested.
     * @param int $courseid id of the course, the workflow was requested for.
     * @return string|int $rc error message or 0 for success
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public function handle_trigger($triggerid, $courseid) {

        // Software enhancement check if trigger to triggerid exists.
        // Check if the trigger is manual.
        $trigger = trigger_manager::get_instance($triggerid);
        $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
        if (!$lib->is_manual_trigger()) {
            throw new \moodle_exception('error_wrong_trigger_selected', 'tool_lifecycle');
        }

        // Check if user has capability.
        $triggersettings = settings_manager::get_settings($triggerid, settings_type::TRIGGER);
        require_capability($triggersettings['capability'], \context_course::instance($courseid), null, true);

        // Check if the course does not have a running process.
        $runningprocess = process_manager::get_process_by_course_id($courseid);
        if ($runningprocess !== null) {
            return get_string('manual_trigger_process_existed', 'tool_lifecycle');
        }

        // Actually trigger process.
        $process = process_manager::manually_trigger_process($courseid, $triggerid);

        $processor = new processor();
        if ($processor->process_course_interactive($process->id)) {
            return 0;
        } else {
            return get_string('error');
        }
    }
}
