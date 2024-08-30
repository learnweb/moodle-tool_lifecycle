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
 * Trigger subplugin for manual triggers.
 *
 * @package lifecycletrigger_manual
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements the basic methods necessary for a life cycle trigger subplugin
 * @package lifecycletrigger_manual
 * @copyright  2018 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manual extends base_manual {

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'manual';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
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
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'icon';
        $mform->addElement(
            'text', $elementname, get_string('setting_icon', 'lifecycletrigger_manual')
        );
        $mform->addHelpButton($elementname, 'setting_icon', 'lifecycletrigger_manual');
        $mform->setType($elementname, PARAM_SAFEPATH);

        $elementname = 'displayname';
        $mform->addElement(
            'text', $elementname, get_string('setting_displayname', 'lifecycletrigger_manual')
        );
        $mform->addHelpButton($elementname, 'setting_displayname', 'lifecycletrigger_manual');
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'capability';
        $capabilities = get_all_capabilities();
        $capabilitynames = [];
        foreach ($capabilities as $cap) {
            $capabilitynames[$cap['name']] = $cap['name'];
        }
        $mform->addElement(
            'autocomplete', $elementname, get_string('setting_capability', 'lifecycletrigger_manual'),
            $capabilitynames
        );
        $mform->addHelpButton($elementname, 'setting_capability', 'lifecycletrigger_manual');
        $mform->setType($elementname, PARAM_CAPABILITY);
    }

    /**
     * Make all fields required.
     * @param array $error Array containing all errors.
     * @param array $data Data passed from the moodle form to be validated.
     * @return void the extended error array.
     * @throws \coding_exception
     */
    public function extend_add_instance_form_validation(&$error, $data) {
        parent::extend_add_instance_form_validation($error, $data);
        $requiredsettings = $this->instance_settings();
        foreach ($requiredsettings as $setting) {
            if (!array_key_exists($setting->name, $data) || empty($data[$setting->name])) {
                $error[$setting->name] = get_string('required');
            }
        }
    }

}
