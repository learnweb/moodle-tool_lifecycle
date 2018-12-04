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
 * @package tool_lifecycle_trigger
 * @subpackage specificdate
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use coursecat;
use DateTime;
use tool_lifecycle\manager\settings_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\response\trigger_response;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanyp courses trigger subplugin
 * @package tool_lifecycle_trigger
 */
class specificdate extends base_automatic {

    private $alreadychecked = array();

    /**
     * Checks the course and returns a repsonse, which tells if the course should be further processed.
     * @param $course object to be processed.
     * @param $triggerid int id of the trigger instance.
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        if (!array_key_exists($triggerid, $this->alreadychecked)) {
            $settings = settings_manager::get_settings($triggerid, SETTINGS_TYPE_TRIGGER);
            $lastrun = getdate($settings['timelastrun']);
            $datesraw = $settings['dates'];
            $dates = $this->parse_dates($datesraw);

            $triggerat = array();

            foreach ($dates as $date) {
                if ($date['mon'] > $lastrun['mon']) {
                    $date = new DateTime($lastrun['year'].'-'.$date['mon'].'-'.$date['day']);
                }
                if ($date['mon'] === $lastrun['mon']) {
                    if ($date['day'] > $lastrun['day']) {
                        $date = new DateTime($lastrun['year'].'-'.$date['mon'].'-'.$date['day']);
                    } else {
                        $date = new DateTime(($lastrun['year'] + 1) .'-'.$date['mon'].'-'.$date['day']);
                    }
                } else {
                    $date = new DateTime(($lastrun['year'] + 1) .'-'.$date['mon'].'-'.$date['day']);
                }

                $triggerat [] = $date->getTimestamp();
            }

            sort($triggerat);

            $current = time();

            foreach ($triggerat as $timestamp) {
                if ($timestamp < $current) {
                    $this->alreadychecked[$triggerid] = trigger_response::trigger();
                    $settings['timelastrun'] = $current;
                    $trigger = trigger_manager::get_instance($triggerid);
                    settings_manager::save_settings($triggerid, SETTINGS_TYPE_TRIGGER, $trigger->subpluginname, $settings);
                    return $this->alreadychecked[$triggerid];
                }
            }
            $this->alreadychecked[$triggerid] = trigger_response::next();
        }
        return $this->alreadychecked[$triggerid];
    }

    /**
     * Parses the dates settings to actual date objects.
     * @param $datesraw string
     */
    private function parse_dates($datesraw) {
        $dates = preg_split('/\r\n|\r|\n/', $datesraw);
        $result = array();
        foreach ($dates as $date) {
            $dateparts = explode('.', $date);
            if (count($dateparts) !== 2) {
                throw new \moodle_exception("Each date has to consist of two parts devided by point. We got: " . $date);
            }
            $result [] = array(
                'mon' => $dateparts[1],
                'day' => $dateparts[0]
            );
        }
        return $result;
    }

    public function get_subpluginname() {
        return 'specificdate';
    }

    public function instance_settings() {
        return array(
            new instance_setting('dates', PARAM_TEXT),
        );
    }

    public function extend_add_instance_form_definition($mform) {
        $mform->addElement('textarea', 'dates', get_string('dates', 'lifecycletrigger_specificdate'),
            get_string('dates_desc', 'lifecycletrigger_specificdate'));
        $mform->setType('categories', PARAM_TEXT);
        $mform->addElement('hidden', 'timelastrun');
        $mform->setDefault('timelastrun', time());
        $mform->setType('timelastrun', PARAM_INT);
    }

    public function extend_add_instance_form_validation(&$error, $data) {
        $dates = preg_split('/\r\n|\r|\n/', $data['dates']);
        foreach ($dates as $date) {
            if (count(explode('.', $date)) !== 2) {
                $error['dates'] = get_string('dates_not_parseable', 'lifecycletrigger_specificdate');
            }
        }
    }

}
