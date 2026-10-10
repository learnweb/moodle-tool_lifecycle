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

namespace lifecycletrigger_opencast;

use tool_lifecycle\trigger\opencast;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../lib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Tests for the Opencast trigger settings form.
 *
 * @package    lifecycletrigger_opencast
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_lifecycle\trigger\opencast
 */
final class opencast_form_test extends \advanced_testcase {
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Markup in an LTI tool name or base URL must not reach the tool picker as HTML.
     */
    public function test_lti_tool_label_is_escaped(): void {
        $label = $this->get_tool_label('<img src=x onerror=alert(1)>Tool', 'https://tool.example.org/</select><b>x</b>');

        $this->assertStringNotContainsString('<img', $label);
        $this->assertStringNotContainsString('</select>', $label);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A regular LTI tool is listed with name and base URL.
     */
    public function test_lti_tool_label_is_shown(): void {
        $label = $this->get_tool_label('Opencast Studio', 'https://opencast.example.org/lti');

        $this->assertSame('Opencast Studio (https://opencast.example.org/lti)', $label);
    }

    /**
     * Create an LTI tool type and return its label in the trigger settings form.
     *
     * @param string $name Tool name
     * @param string $baseurl Tool base URL
     * @return string Option label of the tool
     */
    private function get_tool_label(string $name, string $baseurl): string {
        $this->resetAfterTest();
        $this->setAdminUser();

        $type = (object) [
            'name' => $name,
            'baseurl' => $baseurl,
            'state' => LTI_TOOL_STATE_CONFIGURED,
            'course' => SITEID,
            'coursevisible' => LTI_COURSEVISIBLE_ACTIVITYCHOOSER,
        ];
        $typeid = lti_add_type($type, new \stdClass());

        $mform = new \MoodleQuickForm('opencasttest', 'post', '');
        (new opencast())->extend_add_instance_form_definition($mform);

        foreach ($mform->getElement('ltitools')->_options as $option) {
            if ((int) $option['attr']['value'] === (int) $typeid) {
                return $option['text'];
            }
        }
        $this->fail('LTI tool is not listed.');
    }
}
