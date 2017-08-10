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
 * Offers functionality to trigger, process and finish cleanup processes.
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses;

use tool_cleanupcourses\entity\trigger_subplugin;
use tool_cleanupcourses\manager\process_manager;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\trigger_manager;
use tool_cleanupcourses\manager\lib_manager;
use tool_cleanupcourses\response\step_response;
use tool_cleanupcourses\response\trigger_response;


defined('MOODLE_INTERNAL') || die;

class cleanup_processor {

    public function __construct() {

    }

    /**
     * Processes the trigger plugins for all relevant courses.
     */
    public function call_trigger() {
        $enabledtrigger = trigger_manager::get_enabled_trigger();

        $recordset = $this->get_course_recordset();
        while ($recordset->valid()) {
            $course = $recordset->current();
            foreach ($enabledtrigger as $trigger) {
                $lib = lib_manager::get_trigger_lib($trigger->subpluginname);
                $response = $lib->check_course($course);
                if ($response == trigger_response::next()) {
                    continue;
                }
                if ($response == trigger_response::exclude()) {
                    break;
                }
                if ($response == trigger_response::trigger()) {
                    process_manager::create_process($course->id, trigger_subplugin::from_record($trigger));
                    break;
                }
            }
            $recordset->next();
        }
    }

    /**
     * Calls the process_course() method of each step submodule currently responsible for a given course.
     */
    public function process_courses() {
        foreach (process_manager::get_processes() as $process) {
            while (true) {
                $step = step_manager::get_step_instance($process->stepid);
                $lib = lib_manager::get_step_lib($step->subpluginname);
                try {
                    $course = get_course($process->courseid);
                } catch (\dml_missing_record_exception $e) {
                    // Course no longer exists!
                    break;
                }
                if ($process->waiting) {
                    $result = $lib->process_waiting_course($process->id, $process->stepid, $course);
                } else {
                    $result = $lib->process_course($process->id, $process->stepid, $course);
                }
                if ($result == step_response::waiting()) {
                    process_manager::set_process_waiting($process);
                    break;
                } else if ($result == step_response::proceed()) {
                    if (!process_manager::proceed_process($process)) {
                        break;
                    }
                } else if ($result == step_response::rollback()) {
                    process_manager::rollback_process($process);
                    break;
                } else {
                    throw new \moodle_exception('Return code \''. var_dump($result) . '\' is not allowed!');
                }
            }
        }

    }

    /**
     * Returns a record set with all relevant courses.
     * Relevant means that there is currently no cleanup process running for this course.
     * @return \moodle_recordset with relevant courses.
     */
    private function get_course_recordset() {
        global $DB;
        $sql = 'SELECT {course}.* from {course} '.
            'left join {tool_cleanupcourses_process} '.
            'ON {course}.id = {tool_cleanupcourses_process}.courseid '.
            'WHERE {tool_cleanupcourses_process}.courseid is null';
        return $DB->get_recordset_sql($sql);
    }

}
