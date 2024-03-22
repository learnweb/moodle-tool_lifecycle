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
 * Trigger test for course site trigger.
 *
 * @package    lifecycletrigger_sitecourse
 * @group      lifecycletrigger
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecycletrigger_sitecourse;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\trigger\sitecourse;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

/**
 * Trigger test for course site trigger.
 *
 * @package    lifecycletrigger_sitecourse
 * @group      lifecycletrigger
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class trigger_test extends \advanced_testcase {

    /** @var trigger_subplugin $triggerinstance Instance of the trigger. */
    private $triggerinstance;

    /**
     * Setup the testcase.
     * @throws coding_exception
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->triggerinstance = \tool_lifecycle_trigger_sitecourse_generator::create_trigger_with_workflow();
    }

    /**
     * Tests if the site course is excluded by this plugin.
     * @covers \tool_lifecycle\processor \tool_lifecycle\trigger\sitecourse
     */
    public function test_sitecourse_course(): void {

        $course = get_site();

        $trigger = new sitecourse();
        $response = $trigger->check_course($course, $this->triggerinstance->id);
        $this->assertEquals($response, trigger_response::exclude());

    }

    /**
     * Tests if courses, which are older than the default of 190 days are triggered by this plugin.
     * @covers \tool_lifecycle\processor \tool_lifecycle\trigger\sitecourse
     */
    public function test_normal_course(): void {

        $course = $this->getDataGenerator()->create_course();

        $trigger = new sitecourse();
        $response = $trigger->check_course($course, $this->triggerinstance->id);
        $this->assertEquals($response, trigger_response::next());

    }
}
