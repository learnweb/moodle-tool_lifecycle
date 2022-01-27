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
 * Life Cycle Workflow class
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\entity;

/**
 * Life Cycle Workflow class
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class workflow {

    /** @var int $id Id of the workflow. */
    public $id;

    /** @var string @title Title of the workflow. */
    public $title;

    /** @var /timestamp $timeactive Time the workflow was set active. */
    public $timeactive;

    /** @var /timestamp $timedeactive Time the workflow was deactivated. */
    public $timedeactive;

    /** @var int $sortindex Sort index of all active workflows. */
    public $sortindex;

    /** @var bool|null $manual True if workflow is manually triggered. */
    public $manual;

    /** @var string $displaytitle Title that is displayed to users. */
    public $displaytitle;

    /** @var int $rollbackdelay The delay in case of rollback. */
    public $rollbackdelay;

    /** @var int $finishdelay The delay in case of finished course. */
    public $finishdelay;

    /** @var bool $delayforallworkflows True if a delay counts for all workflows. */
    public $delayforallworkflows;

    /**
     * Workflow constructor.
     * @param int $id Id of the workflow.
     * @param string $title Title of the workflow.
     * @param /timestamp $timeactive Time the workflow was set active.
     * @param /timestamp $timedeactive Time the workflow was deactivated.
     * @param int $sortindex Sort index of all active workflows.
     * @param bool $manual True if workflow is manually triggered.
     * @param string $displaytitle Title that is displayed to users.
     * @param int $rollbackdelay The delay in case of rollback.
     * @param int $finishdelay The delay in case of finished course.
     * @param bool $delayforallworkflows True if a delay counts for all workflows.
     */
    private function __construct($id, $title, $timeactive, $timedeactive, $sortindex, $manual, $displaytitle,
                                 $rollbackdelay, $finishdelay, $delayforallworkflows) {
        $this->id = $id;
        $this->title = $title;
        $this->timeactive = $timeactive;
        $this->timedeactive = $timedeactive;
        $this->sortindex = $sortindex;
        $this->manual = $manual;
        $this->displaytitle = $displaytitle;
        $this->rollbackdelay = $rollbackdelay;
        $this->finishdelay = $finishdelay;
        $this->delayforallworkflows = $delayforallworkflows;
    }

    /**
     * Creates a Workflow from a DB record.
     * @param object $record Data object.
     * @return workflow
     */
    public static function from_record($record) {
        $id = null;
        if (object_property_exists($record, 'id') && $record->id) {
            $id = $record->id;
        }
        if (!object_property_exists($record, 'title')) {
            return null;
        }

        $timeactive = null;
        if (object_property_exists($record, 'timeactive') && $record->timeactive) {
            $timeactive = $record->timeactive;
        }

        $timedeactive = null;
        if (object_property_exists($record, 'timedeactive') && $record->timedeactive) {
            $timedeactive  = $record->timedeactive;
        }

        $sortindex = null;
        if (object_property_exists($record, 'sortindex')) {
            $sortindex = $record->sortindex;
        }

        $manual = null;
        if (object_property_exists($record, 'manual')) {
            if ($record->manual == 1) {
                $manual = true;
            } else if ($record->manual == 0) {
                $manual = false;
            }
        }

        if (!object_property_exists($record, 'displaytitle') || !$record->displaytitle) {
            $displaytitle = $record->title;
        } else {
            $displaytitle = $record->displaytitle;
        }

        $rollbackdelay = 0;
        if (object_property_exists($record, 'rollbackdelay')) {
            $rollbackdelay = $record->rollbackdelay;
        }

        $finishdelay = 0;
        if (object_property_exists($record, 'finishdelay')) {
            $finishdelay = $record->finishdelay;
        }

        $delayforallworkflows = false;
        if (object_property_exists($record, 'delayforallworkflows')) {
            $delayforallworkflows = $record->delayforallworkflows;
        }

        $instance = new self($id, $record->title, $timeactive, $timedeactive, $sortindex, $manual, $displaytitle,
                $rollbackdelay, $finishdelay, $delayforallworkflows);

        return $instance;
    }

}
