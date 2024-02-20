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
 * Trigger subplugin which triggers on specific dates only.
 *
 * @package lifecycletrigger_specificdate
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use DateTime;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package lifecycletrigger_specificdate
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class specificdate extends base_automatic {

    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param object $course Course to be processed.
     * @param int $triggerid Id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Everything is already in the sql statement.
        return trigger_response::trigger();
    }

    /**
     * Returns true or false, depending on if the current date is one of the specified days,
     * at which the trigger should run.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \Exception
     */
    public function get_course_recordset_where($triggerid) {
        $settings = settings_manager::get_settings($triggerid, settings_type::TRIGGER);
        $datesraw = $settings['dates'];
        $dates = $this->parse_dates($datesraw);
        $lastrun = getdate($settings['timelastrun']);
        $current = time();
        $today = getdate($current);

        foreach ($dates as $date) {
            // We want to trigger only if the $date is today.
            if ($date['mon'] == $today['mon'] && $date['day'] == $today['mday']) {
                // Now only make sure if $lastrun was today -> don't trigger.
                if ($lastrun['yday'] == $today['yday'] && $lastrun['year'] == $today['year']) {
                    continue;
                } else {
                    $settings['timelastrun'] = $current;
                    $trigger = trigger_manager::get_instance($triggerid);
                    settings_manager::save_settings($triggerid, settings_type::TRIGGER, $trigger->subpluginname, $settings);
                    return ['true', []];
                }
            }
        }
        return ['false', []];
    }

    /**
     * Parses the dates settings to actual date objects.
     * @param string $datesraw Raw data from the form representing dates.
     * @return array
     * @throws \moodle_exception
     */
    private function parse_dates($datesraw) {
        $dates = preg_split('/\r\n|\r|\n/', $datesraw);
        $result = [];
        foreach ($dates as $date) {
            $dateparts = explode('.', $date);
            if (count($dateparts) !== 2) {
                throw new \moodle_exception("Each date has to consist of two parts devided by point. We got: " . $date);
            }
            $result[] = [
                'mon' => $dateparts[1],
                'day' => $dateparts[0],
            ];
        }
        return $result;
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'specificdate';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('dates', PARAM_TEXT),
            new instance_setting('timelastrun', PARAM_INT),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $mform->addElement('textarea', 'dates', get_string('dates', 'lifecycletrigger_specificdate'));
        $mform->setType('dates', PARAM_TEXT);
        $mform->addHelpButton('dates', 'dates', 'lifecycletrigger_specificdate');
        $mform->addElement('hidden', 'timelastrun');
        $mform->setDefault('timelastrun', 0);
        $mform->setType('timelastrun', PARAM_INT);
    }

    /**
     * Validate parsable dates.
     * @param array $error Array containing all errors.
     * @param array $data Data passed from the moodle form to be validated.
     * @throws \coding_exception
     */
    public function extend_add_instance_form_validation(&$error, $data) {
        $dates = preg_split('/\r\n|\r|\n/', $data['dates']);
        foreach ($dates as $date) {
            if (count(explode('.', $date)) !== 2) {
                $error['dates'] = get_string('dates_not_parseable', 'lifecycletrigger_specificdate');
            }
        }
    }

}
