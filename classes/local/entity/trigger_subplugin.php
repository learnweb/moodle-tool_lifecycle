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
 * Trigger subplugin class
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\entity;

/**
 * Trigger subplugin class
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trigger_subplugin extends subplugin {

    /**
     * Creates a subplugin from a db record.
     * @param object $record Data object.
     * @return trigger_subplugin
     */
    public static function from_record($record) {
        if (!object_property_exists($record, 'subpluginname')) {
            return null;
        }
        if (!object_property_exists($record, 'instancename')) {
            return null;
        }
        if (!object_property_exists($record, 'workflowid')) {
            return null;
        }
        $id = null;
        if (object_property_exists($record, 'id') && !empty($record->id)) {
            $id = $record->id;
        }
        $instance = new self($record->instancename, $record->subpluginname, $record->workflowid, $id);
        if (object_property_exists($record, 'sortindex') ) {
            $instance->sortindex = $record->sortindex;
        }

        return $instance;
    }

}
