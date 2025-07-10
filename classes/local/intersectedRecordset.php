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
 * Helper class which intersects multiple moodle record sets.
 *
 * @package    tool_lifecycle
 * @copyright  2025 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local;

defined('MOODLE_INTERNAL') || die();

class intersectedRecordset implements \Iterator, \Countable {
    private $records = [];
    private $position = 0;

    /**
     * Constructor: Inits class & intersects passed recordsets.
     *
     * @param moodle_recordset|array|null $recordsets
     * @param string $key
     */
    public function __construct($recordsets = null, string $key = 'id') {
        if($recordsets !== null) {
            if(is_array($recordsets)) {
                foreach($recordsets as $recordset) { $this->add($recordset, $key); }
            } else { $this->add($recordsets, $key); }
        }
    }

    /**
     * Adds recordset & saves intersection of all recordsets.
     *
     * @param moodle_recordset $recordset
     * @param string $key
     */
    public function add($recordset, string $key = 'id'): void {
        $newRecords = [];
        foreach($recordset as $record) { $newRecords[] = $record; }
        //$recordset->close();

        $newRecordsByKey = [];
        foreach($newRecords as $record) {
            if(isset($record->$key)) { $newRecordsByKey[$record->$key] = $record; }
        }

        if(empty($this->records)) {
            $this->records = array_values($newRecordsByKey);

            return;
        }

        $existingRecordsByKey = [];
        foreach($this->records as $record) {
            if(isset($record->$key)) { $existingRecordsByKey[$record->$key] = $record; }
        }

        $intersectionKeys = array_intersect_key($existingRecordsByKey, $newRecordsByKey);

        $this->records = [];
        foreach($intersectionKeys as $keyValue => $record) {
            $this->records[] = $existingRecordsByKey[$keyValue];
        }
    }

    /**
     * Returns current recordset.
     *
     * @return mixed
     */
    public function current(): mixed {
        return $this->records[$this->position];
    }

    /**
     * Returns current key (index).
     *
     * @return int
     */
    public function key(): int {
        return $this->position;
    }

    /**
     * Moves internal pointer to next recordset.
     */
    public function next(): void {
        $this->position++;
    }

    /**
     * Returns internal pointer to start.
     */
    public function rewind(): void {
        $this->position = 0;
    }

    /**
     * Checks if current pointer points to a valid recordset.
     *
     * @return bool
     */
    public function valid(): bool {
        return isset($this->records[$this->position]);
    }

    /**
     * Returns the amount of all recordsets.
     *
     * @return int
     */
    public function count(): int {
        return count($this->records);
    }
}