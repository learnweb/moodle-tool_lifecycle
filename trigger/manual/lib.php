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
 * @subpackage manual
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\trigger;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a cleanup courses trigger subplugin
 * @package tool_cleanupcourses_trigger
 */
class manual extends base_manual {

    public function get_subpluginname() {
        return 'manual';
    }

    /**
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [new instance_setting('icon',  PARAM_SAFEPATH),
            new instance_setting('displayname',  PARAM_TEXT),
            new instance_setting('capability',  PARAM_CAPABILITY),
            ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'icon';
        $mform->addElement(
            'text', $elementname, get_string('setting_icon','cleanupcoursestrigger_manual')
        );
        $mform->setType($elementname, PARAM_SAFEPATH);

        $elementname = 'displayname';
        $mform->addElement(
            'text', $elementname, get_string('setting_displayname','cleanupcoursestrigger_manual')
        );
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'capability';
        $mform->addElement(
            'text', $elementname, get_string('setting_capability','cleanupcoursestrigger_manual')
        );
        $mform->setType($elementname, PARAM_CAPABILITY);
    }

}
