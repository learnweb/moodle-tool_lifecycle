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

namespace lifecyclestep_email\privacy;

use context;
use context_course;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use dml_exception;

/**
 * Privacy provider for lifecyclestep_email.
 *
 * @package    lifecyclestep_email
 * @copyright  2022 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    core_userlist_provider {

    /**
     * Returns metadata about lifecyclestep_email.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored by lifecyclestep_email.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'lifecyclestep_email',
            [
                'touser' => 'privacy:metadata:lifecyclestep_email:touser',
                'courseid' => 'privacy:metadata:lifecyclestep_email:courseid',
                'instanceid' => 'privacy:metadata:lifecyclestep_email:instanceid',
            ],
            'privacy:metadata:lifecyclestep_email:summary'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_system_context();

        $sql = "SELECT c.id FROM {context} c JOIN {lifecyclestep_email} e ON c.instanceid = e.courseid "
            . "WHERE contextlevel = :coursecontextlevel "
            . "AND e.touser = :touser";
        $contextlist->add_from_sql($sql, ['coursecontextlevel' => CONTEXT_COURSE, 'touser' => $userid]);
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for
     * @throws dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_system) {
                $records = $DB->get_records('lifecyclestep_email', ['touser' => $contextlist->get_user()->id]);
                $writer = writer::with_context($context);
                foreach ($records as $record) {
                    $subcontext = ['lifecyclestep_email-' . $record->id];
                    $writer->export_data($subcontext, $record);
                }
            } else if ($context instanceof context_course) {
                $records = $DB->get_records('lifecyclestep_email',
                    ['courseid' => $context->instanceid, 'touser' => $contextlist->get_user()->id]);
                $writer = writer::with_context($context);
                foreach ($records as $record) {
                    $subcontext = ['lifecyclestep_email-' . $record->id];
                    $writer->export_data($subcontext, $record);
                }
            }
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist): void {
        if ($userlist->get_context() instanceof context_system) {
            $sql = "SELECT touser FROM {lifecyclestep_email}";
            $userlist->add_from_sql('touser', $sql, []);
        } else if ($userlist->get_context() instanceof context_course) {
            $sql = "SELECT touser FROM {lifecyclestep_email} WHERE courseid = :courseid";
            $userlist->add_from_sql('touser', $sql, ['courseid' => $userlist->get_context()->instanceid]);
        }
    }

    /**
     * Delete data of multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     * @throws dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        foreach ($userlist->get_userids() as $userid) {
            if ($userlist->get_context() instanceof context_system) {
                $DB->delete_records('lifecyclestep_email', ['touser' => $userid]);
            } else if ($userlist->get_context() instanceof context_course) {
                $DB->delete_records('lifecyclestep_email', ['touser' => $userid,
                    'courseid' => $userlist->get_context()->instanceid, ]);
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if ($context instanceof context_system) {
            $DB->delete_records('lifecyclestep_email');
        } else if ($context instanceof context_course) {
            $DB->delete_records('lifecyclestep_email', ['courseid' => $context->instanceid]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for
     * @throws dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $user = $contextlist->get_user();
        foreach ($contextlist as $context) {
            if ($context instanceof context_system) {
                $DB->delete_records('lifecyclestep_email', ['touser' => $user->id]);
            } else if ($context instanceof context_course) {
                $DB->delete_records('lifecyclestep_email', ['touser' => $user->id, 'courseid' => $context->instanceid]);
            }
        }
    }
}
