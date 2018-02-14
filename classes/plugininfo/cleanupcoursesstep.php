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
 * Pluginfo for cleanup courses step
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_cleanupcourses\plugininfo;

use core\plugininfo\base;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die();


class cleanupcoursesstep extends base {
    public function is_uninstall_allowed() {
        if ($this->is_standard()) {
            return false;
        }
        // Only allow uninstall, if no active workflow with step instances of this type is present.
        $steps = step_manager::get_step_instances_by_subpluginname($this->name);
        foreach ($steps as $step) {
            if (workflow_manager::is_active($step->workflowid)) {
                return false;
            }
        }
        return true;
    }

    public function uninstall(\progress_trace $progress) {
        step_manager::remove_all_instances($this->name);
    }
}