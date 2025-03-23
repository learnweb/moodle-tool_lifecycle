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
 * Life Cycle Process class
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\entity;

/**
 * Life Cycle Process class
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process {

    /** @var int $id Id of the process*/
    public $id;

    /** @var int $workflowid Id of the workflow */
    public $workflowid;

    /** @var int $courseid Id of the course*/
    public $courseid;

    /** @var bool $waiting True if course is in status waiting*/
    public $waiting;

    /** @var int $stepindex Sortindex of the step within the workflow */
    public $stepindex;

    /** @var /timestamp $timestepchanged Date the process was moved to the current step instance */
    public $timestepchanged;

    /**
     * Process constructor.
     * @param int $id Id of the process.
     * @param int $workflowid Id of the workflow.
     * @param int $courseid Id of the course.
     * @param int $stepindex Sortindex of the step within the workflow.
     * @param bool $waiting True if course is in status waiting.
     * @param null $timestepchanged Date the process was moved to the current step instance.
     */
    private function __construct($id, $workflowid, $courseid, $stepindex, $waiting = false, $timestepchanged = null) {
        $this->id = $id;
        $this->workflowid = $workflowid;
        $this->courseid = $courseid;
        $this->waiting = $waiting;
        $this->stepindex = $stepindex;
        if ($timestepchanged === null) {
            $this->timestepchanged = time();
        } else {
            $this->timestepchanged = $timestepchanged;
        }
    }

    /**
     * Creates a Life Cycle Process from a db record.
     * @param object $record Data object.
     * @return process
     */
    public static function from_record($record) {
        if (!object_property_exists($record, 'id')) {
            return null;
        }
        if (!object_property_exists($record, 'workflowid')) {
            return null;
        }
        if (!object_property_exists($record, 'courseid')) {
            return null;
        }
        if (object_property_exists($record, 'waiting') && $record->waiting) {
            $waiting = true;
        } else {
            $waiting = false;
        }

        if (object_property_exists($record, 'stepindex')) {
            $stepindex = $record->stepindex;
        } else {
            $stepindex = 0;
        }

        $instance = new self($record->id, $record->workflowid, $record->courseid, $stepindex, $waiting, $record->timestepchanged);

        return $instance;
    }

}
