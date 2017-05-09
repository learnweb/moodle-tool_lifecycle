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

namespace tool_cleanupcourses\trigger;

use tool_cleanupcourses\TriggerResponse;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Trigger test for start date delay trigger.
 *
 * @package    tool_cleanupcourses_trigger
 * @category   startdatedelay
 * @group tool_cleanupcourses_trigger
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupcourses_trigger_startdatedelay_testcase extends \advanced_testcase {

    /**
     * Tests if courses, which are newer than the default of 190 days are not triggered by this plugin.
     */
    public function test_young_course() {

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(array('startdate' => time() - 50 * 24 * 60 * 60));

        $trigger = new startdatedelay_trigger();
        $response = $trigger->check_course($course);
        $this->assertEquals($response, TriggerResponse::next());

    }

    /**
     * Tests if courses, which are older than the default of 190 days are triggered by this plugin.
     */
    public function test_old_course() {

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(array('startdate' => time() - 200 * 24 * 60 * 60));

        $trigger = new startdatedelay_trigger();
        $response = $trigger->check_course($course);
        $this->assertEquals($response, TriggerResponse::trigger());

    }
}