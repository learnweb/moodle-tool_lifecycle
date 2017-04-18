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

use local_course_deprovision\trigger\startdatedelay;

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
        $recordset = $DB->get_recordset_select('course', null);
        while ($recordset->valid()) {
            $course = $recordset->current();
            /* @var $trigger trigger\base */
            foreach ($triggerclasses as $id => $trigger) {
                $trigger->check_course($course);
            }
            $recordset->next();
        }
    }

}
