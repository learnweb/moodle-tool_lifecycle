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
 * Possible Responses of a Trigger Subplugin
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses;

defined('MOODLE_INTERNAL') || die();

class TriggerResponse {

    const NEXT = 'next';
    const EXCLUDE = 'exclude';
    const TRIGGER = 'trigger';

    private $value;

    /**
     * Creates an instance of a TriggerResponse
     * @param string $responsetype code of the response
     */
    private function __construct($responsetype) {
        $this->value = $responsetype;
    }

    /**
     * Creates a TriggerResponse telling that the subplugin does not want to process the course.
     * This means that the course can be passed to the next trigger.
     * @return TriggerResponse
     */
    public static function next() {
        return new TriggerResponse(self::NEXT);
    }

    /**
     * Creates a TriggerResponse telling that the subplugin wants to exlude the course from cleanup.
     * @return TriggerResponse
     */
    public static function exclude() {
        return new TriggerResponse(self::EXCLUDE);
    }

    /**
     * Creates a TriggerResponse telling that the subplugin wants to trigger the cleanup process for the course.
     * @return TriggerResponse
     */
    public static function trigger() {
        return new TriggerResponse(self::TRIGGER);
    }



}
