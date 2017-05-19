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
 * Trigger subplugin class
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class trigger_subplugin extends subplugin{

    /** bool is the subplugin enabled*/
    public $enabled;

    /** int sortindex of subplugin */
    public $sortindex;

    /**
     * Creates a subplugin with name and optional id.
     * @param string $name name of the subplugin
     * @param int $id id of the subplugin
     */
    public function __construct($name, $id = null) {
        parent::__construct($name, $id);
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