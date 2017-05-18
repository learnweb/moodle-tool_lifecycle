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

namespace tool_cleanupcourses\object;

defined('MOODLE_INTERNAL') || die();

/**
 * Subplugin class
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class subplugin{

    /** int Id of subplugin */
    public $id;

    /** string name of subplugin */
    public $name;

    /** subplugin this subplugin is followed by in the cleanup process*/
    public $followedby;

    /**
     * Creates a subplugin with name and optional id.
     * @param string $name name of the subplugin
     * @param int $id id of the subplugin
     */
    public function __construct($name, $id = null) {
        $this->name = $name;
        $this->id = $id;
    }

    /**
     * Creates a subplugin from a db record.
     * @param $record
     * @return trigger_subplugin
     */
    public static function from_record($record) {
        if (!object_property_exists($record, 'name')) {
            return null;
        }
        $instance = new self($record->name);
        foreach (array_keys((array) $record) as $field) {
            if (object_property_exists($instance, $field)) {
                $instance->$field = $record->$field;
            }
        }

        return $instance;
    }

}