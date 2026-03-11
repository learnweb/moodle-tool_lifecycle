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
 * Duration form element for lifecycle to introduce month and year as units.
 *
 * @package   tool_lifecycle
 * @copyright 2026 Thomas Niedermaier University Münster
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\local\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/form/duration.php');

/**
 * Lifecycle Duration element, overrides Quickform duration element to introduce years and months as units.
 *
 * @package   tool_lifecycle
 * @copyright 2026 Thomas Niedermaier University Münster
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lifecycle_duration extends \MoodleQuickForm_duration {
    /**
     * Returns time associative array of unit length.
     *
     * @return array unit length in seconds => string unit name.
     * @throws \coding_exception
     */
    public function get_units() {
        $durationunits = parent::get_units();
        $additionalunits = [
                YEARSECS => get_string('years'),
                (int)(YEARSECS / 12) => strtolower(get_string('months')),
            ];
        $units = $additionalunits + $durationunits;
        return $units;
    }

}
