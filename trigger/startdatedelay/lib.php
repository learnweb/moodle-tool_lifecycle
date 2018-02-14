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
 * Interface for the subplugintype trigger
 * It has to be implemented by all subplugins.
 *
 * @package tool_cleanupcourses_trigger
 * @subpackage startdatedelay
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\trigger;

use tool_cleanupcourses\response\trigger_response;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package tool_cleanupcourses_trigger
 */
class startdatedelay extends base {


    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param $course object to be processed.
     * @return trigger_response
     */
    public function check_course($course) {
        global $CFG;
        $delay = $CFG->cleanupcoursestrigger_startdatedelay_delay;
        $now = time();
        if ($course->startdate + $delay < $now) {
            return trigger_response::trigger();
        }
        return trigger_response::next();
    }

    public function get_subpluginname() {
        return 'startdatedelay';
    }

    public function instance_settings() {
        return array(
            new instance_setting('delay', PARAM_INT)
        );
    }

    public function extend_add_instance_form_definition($mform) {
        $mform->addElement('duration', 'delay', get_string('delay', 'cleanupcoursestrigger_startdatedelay'));
    }

    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings) && array_key_exists('delay', $settings)) {
            $default = $settings['delay'];
        } else {
            $default = 16416000;
        }
        $mform->setDefault('delay', $default);
    }
}
