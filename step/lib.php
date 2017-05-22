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
 * Interface for the subplugintype step
 * It has to be implemented by all subplugins.
 *
 * @package tool_cleanupcourses
 * @subpackage step
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\step;

use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\response\step_response;

defined('MOODLE_INTERNAL') || die();

abstract class base {


    /**
     * Processes the course and returns a repsonse.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param $course object to be processed.
     * @return step_response
     */
    public abstract function process_course($course);

    public abstract function get_subpluginname();

    /**
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return array();
    }

    public function get_settings($instanceid) {
        global $DB;
        $manager = new step_manager();
        $stepinstance = $manager->get_step_instance($instanceid);

        if (!$stepinstance || $stepinstance->subpluginname !== $this->get_subpluginname()) {
            return null;
        }

        $settingsvalues = array();
        foreach ($this->instance_settings() as $setting) {
            $record = $DB->get_record('tool_cleanupcourses_settings',
                array('instanceid' => $instanceid,
                    'name' => $setting->name));
            if ($record) {
                $value = clean_param($record->value, $setting->paramtype);
                $settingsvalues[$setting->name] = $value;
            }
        }
        return $settingsvalues;
    }

}

class instance_setting {

    public $name;

    public $paramtype;

    public function __construct($name, $paramtype) {
        $this->name = $name;
        $this->paramtype = $paramtype;
    }

}