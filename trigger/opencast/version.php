<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Life Cycle Trigger lifecycletrigger_opencast.
 *
 * @package    lifecycletrigger_opencast
 * @copyright  2024 Michael Schink JKU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2025071000;
$plugin->component = 'lifecycletrigger_opencast';
$plugin->dependencies = array('tool_lifecycle' => 2022112400);
$plugin->requires = 2020061500; // Requires Moodle 3.9+.
$plugin->release = '0.1.0';
$plugin->maturity = MATURITY_ALPHA; //MATURITY_STABLE
