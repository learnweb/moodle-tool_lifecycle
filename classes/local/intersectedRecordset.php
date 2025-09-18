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
    private $wasFilled = false;

    /**
     * Constructor: Inits class & intersects passed recordsets.
     *
     * @param moodle_recordset|array|null $recordsets
     * @param string $key
     */
    public function __construct($recordsets = null, string $key = 'id') {
        if($recordsets !== null) {
            if(is_array($recordsets)) {
                // For multiple recordsets
                foreach($recordsets as $recordset) {
                    // If recordset is a chunked recordset
                    if(is_array($recordset)) {
                        //mtrace('Chunked recordset');
                        // Create new array for chunked recordset
                        $chunkedRecords = [];
                        // For each chunked recordset
                        foreach($recordset as $chunk_recordset) {
                            // For each record in chunked recordset
                            foreach($chunk_recordset as $record) {
                                if(isset($record->$key)) { $chunkedRecords[$record->$key] = $record; }
                            }
                        }
                        // Add all records of chunked recordsets
                        $this->add($chunkedRecords, $key);
                    } else {
                        //mtrace('Normal recordset');
                        $this->add($recordset, $key);
                    }
                }
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
        // Add new records to array with key
        $newRecords = [];
        foreach($recordset as $record) {
            if(isset($record->$key)) { $newRecords[$record->$key] = $record; }
        }
        //mtrace('     Found '.count($newRecords).' records in recordset');
        //$recordset->close();

        // Store new records without key, if no records were stored & return
        if(empty($this->records) && !$this->wasFilled) {
            $this->records = array_values($newRecords);
            $this->wasFilled = true;

            return;
        }

        // Add existing records to array with key
        $existingRecords = [];
        foreach($this->records as $record) {
            if(isset($record->$key)) { $existingRecords[$record->$key] = $record; }
        }

        // Intersect existing & new records by keys
        $intersectionKeys = array_intersect_key($existingRecords, $newRecords);
        // Clear existing records
        $this->records = [];
        // Store intersected records by keys
        foreach($intersectionKeys as $keyValue => $record) {
            $this->records[] = $existingRecords[$keyValue];
        }
        //mtrace('Add - Intersected record sets: '.count($this->records));
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