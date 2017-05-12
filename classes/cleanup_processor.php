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

defined('MOODLE_INTERNAL') || die;

class cleanup_processor {

    public function __construct() {

    }

    /**
     * Processes the trigger plugins for all relevant courses.
     */
    public function call_trigger() {
        $manager = new trigger_manager();
        $enabledtrigger = $manager->get_enabled_trigger();
        $triggerlist = \core_component::get_plugin_list('cleanupcoursestrigger');
        $triggerclasses = [];
        foreach ($enabledtrigger as $trigger) {
            require_once($triggerlist[$trigger->name].'/lib.php');
            $extendedclass = "tool_cleanupcourses\\trigger\\$trigger->name";
            $triggerclasses[$trigger->name] = new $extendedclass();
        }
        $recordset = $this->get_course_recordset();
        while ($recordset->valid()) {
            $course = $recordset->current();
            /* @var $trigger trigger\base -> Implementation of the subplugin trigger interface */
            foreach ($enabledtrigger as $trigger) {
                $response = $triggerclasses[$trigger->name]->check_course($course);
                if ($response == trigger_respone::next()) {
                    continue;
                }
                if ($response == trigger_respone::exclude()) {
                    break;
                }
                if ($response == trigger_respone::trigger()) {
                    $this->trigger_course($course->id, $trigger->id);
                    break;
                }
            }
            $recordset->next();
        }
    }

    /**
     * Returns a record set with all relevant courses.
     * Relevant means that there is currently no cleanup process running for this course.
     * @return \moodle_recordset with relevant courses.
     */
    public function get_course_recordset() {
        global $DB;
        $sql = 'SELECT {course}.* from {course} '.
            'left join {tool_cleanupcourses_process} '.
            'ON {course}.id = {tool_cleanupcourses_process}.courseid '.
            'WHERE {tool_cleanupcourses_process}.courseid is null';
        return $DB->get_recordset_sql($sql);
    }

    private function trigger_course($courseid, $subpluginid) {
        global $DB;
        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->stepid = 1;
        $DB->insert_record('tool_cleanupcourses_process', $record);
    }

}
