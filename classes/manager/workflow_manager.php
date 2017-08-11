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
 * Manager for Cleanup Course Workflows
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

use tool_cleanupcourses\entity\workflow;

defined('MOODLE_INTERNAL') || die();

class workflow_manager {

    /**
     * Persists a workflow to the database.
     * @param workflow $workflow
     */
    public static function insert_or_update(workflow &$workflow) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($workflow->id) {
            $DB->update_record('tool_cleanupcourses_workflow', $workflow);
        } else {
            $workflow->id = $DB->insert_record('tool_cleanupcourses_workflow', $workflow);
        }
        $transaction->allow_commit();
    }

    /**
     * Returns a workflow instance if one with the is is available.
     * @param int $workflowid id of the workflow
     * @return workflow|null
     */
    public static function get_workflow($workflowid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_workflow', array('id' => $workflowid));
        if ($record) {
            $workflow = workflow::from_record($record);
            return $workflow;
        } else {
            return null;
        }
    }
}
