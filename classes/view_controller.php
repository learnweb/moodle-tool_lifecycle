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
 * @package    tool_cleanupcourses
 * @copyright  2018 Tamara Gunkel, Jan Dageförde (WWU)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_cleanupcourses;
use core\notification;
use tool_cleanupcourses\manager\interaction_manager;
use tool_cleanupcourses\manager\process_manager;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\table\interaction_remaining_table;
use tool_cleanupcourses\table\interaction_attention_table;

defined('MOODLE_INTERNAL') || die();

/**
 * Controller for view.php
 * @package    tool_cleanupcourses
 * @copyright  2018 Tamara Gunkel, Jan Dageförde (WWU)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_controller {

    /**
     * Handles actions triggered in the view.php and displays the view.
     *
     * @param \renderer_base $renderer
     *
     */
    public function handle_view($renderer) {
        global $USER, $DB;

        $courses = get_user_capability_course('tool/cleanupcourses:managecourses', $USER, false);
        if (!$courses) {
            echo 'no courses';
            // TODO show error.
            return;
        }

        $arrayofcourseids = array();
        foreach ($courses as $course) {
            $arrayofcourseids[$course->id] = $course->id;
        }
        $listofcourseids = join(',', $arrayofcourseids);

        $processes = $DB->get_recordset_sql("SELECT p.id as processid, c.id as courseid, c.fullname as coursefullname, " .
        "c.shortname as courseshortname, s.id as stepinstanceid, s.instancename as stepinstancename, s.subpluginname " .
            "FROM {tool_cleanupcourses_process} p join " .
            "{course} c on p.courseid = c.id join " .
            "{tool_cleanupcourses_step} s ".
            "on p.workflowid = s.workflowid AND p.stepindex = s.sortindex " .
            "WHERE p.courseid IN (". $listofcourseids . ")");

        $requiresinteraction = array();

        foreach ($processes as $process) {
            $step = step_manager::get_step_instance($process->stepinstanceid);
            $capability = interaction_manager::get_relevant_capability($step->subpluginname);

            if (has_capability($capability, \context_course::instance($process->courseid), null, false)) {
                $requiresinteraction[] = $process->courseid;
                unset($arrayofcourseids[$process->courseid]);
            }
        }

        echo $renderer->heading(get_string('tablecoursesrequiringattention', 'tool_cleanupcourses'), 3);
        $table1 = new interaction_attention_table('tool_cleanupcourses_interaction', $requiresinteraction);

        echo $renderer->box_start();
        $table1->out(50, false);
        echo $renderer->box_end();

        echo $renderer->box("");
        echo $renderer->heading(get_string('tablecoursesremaining', 'tool_cleanupcourses'), 3);
        $table2 = new interaction_remaining_table('tool_cleanupcourses_remaining', $arrayofcourseids);

        echo $renderer->box_start("cleanupcourses-enable-overflow");
        $table2->out(50, false);
        echo $renderer->box_end();
    }

    /**
     * Handle the case that the user requested interaction.
     *
     * @param string $action triggered action code.
     * @param int $processid id of the process the action was triggered for.
     * @param int $stepid id of the step the action was triggered for.
     */
    public function handle_interaction($action, $processid, $stepid) {
        global $PAGE;

        interaction_manager::handle_interaction($stepid, $processid, $action);
        redirect($PAGE->url, get_string('interaction_success', 'tool_cleanupcourses'), null, notification::SUCCESS);
    }

    /**
     * Handle the case that the user manually triggered a workflow.
     *
     * @param int $triggerid id of the trigger whose workflow was requested.
     * @param int $courseid id of the course the workflow was requested for.
     */
    public function handle_trigger($triggerid, $courseid) {
        global $PAGE;

        //interaction_manager::handle_interaction($stepid, $processid, $action);
        redirect($PAGE->url, get_string('manual_trigger_success', 'tool_cleanupcourses'), null, notification::SUCCESS);
    }
}