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

namespace lifecycletrigger_specificdate;

use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\processor;
use tool_lifecycle\settings_type;

/**
 * Tests that only the lifecycle cron consumes the daily run of the specificdate trigger.
 *
 * @package    lifecycletrigger_specificdate
 * @category   test
 * @copyright  2026 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class specificdate_test extends \advanced_testcase {
    /** @var \stdClass Course under test. */
    private $course;

    /** @var \tool_lifecycle\local\entity\trigger_subplugin Trigger configured for today. */
    private $trigger;

    /**
     * Creates an active workflow with a specificdate trigger that is due today.
     */
    private function create_workflow(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $workflow = $generator->create_workflow([], ['createbackup']);
        $this->trigger = $generator->create_trigger('specificdate', 'specificdate', $workflow->id);
        settings_manager::save_settings($this->trigger->id, settings_type::TRIGGER, 'specificdate', [
            'dates' => date('j.n'),
            'timelastrunactive' => 1,
            'timelastrun' => 0,
        ]);
        $DB->set_field('tool_lifecycle_workflow', 'timeactive', time(), ['id' => $workflow->id]);
        $this->course = $this->getDataGenerator()->create_course();
    }

    /**
     * Building the course list for a page view must not use up the run of the day.
     *
     * @covers \tool_lifecycle\trigger\specificdate::get_course_recordset_where
     */
    public function test_page_view_does_not_consume_daily_run(): void {
        $this->create_workflow();
        $processor = new processor();

        // Pages such as the remaining courses table of view.php build the course list like this.
        $processor->get_course_recordset([$this->trigger])->close();
        $this->assertEquals(0, settings_manager::get_settings($this->trigger->id, settings_type::TRIGGER)['timelastrun']);

        $processor->call_trigger();
        $this->assertNotNull(process_manager::get_process_by_course_id($this->course->id));
    }

    /**
     * The cron run still fires once a day and records it.
     *
     * @covers \tool_lifecycle\trigger\specificdate::get_course_recordset_where
     */
    public function test_cron_run_is_recorded(): void {
        $this->create_workflow();

        (new processor())->call_trigger();
        $this->assertNotNull(process_manager::get_process_by_course_id($this->course->id));
        $this->assertGreaterThan(0, settings_manager::get_settings($this->trigger->id, settings_type::TRIGGER)['timelastrun']);
        $lib = lib_manager::get_automatic_trigger_lib('specificdate');
        $this->assertSame(['false', []], $lib->get_course_recordset_where($this->trigger->id));
    }
}
