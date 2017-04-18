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

namespace local_course_deprovision;

defined('MOODLE_INTERNAL') || die;
/**
 * External Page for showing active deprovision processes
 *
 * @package local
 * @subpackage course_deprovision
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class admin_page_active_processes extends \admin_externalpage
{

    /**
     * The constructor - calls parent constructor
     *
     */
    public function __construct() {
        $url = new \moodle_url('/local/course_deprovision/activeprocesses.php');
        parent::__construct('activeprocesses',
            get_string('active_processes_list_header', 'local_course_deprovision'),
            $url);
    }
}