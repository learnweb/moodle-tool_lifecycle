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
 * Life Cycle Move Category Step
 *
 * @package lifecyclestep_movecategory
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version = 2025050400;
$plugin->requires = 2022112800; // Requires Moodle 4.1+.
$plugin->supported = [401, 405];
$plugin->component = 'lifecyclestep_movecategory';
$plugin->dependencies = [
    'tool_lifecycle' => 2025050400,
];
$plugin->release   = 'v4.5-r1';
$plugin->maturity = MATURITY_STABLE;
