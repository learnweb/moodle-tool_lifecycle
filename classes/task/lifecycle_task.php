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
 * Scheduled task for working on lifecycle processes
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\task;

use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\processor;

/**
 * Scheduled task for working on lifecycle processes
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lifecycle_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('lifecycle_task', 'tool_lifecycle');
    }

    /**
     * Do the job.
     */
    public function execute() {
        $processor = new processor();
        $processor->call_trigger();

        $steps = step_manager::get_step_types();
        /* @var \tool_lifecycle\step\libbase[] $steplibs stores the lib classes of all step subplugins.*/
        $steplibs = [];
        foreach ($steps as $id => $step) {
            $steplibs[$id] = lib_manager::get_step_lib($id);
            $steplibs[$id]->pre_processing_bulk_operation();
        }
        $processor->process_courses();
        foreach ($steps as $id => $step) {
            $steplibs[$id]->post_processing_bulk_operation();
        }
    }
}
