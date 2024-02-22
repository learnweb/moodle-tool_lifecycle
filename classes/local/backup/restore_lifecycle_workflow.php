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
 * Class to restore a workflow.
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\backup;

use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\settings_type;

/**
 * Class to restore a workflow.
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_lifecycle_workflow {

    /** @var $workflow workflow */
    private $workflow;
    /** @var $steps step_subplugin[] */
    private $steps = [];
    /** @var $trigger trigger_subplugin[] */
    private $trigger = [];
    /** @var $settings array */
    private $settings = [];
    /** @var $errors string[] errors that occurred during restore.*/
    private $errors = [];
    /** @var $writer \XMLWriter */
    private $reader;

    /**
     * Restore_lifecycle_workflow constructor.
     * @param string $xmldata XML-Data the workflow should be restored from.
     */
    public function __construct($xmldata) {
        $this->reader = new \XMLReader();
        $this->reader->XML($xmldata);
    }

    /**
     * Executes the restore process. It loads the workflow with all steps and triggers from the xml data.
     * If all data is valid, it restores the workflow with all subplugins and settings.
     * Otherwise an array with error strings is returned.
     * @param bool $force force import, even if there are errors.
     * @return string[] Errors, which occurred during the restore process.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function execute(bool $force = false) {
        $this->reader->read();

        $this->load_workflow();
        // If the workflow could be loaded continue with the subplugins.
        if ($this->workflow) {
            $this->load_subplugins();

            if (!$this->all_subplugins_installed()) {
                return $this->errors;
            }

            // Validate the subplugin data.
            $this->check_subplugin_validity();
            if (empty($this->errors) || $force) {
                // If all loaded data is valid, the new workflow and the steps can be stored in the database.
                // If we force the import, we empty the errors;
                $this->errors = [];
                $this->persist();
            }
        }
        return $this->errors;
    }

    /**
     * Load the data of the workflow, stored within the xml data.
     */
    private function load_workflow() {
        if (!$this->reader->name == 'workflow' || !$this->reader->hasAttributes) {
            $this->errors[] = get_string('restore_workflow_not_found', 'tool_lifecycle');
        }
        $tempworkflow = new \stdClass();
        foreach (get_class_vars(workflow::class) as $prop => $value) {
            $tempworkflow->$prop = $this->reader->getAttribute($prop);
        }
        unset($tempworkflow->id);
        $this->workflow = workflow::from_record($tempworkflow);
        $this->workflow->timeactive = null;
        $this->workflow->timedeactive = null;
        $this->workflow->sortindex = null;
    }

    /**
     * Load the data of the subplugins, stored within the xml data.
     */
    private function load_subplugins() {
        $currentsubplugin = null;
        $currenttype = null;
        while ($this->reader->read()) {
            $tag = $this->reader->name;
            if ($tag == '#text' || $this->reader->nodeType == \XMLReader::END_ELEMENT) {
                continue;
            }
            if ((!$tag == 'step' && !$tag == 'trigger' && !$tag == 'setting')
                || !$this->reader->hasAttributes) {
                $this->errors[] = get_string('restore_subplugins_not_found', 'tool_lifecycle');
            }
            switch ($tag) {
                case 'trigger':
                    $currentsubplugin = new \stdClass();
                    $currenttype = settings_type::TRIGGER;
                    foreach (get_class_vars(trigger_subplugin::class) as $prop => $value) {
                        $currentsubplugin->$prop = $this->reader->getAttribute($prop);
                    }
                    $this->trigger[] = trigger_subplugin::from_record($currentsubplugin);
                    break;
                case 'step':
                    $currentsubplugin = new \stdClass();
                    $currenttype = settings_type::STEP;
                    foreach (get_class_vars(step_subplugin::class) as $prop => $value) {
                        $currentsubplugin->$prop = $this->reader->getAttribute($prop);
                    }
                    $this->steps[] = step_subplugin::from_record($currentsubplugin);
                    break;
                case 'setting':
                    $setting = new \stdClass();
                    $setting->name = $this->reader->getAttribute('name');
                    $setting->pluginid = $currentsubplugin->id;
                    $setting->type = $currenttype;
                    $setting->value = $this->reader->getAttribute('value');
                    $this->settings[] = $setting;
            }
        }
    }

    /**
     * Checks if all subplugins loaded from the backup file are installed in the system.
     * If subplugins are missing, an error string is appended to the $error array,
     * which can be used for displaying to the user later.
     * @return bool true, if all subplugins are installed; false otherwise.
     * @throws \coding_exception
     */
    private function all_subplugins_installed() {
        $installedsteps = \core_component::get_plugin_list('lifecyclestep');
        foreach ($this->steps as $step) {
            if (!array_key_exists($step->subpluginname, $installedsteps)) {
                $this->errors[] = get_string('restore_step_does_not_exist', 'tool_lifecycle', $step->subpluginname);
            }
        }
        $installedtrigger = \core_component::get_plugin_list('lifecycletrigger');
        foreach ($this->trigger as $trigger) {
            if (!array_key_exists($trigger->subpluginname, $installedtrigger)) {
                $this->errors[] = get_string('restore_trigger_does_not_exist',
                    'tool_lifecycle', $trigger->subpluginname);
            }
        }
        if (count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Calls the subplugins to check the consistency and validity of the step and trigger settings.
     */
    private function check_subplugin_validity() {
        foreach ($this->steps as $step) {
            $steplib = lib_manager::get_step_lib($step->subpluginname);
            $filteredsettings = [];
            foreach ($this->settings as $setting) {
                if ($setting->pluginid === $step->id) {
                    $filteredsettings[$setting->name] = $setting->value;
                }
            }
            $errors = array_map(
                    fn($x) => get_string('restore_error_in_step', 'tool_lifecycle', $step->instancename) . $x,
                    $steplib->ensure_validity($filteredsettings)
            );
            $this->errors = array_merge($this->errors, $errors);
        }

        foreach ($this->trigger as $trigger) {
            $steplib = lib_manager::get_trigger_lib($trigger->subpluginname);
            $filteredsettings = [];
            foreach ($this->settings as $setting) {
                if ($setting->pluginid === $trigger->id) {
                    $filteredsettings[$setting->name] = $setting->value;
                }
            }
            $errors = array_map(
                    fn($x) => get_string('restore_error_in_trigger', 'tool_lifecycle', $trigger->instancename) . $x,
                    $steplib->ensure_validity($filteredsettings)
            );
            $this->errors = array_merge($this->errors, $errors);
        }
    }

    /**
     * Stores all loaded data in the database.
     * @throws \moodle_exception
     */
    private function persist() {
        workflow_manager::insert_or_update($this->workflow);
        usort($this->steps, function($a, $b) {
            return $a->sortindex - $b->sortindex;
        });
        foreach ($this->steps as $step) {
            $step->workflowid = $this->workflow->id;
            $stepid = $step->id;
            $step->id = null;
            step_manager::insert_or_update($step);
            foreach ($this->settings as $setting) {
                if ($setting->type == settings_type::STEP &&
                    $setting->pluginid == $stepid) {
                    settings_manager::save_setting($step->id, settings_type::STEP, $step->subpluginname,
                        $setting->name, $setting->value);
                }
            }
        }
        usort($this->trigger, function($a, $b) {
            return $a->sortindex - $b->sortindex;
        });
        foreach ($this->trigger as $trigger) {
            $trigger->workflowid = $this->workflow->id;
            $triggerid = $trigger->id;
            $trigger->id = null;
            trigger_manager::insert_or_update($trigger);
            foreach ($this->settings as $setting) {
                if ($setting->type == settings_type::TRIGGER &&
                    $setting->pluginid == $triggerid) {
                    settings_manager::save_setting($trigger->id, settings_type::TRIGGER, $trigger->subpluginname,
                        $setting->name, $setting->value);
                }
            }
        }
    }

    /**
     * Returns the workflow in case there were no errors.
     * @return workflow
     */
    public function get_workflow() {
        if (empty($this->errors)) {
            return $this->workflow;
        }
        return null;
    }
}
