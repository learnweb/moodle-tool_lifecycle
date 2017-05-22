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
 * Subplugin class
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class step_subplugin extends subplugin {

    /** string instancename of the step*/
    public $instancename;

    /**
     * Creates a subplugin with subpluginname and optional id.
     * @param string $subpluginname name of the subplugin
     * @param int $id id of the subplugin
     */
    public function __construct($instancename, $subpluginname, $id = null) {
        parent::__construct($subpluginname, $id);
        $this->instancename = $instancename;
    }

    /**
     * Creates a subplugin from a db record.
     * @param $record
     * @return step_subplugin
     */
    public static function from_record($record) {
        if (!object_property_exists($record, 'subpluginname')) {
            return null;
        }
        if (!object_property_exists($record, 'instancename')) {
            return null;
        }
        $instance = new self($record->instancename, $record->subpluginname);
        foreach (array_keys((array) $record) as $field) {
            if (object_property_exists($instance, $field)) {
                $instance->$field = $record->$field;
            }
        }

        return $instance;
    }

}