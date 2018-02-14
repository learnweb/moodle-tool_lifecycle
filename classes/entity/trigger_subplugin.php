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

    /**
     * Creates a subplugin with subpluginname and optional id.
     * @oaram string $instancename name of the subplugin instance
     * @param string $subpluginname name of the subplugin
     * @param int $workflowid id of the workflow the subplugin belongs to
     * @param int $id id of the subplugin
     */
    public function __construct($instancename, $subpluginname, $workflowid, $id = null) {
        parent::__construct($instancename, $subpluginname, $workflowid, $id);
    }

    /**
     * Creates a subplugin from a db record.
     * @param $record
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
        return $instance;
    }

}