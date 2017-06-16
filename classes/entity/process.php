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

namespace tool_cleanupcourses\entity;

defined('MOODLE_INTERNAL') || die();

/**
 * Cleanup Course Process class
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class process {

    /** int id of the process*/
    public $id;

    /** int id of the step instance*/
    public $stepid;

    /** int id of the course*/
    public $courseid;

    /** bool true if course is in status waiting*/
    public $waiting;

    /** timestamp date the process was moved to the current step instance */
    public $timestepchanged;

    public function __construct($id, $stepid, $courseid, $waiting = false, $timestepchanged = null) {
        $this->id = $id;
        $this->stepid = $stepid;
        $this->courseid = $courseid;
        $this->waiting = $waiting;
        if ($timestepchanged === null) {
            $this->timestepchanged = time();
        }
    }

    /**
     * Creates a Cleanup Course Process from a db record.
     * @param $record
     * @return process
     */
    public static function from_record($record) {
        if (!object_property_exists($record, 'id')) {
            return null;
        }
        if (!object_property_exists($record, 'stepid')) {
            return null;
        }
        if (!object_property_exists($record, 'courseid')) {
            return null;
        }
        if (!object_property_exists($record, 'timestepchanged')) {
            return null;
        }
        if ($record->waiting) {
            $waiting = true;
        } else {
            $waiting = false;
        }

        $instance = new self($record->id, $record->stepid, $record->courseid, $waiting, $record->timestepchanged);

        return $instance;
    }

}