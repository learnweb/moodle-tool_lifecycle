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
 * Tests Privacy Implementation
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

defined('MOODLE_INTERNAL') || die();
global $CFG;

use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use core_privacy\tests\request\approved_contextlist;
use tool_lifecycle\action;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\interaction_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\privacy\provider;
use tool_lifecycle\processor;

/**
 * Tests Privacy Implementation
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class privacy_test extends provider_testcase {


    /** Icon of the manual trigger. */
    const MANUAL_TRIGGER1_ICON = 't/up';
    /** Display name of the manual trigger. */
    const MANUAL_TRIGGER1_DISPLAYNAME = 'Up';
    /** Capability of the manual trigger. */
    const MANUAL_TRIGGER1_CAPABILITY = 'moodle/course:manageactivities';

    /** @var string Action string for triggering to keep a course from Email step. */
    const ACTION_KEEP = 'keep';

    /** @var workflow $workflow Workflow of this test. */
    private $workflow;

    /** @var \tool_lifecycle_generator $generator Instance of the test generator. */
    private $generator;

    /** @var step_subplugin $emailstep Instance of the Email step */
    private $emailstep;

    /**
     * Setup the testcase.
     * @throws \coding_exception
     */
    public function setUp(): void {
        global $USER;

        // We do not need a sesskey check in theses tests.
        $USER->ignoresesskey = true;
        $this->resetAfterTest();
        $this->generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $settings = new \stdClass();
        $settings->icon = self::MANUAL_TRIGGER1_ICON;
        $settings->displayname = self::MANUAL_TRIGGER1_DISPLAYNAME;
        $settings->capability = self::MANUAL_TRIGGER1_CAPABILITY;
        $this->workflow = $this->generator->create_manual_workflow($settings);
        workflow_manager::handle_action(action::WORKFLOW_ACTIVATE, $this->workflow->id);

        $this->emailstep = $this->generator->create_step("instance2", "email", $this->workflow->id);
    }

    /**
     * Get all contextes in which users are effected.
     * @covers \tool_lifecycle\privacy\provider contexts
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public function test_get_contexts_for_userid(): void {
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $this->setUser($u1);
        $contextlist = provider::get_contexts_for_userid($u1->id);
        $this->assertEquals(0, $contextlist->count());

        $p1 = $this->generator->create_process($c1->id, $this->workflow->id);
        $p2 = $this->generator->create_process($c2->id, $this->workflow->id);

        $processor = new processor();
        $processor->process_courses();

        interaction_manager::handle_interaction($this->emailstep->id, $p1->id, self::ACTION_KEEP);
        interaction_manager::handle_interaction($this->emailstep->id, $p2->id, self::ACTION_KEEP);

        $contextlist = provider::get_contexts_for_userid($u1->id);
        $this->assertEquals(1, $contextlist->count());
        $this->assertTrue($contextlist->current() instanceof \context_system);
    }

    /**
     * Export all data for privacy provider
     * @covers \tool_lifecycle\privacy\provider data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public function test_export_user_data(): void {
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $this->setUser($u1);

        $p1 = $this->generator->create_process($c1->id, $this->workflow->id);
        $p2 = $this->generator->create_process($c2->id, $this->workflow->id);

        $processor = new processor();
        $processor->process_courses();

        interaction_manager::handle_interaction($this->emailstep->id, $p1->id, self::ACTION_KEEP);
        interaction_manager::handle_interaction($this->emailstep->id, $p2->id, self::ACTION_KEEP);

        $contextlist = new approved_contextlist($u1, 'tool_lifecycle', [\context_system::instance()->id]);
        provider::export_user_data($contextlist);
        $writer = writer::with_context(\context_system::instance());
        $step = step_manager::get_step_instance_by_workflow_index($this->workflow->id, 1);
        $subcontext = ['tool_lifecycle', 'action_log', "process_$p1->id", $step->instancename,
                "action_" . self::ACTION_KEEP, ];
        $data1 = $writer->get_data($subcontext);
        $this->assertEquals($u1->id, $data1->userid);
        $this->assertEquals(self::ACTION_KEEP, $data1->action);
        $subcontext = ['tool_lifecycle', 'action_log', "process_$p2->id", $step->instancename,
                "action_" . self::ACTION_KEEP, ];
        $data2 = $writer->get_data($subcontext);
        $this->assertEquals($u1->id, $data2->userid);
        $this->assertEquals(self::ACTION_KEEP, $data2->action);
    }

    /**
     * delete data for context - privacy provider
     * @covers \tool_lifecycle\privacy\provider data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        $c1 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $this->setUser($u1);

        $p1 = $this->generator->create_process($c1->id, $this->workflow->id);

        $processor = new processor();
        $processor->process_courses();

        interaction_manager::handle_interaction($this->emailstep->id, $p1->id, self::ACTION_KEEP);

        provider::delete_data_for_all_users_in_context(\context_system::instance());

        $this->assertFalse($DB->record_exists_select('tool_lifecycle_action_log', 'userid != -1'));
    }

    /**
     * delete data for user - privacy provider
     * @covers \tool_lifecycle\privacy\provider data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public function test_delete_data_for_user(): void {
        global $DB;
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();

        $p1 = $this->generator->create_process($c1->id, $this->workflow->id);
        $p2 = $this->generator->create_process($c2->id, $this->workflow->id);

        $processor = new processor();
        $processor->process_courses();

        $this->setUser($u1);
        interaction_manager::handle_interaction($this->emailstep->id, $p1->id, self::ACTION_KEEP);

        $this->setUser($u2);
        interaction_manager::handle_interaction($this->emailstep->id, $p2->id, self::ACTION_KEEP);

        $contextlist = new approved_contextlist($u1, 'tool_lifecycle', [1]);
        provider::delete_data_for_user($contextlist);
        $this->assertEquals(0, $DB->count_records_select('tool_lifecycle_action_log', "userid = $u1->id"));
        $this->assertEquals(1, $DB->count_records_select('tool_lifecycle_action_log', "userid = $u2->id"));
        $this->assertEquals(1, $DB->count_records_select('tool_lifecycle_action_log', "userid = -1"));
    }

    /**
     * all users of context - privacy provider
     * @covers \tool_lifecycle\privacy\provider user in context
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public function test_get_users_in_context(): void {
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();

        $p1 = $this->generator->create_process($c1->id, $this->workflow->id);
        $p2 = $this->generator->create_process($c2->id, $this->workflow->id);

        $processor = new processor();
        $processor->process_courses();

        $this->setUser($u1);
        interaction_manager::handle_interaction($this->emailstep->id, $p1->id, self::ACTION_KEEP);
        interaction_manager::handle_interaction($this->emailstep->id, $p2->id, self::ACTION_KEEP);

        $userlist = new userlist(\context_system::instance(), 'tool_lifecycle');
        provider::get_users_in_context($userlist);
        $this->assertEquals(1, $userlist->count());
        $this->assertEquals($u1->id, $userlist->current()->id);
    }

    /**
     * delete data for *users* - privacy provider
     * @covers \tool_lifecycle\privacy\provider data *users*
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public function test_delete_data_for_users(): void {
        global $DB;
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();

        $proc1 = $this->generator->create_process($c1->id, $this->workflow->id);
        $proc2 = $this->generator->create_process($c2->id, $this->workflow->id);
        $this->setUser($u1);

        $processor = new processor();
        $processor->process_courses();

        interaction_manager::handle_interaction($this->emailstep->id, $proc1->id, self::ACTION_KEEP);

        $this->setUser($u2);
        interaction_manager::handle_interaction($this->emailstep->id, $proc2->id, self::ACTION_KEEP);

        $userlist = new approved_userlist(\context_system::instance(), 'tool_lifecycle', [$u1->id]);
        provider::delete_data_for_users($userlist);
        $this->assertEquals(0, $DB->count_records_select('tool_lifecycle_action_log', "userid = $u1->id"));
        $this->assertEquals(1, $DB->count_records_select('tool_lifecycle_action_log', "userid = $u2->id"));
        $this->assertEquals(1, $DB->count_records_select('tool_lifecycle_action_log', "userid = -1"));
    }

}
