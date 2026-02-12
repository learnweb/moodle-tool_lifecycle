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

    /** @var /timestamp timestampcreated Date the process was created */
    public $timestampcreated;

    /** @var int $context context of the course in the workflow */
    public $context;

    /**
     * Process constructor.
     * @param int $id Id of the process.
     * @param int $workflowid Id of the workflow.
     * @param int $courseid Id of the course.
     * @param int $context context of the course.
     * @param int $stepindex Sortindex of the step within the workflow.
     * @param bool $waiting True if course is in status waiting.
     * @param null $timestepchanged Date the process was moved to the current step instance.
     * @param null $timestampcreated Date the process was created.
     */
    private function __construct($id, $workflowid, $courseid, $context, $stepindex, $waiting = false,
                                 $timestepchanged = null, $timestampcreated = null) {
        $this->id = $id;
        $this->workflowid = $workflowid;
        $this->courseid = $courseid;
        $this->context = $context;
        $this->waiting = $waiting;
        $this->stepindex = $stepindex;
        if ($timestepchanged === null) {
            $this->timestepchanged = time();
        } else {
            $this->timestepchanged = $timestepchanged;
        }
        if ($timestampcreated !== null) {
            $this->timestampcreated = $timestampcreated;
        }
    }

    /**
     * Creates a Life Cycle Process from a db record.
     * @param object $record Data object.
     * @param bool $coursedeleted If course is deleted no context can be fetched
     * @return process
     */
    public static function from_record($record, $coursedeleted = false) {
        if (!object_property_exists($record, 'id')) {
            return null;
        }
        if (!object_property_exists($record, 'workflowid')) {
            return null;
        }
        if (!object_property_exists($record, 'courseid') || !is_numeric($record->courseid)) {
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

        if (!$coursedeleted) {
            $context = \context_course::instance($record->courseid);
        } else {
            $context = "";
        }
        $instance = new self($record->id,
                $record->workflowid,
                $record->courseid,
                $context,
                $stepindex,
                $waiting,
                $record->timestepchanged,
                $record->timestampcreated,
        );

        return $instance;
    }

}
