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
 * Helper class for reporting.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_opencast;

/**
 * Helper class for reporting.
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_helper {
    /** @var array The information array to be reported. */
    private array $info = [];

    /** @var int The course id. */
    private int $courseid;

    /** @var int The opencast instance id. */
    private int $ocinstanceid;

    /** @var int The step instance id. */
    private int $stepid;

    /**
     * Constructor.
     * @param int $courseid Course Id.
     * @param int $ocinstanceid The opencast instance id.
     * @param int $stepid The step instance id.
     * @return void
     */
    public function __construct(int $courseid, int $ocinstanceid, int $stepid) {
        $this->courseid = $courseid;
        $this->ocinstanceid = $ocinstanceid;
        $this->stepid = $stepid;
    }

    /**
     * Adds a new line to the info array.
     * @param string $line The line to be recorded.
     * @return void
     */
    public function add_info_line(string $line): void {
        $this->info[] = $line;
    }

    /**
     * Prepare and return the formatted report information.
     *
     * Combines course, Opencast instance, and step details with any added info lines,
     * formatted as an HTML string with line breaks.
     *
     * @return string The formatted report info.
     */
    public function get_info(): string {
        $finalinfo = [
            "Course (ID: {$this->courseid})",
            "Opencast Instance (ID: {$this->ocinstanceid})",
            "Step (ID: {$this->stepid})",
            ...$this->info,
        ];
        return implode('<br>', $finalinfo);
    }

    /**
     * Determine if the the report instance has any info to report.
     * @return bool
     */
    public function has_info(): bool {
        return !empty($this->info);
    }
}
