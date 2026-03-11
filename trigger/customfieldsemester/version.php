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
 * Admin tool "Course Life Cycle" - Subplugin "Customfield Semester Trigger" - Version file
 *
 * @package    lifecycletrigger_customfieldsemester
 * @copyright  2021 Alexander Bias <bias@alexanderbias.de>
 *             on behalf of Hochschule Hannover, Servicezentrum Lehre E-Learning (elc)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'lifecycletrigger_customfieldsemester';
$plugin->version = 2025041400;
$plugin->release = 'v5.0-r1';
$plugin->requires = 2025041400;
$plugin->supported = [500, 500];
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = ['tool_lifecycle' => 2025050403,
        'customfield_semester' => 2025043001, ];
