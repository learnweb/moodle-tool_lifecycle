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
 * Trigger manually the task for working on lifecycle processes
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\processor;

require_once(__DIR__ . '/../../../config.php');

require_login();

$processor = new processor();
$processor->call_trigger();

$steps = step_manager::get_step_types();
$steplibs = [];
foreach ($steps as $id => $step) {
    $steplibs[$id] = lib_manager::get_step_lib($id);
    $steplibs[$id]->pre_processing_bulk_operation();
}
$processor->process_courses();
foreach ($steps as $id => $step) {
    $steplibs[$id]->post_processing_bulk_operation();
}
