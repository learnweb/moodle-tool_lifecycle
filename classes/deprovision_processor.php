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
 * Offers functionality to trigger, process and finish deprovision processes.
 *
 * @package local
 * @subpackage course_deprovision
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_course_deprovision;

defined('MOODLE_INTERNAL') || die;

class deprovision_processor {

    public function __construct() {

    }

    public function call_trigger() {
        global $DB;
        $triggerlist = \core_component::get_plugin_list('coursedeprovisiontrigger');
        $triggerclasses = [];
        foreach ($triggerlist as $class => $classdir) {
            require_once($classdir.'/lib.php');
            $extendedclass = "local_course_deprovision\\trigger\\$class";
            $triggerclasses[$class] = new $extendedclass();
        }
        $recordset = $DB->get_recordset_sql('SELECT {course}.* from {course} '.
            'left join {local_coursedeprov_process} '.
            'ON {course}.id = {local_coursedeprov_process}.courseid '.
            'WHERE {local_coursedeprov_process}.courseid is null');
        while ($recordset->valid()) {
            $course = $recordset->current();
            /* @var $trigger trigger\base -> Implementation of the subplugin trigger interface */
            foreach ($triggerclasses as $id => $trigger) {
                $response = $trigger->check_course($course);
                if ($response == TriggerResponse::next()) {
                    continue;
                }
                if ($response == TriggerResponse::exclude()) {
                    break;
                }
                if ($response == TriggerResponse::trigger()) {
                    $this->trigger_course($course->id);
                }
            }
            $recordset->next();
        }
    }

    private function trigger_course($courseid) {
        global $DB;
        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->subplugin_id = 3;
        $DB->insert_record('local_coursedeprov_process', $record);
    }

}
