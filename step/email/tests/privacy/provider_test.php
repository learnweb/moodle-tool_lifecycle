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
 * Unit tests for the lifecyclestep_email implementation of the privacy API.
 *
 * @package    lifecyclestep_email
 * @copyright  2022 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecyclestep_email\privacy;

use context_course;
use context_system;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use dml_exception;
use stdClass;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\workflow_manager;

/**
 * Unit tests for the lifecyclestep_email implementation of the privacy API.
 *
 * @package    lifecyclestep_email
 * @copyright  2022 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends provider_testcase {

    /**
     * Basic setup for the provider tests.
     *
     * @return void
     * @throws dml_exception
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest();
        $this->user1 = $this->getDataGenerator()->create_user();
        $this->user2 = $this->getDataGenerator()->create_user();
        $this->user3 = $this->getDataGenerator()->create_user();

        $this->course1 = $this->getDataGenerator()->create_course();
        $this->course2 = $this->getDataGenerator()->create_course();
        $this->course3 = $this->getDataGenerator()->create_course();

        // Create a lifecycle email step.
        $testworkflow = workflow_manager::create_workflow('testworkflow');
        $step = new step_subplugin('test email', 'email', $testworkflow->id);
        step_manager::insert_or_update($step);

        // Add some entries.
        $record = new stdClass();
        $record->touser = $this->user1->id;
        $record->courseid = $this->course1->id;
        $record->instanceid = $step->id;
        $DB->insert_record('lifecyclestep_email', $record);

        $record = new stdClass();
        $record->touser = $this->user1->id;
        $record->courseid = $this->course2->id;
        $record->instanceid = $step->id;
        $DB->insert_record('lifecyclestep_email', $record);

        $record = new stdClass();
        $record->touser = $this->user2->id;
        $record->courseid = $this->course1->id;
        $record->instanceid = $step->id;
        $DB->insert_record('lifecyclestep_email', $record);

        $record = new stdClass();
        $record->touser = $this->user2->id;
        $record->courseid = $this->course2->id;
        $record->instanceid = $step->id;
        $DB->insert_record('lifecyclestep_email', $record);

        $record = new stdClass();
        $record->touser = $this->user2->id;
        $record->courseid = $this->course3->id;
        $record->instanceid = $step->id;
        $DB->insert_record('lifecyclestep_email', $record);

        $record = new stdClass();
        $record->touser = $this->user3->id;
        $record->courseid = $this->course3->id;
        $record->instanceid = $step->id;
        $DB->insert_record('lifecyclestep_email', $record);
        // We now have 6 entries in the table, 2 for user1, 3 for user2 and 1 for user3.
    }

    /**
     * Tests \block_lifecyclealert\privacy\provider::get_contexts_for_userid.
     *
     * @covers \lifecyclestep_email\privacy\provider::get_contexts_for_userid
     */
    public function test_get_contexts_for_userid(): void {
        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(3, $contextlist->count());
        $this->assertTrue(in_array(context_system::instance(), $contextlist->get_contexts()));
        $this->assertTrue(in_array(context_course::instance($this->course1->id), $contextlist->get_contexts()));
        $this->assertTrue(in_array(context_course::instance($this->course2->id), $contextlist->get_contexts()));
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::export_user_data.
     *
     * @covers \lifecyclestep_email\privacy\provider::export_user_data
     * @return void
     * @throws dml_exception
     */
    public function test_export_user_data(): void {
        global $DB;
        $course1context = context_course::instance($this->course1->id);
        $course2context = context_course::instance($this->course2->id);
        $approvedcontextlist = new approved_contextlist($this->user1, 'lifecyclestep_email',
            [context_system::instance()->id, $course1context->id, $course2context->id]);
        provider::export_user_data($approvedcontextlist);
        $writer = writer::with_context(context_system::instance());
        $this->assertTrue($writer->has_any_data());
        $recordids = $DB->get_records('lifecyclestep_email', ['touser' => $this->user1->id], '', 'id');
        foreach ($recordids as $id) {
            $exportedrecord = $writer->get_data(['lifecyclestep_email-' . $id->id]);
            $this->assertEquals($this->user1->id, $exportedrecord->touser);
            $this->assertTrue(in_array($exportedrecord->courseid, [$this->course1->id, $this->course2->id]));
        }

        $writer = writer::with_context($course1context);
        $this->assertTrue($writer->has_any_data());
        $recordids = $DB->get_records('lifecyclestep_email', ['touser' => $this->user1->id,
            'courseid' => $this->course1->id, ], '', 'id');
        foreach ($recordids as $id) {
            $exportedrecord = $writer->get_data(['lifecyclestep_email-' . $id->id]);
            $this->assertEquals($this->user1->id, $exportedrecord->touser);
            $this->assertEquals($exportedrecord->courseid, $this->course1->id);
        }

        $writer = writer::with_context($course2context);
        $this->assertTrue($writer->has_any_data());
        $recordids = $DB->get_records('lifecyclestep_email', ['touser' => $this->user1->id,
            'courseid' => $this->course2->id, ], '', 'id');
        foreach ($recordids as $id) {
            $exportedrecord = $writer->get_data(['lifecyclestep_email-' . $id->id]);
            $this->assertEquals($this->user1->id, $exportedrecord->touser);
            $this->assertEquals($exportedrecord->courseid, $this->course2->id);
        }
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::get_users_in_context.
     *
     * @covers \lifecyclestep_email\privacy\provider::get_users_in_context
     * @return void
     * @throws dml_exception
     */
    public function test_get_users_in_context(): void {
        $userlist = new userlist(context_system::instance(), 'lifecyclestep_email');
        provider::get_users_in_context($userlist);
        $this->assertCount(3, $userlist->get_userids());

        $userlist = new userlist(context_course::instance($this->course1->id), 'lifecyclestep_email');
        provider::get_users_in_context($userlist);
        $this->assertEquals(2, count($userlist->get_userids()));
        $this->assertTrue(in_array($this->user1->id, $userlist->get_userids()));
        $this->assertTrue(in_array($this->user2->id, $userlist->get_userids()));
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::delete_data_for_users with system context.
     *
     * @covers \lifecyclestep_email\privacy\provider::delete_data_for_users
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_users(): void {
        global $DB;
        $approveduserlist = new approved_userlist(context_system::instance(), 'lifecyclestep_email',
            [$this->user1->id, $this->user2->id]);
        $this->assertEquals(6, count($DB->get_records('lifecyclestep_email')));
        provider::delete_data_for_users($approveduserlist);
        // Only user3 should be left.
        $this->assertEquals(1, count($DB->get_records('lifecyclestep_email')));
        foreach ($DB->get_records('lifecyclestep_email') as $record) {
            // This should really only be one record.
            $this->assertNotEquals($this->user1->id, $record->touser);
            $this->assertNotEquals($this->user2->id, $record->touser);
        }
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::delete_data_for_users with a course context.
     *
     * @covers \lifecyclestep_email\privacy\provider::delete_data_for_users
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_users_coursecontext(): void {
        global $DB;
        $approveduserlist = new approved_userlist(context_course::instance($this->course1->id), 'lifecyclestep_email',
            [$this->user1->id, $this->user2->id]);
        $this->assertEquals(2, count($DB->get_records('lifecyclestep_email', ['courseid' => $this->course1->id])));
        provider::delete_data_for_users($approveduserlist);
        $this->assertEquals(0, count($DB->get_records('lifecyclestep_email', ['courseid' => $this->course1->id])));
        // Other entries should still exist.
        $this->assertEquals(4, count($DB->get_records('lifecyclestep_email')));
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::delete_data_for_all_users_in_context.
     *
     * @covers \lifecyclestep_email\privacy\provider::delete_data_for_all_users_in_context
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        $this->assertEquals(6, count($DB->get_records('lifecyclestep_email')));
        provider::delete_data_for_all_users_in_context(context_system::instance());
        $this->assertEmpty($DB->get_records('lifecyclestep_email'));
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::delete_data_for_all_users_in_context with a course context.
     *
     * @covers \lifecyclestep_email\privacy\provider::delete_data_for_all_users_in_context
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_all_users_in_context_coursecontext(): void {
        global $DB;
        $this->assertEquals(2, count($DB->get_records('lifecyclestep_email', ['courseid' => $this->course1->id])));
        provider::delete_data_for_all_users_in_context(context_course::instance($this->course1->id));
        $this->assertEmpty($DB->get_records('lifecyclestep_email', ['courseid' => $this->course1->id]));
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::delete_data_for_user.
     *
     * @covers \lifecyclestep_email\privacy\provider::delete_data_for_user
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_user(): void {
        global $DB;
        $approvedcontextlist = new approved_contextlist($this->user2, 'lifecyclestep_email',
            [context_system::instance()->id]);
        $this->assertEquals(6, count($DB->get_records('lifecyclestep_email')));
        provider::delete_data_for_user($approvedcontextlist);
        $this->assertEquals(3, count($DB->get_records('lifecyclestep_email')));
        foreach ($DB->get_records('lifecyclestep_email') as $record) {
            // This should really only be one record.
            $this->assertNotEquals($this->user2->id, $record->touser);
        }
    }

    /**
     * Tests \lifecyclestep_email\privacy\provider::delete_data_for_user.
     *
     * @covers \lifecyclestep_email\privacy\provider::delete_data_for_user
     * @return void
     * @throws dml_exception
     */
    public function test_delete_data_for_user_coursecontext(): void {
        global $DB;
        $approvedcontextlist = new approved_contextlist($this->user1, 'lifecyclestep_email',
            [context_course::instance($this->course1->id)->id, context_course::instance($this->course2->id)->id]);
        $this->assertEquals(6, count($DB->get_records('lifecyclestep_email')));
        provider::delete_data_for_user($approvedcontextlist);
        $this->assertEquals(0, count($DB->get_records('lifecyclestep_email',
            ['courseid' => $this->course1->id, 'touser' => $this->user1->id])));
        $this->assertEquals(0, count($DB->get_records('lifecyclestep_email',
            ['courseid' => $this->course2->id, 'touser' => $this->user1->id])));
    }
}
