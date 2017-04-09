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
 * Possible Responses of a Subplugin
 *
 * @package local
 * @subpackage course_deprovision
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_course_deprovision;

defined('MOODLE_INTERNAL') || die();

public class SubpluginResponse {

    const NOTTRIGGERED = 'nottriggered';
    const TRIGGERED = 'triggered';
    const WAITING = 'waiting';

    private $value;

    /**
     * Creates an instance of a SubpluginResponse
     * @param string $responsetype code of the response
     */
    private function __construct($responsetype) {
        $this->value = $responsetype;
    }

    /**
     * Creates a SubpluginResponse telling that the subplugin does not want to process the course.
     */
    public function nottriggered() {
        return new SubpluginResponse(self::NOTTRIGGERED);
    }

    /**
     * Creates a SubpluginResponse telling that the subplugin processed the course and marked it for further processing.
     */
    public function triggered() {
        return new SubpluginResponse(self::TRIGGERED);
    }

    /**
     * Creates a SubpluginResponse telling that the subplugin is still processing the course.
     */
    public function waiting() {
        return new SubpluginResponse(self::WAITING);
    }



}
