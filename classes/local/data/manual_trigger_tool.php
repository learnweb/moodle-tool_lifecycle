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

namespace tool_cleanupcourses\local\data;

use renderable;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a manual trigger tool
 *
 * @package tool_cleanupcourses
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class manual_trigger_tool implements renderable {

    /** string icon, which is displayed to the user in the trigger tools menu*/
    public $icon;

    /** string name, which is displayed to the user in the trigger tools menu*/
    public $displayname;

    /** string capability required to use and display this tool*/
    public $capability;

    public function __construct($icon, $displayname, $capability) {
        $this->icon = $icon;
        $this->displayname= $displayname;
        $this->capability = $capability;
    }

}