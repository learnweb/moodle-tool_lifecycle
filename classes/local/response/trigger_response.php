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
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\response;

/**
 * Possible Responses of a Trigger Subplugin
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trigger_response {

    /** @var string Ask the next workflow if it wants to trigger the course. */
    const NEXT = 'next';
    /** @var string Exclude the course from being processed by this and any succeeding workflow. */
    const EXCLUDE = 'exclude';
    /** @var string Trigger a process for this workflow if all other triggers of this workflow say the same. */
    const TRIGGER = 'trigger';

    /** @var string Value of the response. */
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
     * @return trigger_response
     */
    public static function next() {
        return new trigger_response(self::NEXT);
    }

    /**
     * Creates a TriggerResponse telling that the subplugin wants to exlude the course from being processed.
     * @return trigger_response
     */
    public static function exclude() {
        return new trigger_response(self::EXCLUDE);
    }

    /**
     * Creates a TriggerResponse telling that the subplugin wants to trigger a lifecycle process for the course.
     * @return trigger_response
     */
    public static function trigger() {
        return new trigger_response(self::TRIGGER);
    }



}
