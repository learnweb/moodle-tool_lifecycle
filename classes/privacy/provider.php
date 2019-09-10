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
 * Privacy Subsystem implementation for tool_lifecycle.
 *
 * @package    tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_user_data_provider;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the Life Cycle tool.
 *
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider{

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('tool_lifecycle_action_log',
                array(
                        'processid' => 'privacy:metadata:tool_lifecycle_action_log:processid',
                        'workflowid' => 'privacy:metadata:tool_lifecycle_action_log:workflowid',
                        'courseid' => 'privacy:metadata:tool_lifecycle_action_log:courseid',
                        'stepindex' => 'privacy:metadata:tool_lifecycle_action_log:stepindex',
                        'time' => 'privacy:metadata:tool_lifecycle_action_log:time',
                        'userid' => 'privacy:metadata:tool_lifecycle_action_log:userid',
                        'action' => 'privacy:metadata:tool_lifecycle_action_log:action'
                ),
                'privacy:metadata:tool_lifecycle_action_log');
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        if ($DB->record_exists('tool_lifecycle_action_log', array('userid' => $userid))) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel === CONTEXT_SYSTEM) {
                $records = $DB->get_records('tool_lifecycle_action_log', array('userid' => $contextlist->get_user()->id));
                $writer = writer::with_context($contextlist->current());
                foreach ($records as $record) {
                    $writer->export_data(['tool_lifecycle', 'action_log'], $record);
                }
            }
        }

    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {

    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // TODO: Implement delete_data_for_user() method.
    }
}
