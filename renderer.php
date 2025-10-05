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
 * Renderer for life cycle
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\single_button;

/**
 * Renderer for life cycle
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_renderer extends plugin_renderer_base {

    /**
     * Write the page footer
     */
    public function footer() {
        echo $this->output->footer();
    }

    /**
     * Write the page header
     *
     * @param string $title optional page title.
     * @throws \core\exception\coding_exception
     */
    public function header($title = null) {
        echo $this->output->header();
        if ($title) {
            echo $this->output->heading($title);
        }
    }

    /**
     * Write the tab row in page
     *
     * @param array $tabs the tabs
     * @param string $id ID of current page (can be empty)
     */
    public function tabs($tabs, $id) {
        echo $this->output->tabtree($tabs, $id);
    }

}
