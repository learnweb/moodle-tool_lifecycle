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
 * Class representing a manual trigger tool
 *
 * @package tool_lifecycle
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\data;

use renderable;

/**
 * Class representing a manual trigger tool
 *
 * @package tool_lifecycle
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manual_trigger_tool implements renderable {

    /** @var int $triggerid Id of the trigger the tool belongs to. */
    public $triggerid;

    /** @var string $icon Icon, which is displayed to the user in the trigger tools menu. */
    public $icon;

    /** @var string $displayname Name, which is displayed to the user in the trigger tools menu. */
    public $displayname;

    /** @var string $capability Capability, which is required to use and display this tool. */
    public $capability;

    /**
     * manual_trigger_tool constructor.
     * @param int $triggerid Id of the trigger the tool belongs to.
     * @param string $icon Icon, which is displayed to the user in the trigger tools menu.
     * @param string $displayname Name, which is displayed to the user in the trigger tools menu.
     * @param string $capability Capability, which is required to use and display this tool.
     */
    public function __construct($triggerid, $icon, $displayname, $capability) {
        $this->triggerid = $triggerid;
        $this->icon = $icon;
        $this->displayname = $displayname;
        $this->capability = $capability;
    }

}
