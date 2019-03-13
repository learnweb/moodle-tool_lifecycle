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

namespace tool_lifecycle\trigger;
defined('MOODLE_INTERNAL') || die();

use tool_lifecycle\response\trigger_response;
use core_course_category;

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');


/**
 * Trigger test for start date delay trigger.
 *
 * @package    tool_lifecycle_trigger
 * @category   category
 * @group tool_lifecycle_trigger
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_category_testcase extends \advanced_testcase {

    private $triggerinstance;
    private $category1;
    private $category2;

    public function setUp() {
        global $CFG;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $data = new \stdClass();

        $data->name = 'my category 1';
        $data->description = 'my category 1 description';
        $data->idnumber = '';
        if ($CFG->version >= 2018120300) { // Since Moodle 3.6
            $this->category1 = core_course_category::create($data);
        } else { // Before Moodle 3.6
            require_once($CFG->libdir . '/coursecatlib.php');
            $this->category1 = coursecat::create($data);
        }

        $data->name = 'my category 2';
        $data->description = 'my category 2 description';
        $data->idnumber = '';
        if ($CFG->version >= 2018120300) { // Since Moodle 3.6
            $this->category2 = core_course_category::create($data);
        } else { // Before Moodle 3.6
            $this->category2 = core_course_category::create($data);
        }

        $this->triggerinstance = \tool_lifecycle_trigger_category_generator::create_trigger_with_workflow($this->category1->id);
    }

    /**
     * Tests if courses, which are not in the test category are not triggered by this plugin.
     */
    public function test_wrong_category_course() {
        $course = $this->getDataGenerator()->create_course(array('category' => $this->category2->id));

        $trigger = new category();
        $response = $trigger->check_course($course, $this->triggerinstance->id);
        $this->assertEquals($response, trigger_response::next());
    }

    /**
     * Tests if courses, which are in the test category are triggered by this plugin.
     */
    public function test_right_category_course() {
        $course = $this->getDataGenerator()->create_course(array('category' => $this->category1->id));

        $trigger = new category();
        $response = $trigger->check_course($course, $this->triggerinstance->id);
        $this->assertEquals($response, trigger_response::trigger());
    }
}