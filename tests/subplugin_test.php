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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/upgradelib.php');

use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;


class subplugin_test extends \advanced_testcase {

    public function test_builtin_triggers() {
        $this->resetAfterTest();
        $builtintriggers = \core_component::get_plugin_list('lifecycletrigger');
        $triggers = trigger_manager::get_trigger_types();

        // All builtin triggers are also in the list of triggers.
        $this->assertEmpty(array_diff_key($builtintriggers, $triggers));

        // Trigger classes are loadable.
        foreach ($builtintriggers as $triggername => $triggerpath) {
            $this->assertNotEmpty(lib_manager::get_trigger_lib($triggername));
        }
    }

    public function test_builtin_steps() {
        $this->resetAfterTest();
        $builtinsteps = \core_component::get_plugin_list('lifecyclestep');
        $steps = step_manager::get_step_types();

        // All builtin steps are also in the list of steps.
        $this->assertEmpty(array_diff_key($builtinsteps, $steps));

        // Step classes are loadable.
        foreach ($builtinsteps as $stepname => $steppath) {
            $this->assertNotEmpty(lib_manager::get_step_lib($stepname));
        }
    }

    public function test_additional_triggers() {
        $this->resetAfterTest();

        // Add a fake tool plugin, which define a trigger.
        $mockedcomponent = new ReflectionClass(\core_component::class);
        $mockedplugins = $mockedcomponent->getProperty('plugins');
        $mockedplugins->setAccessible(true);
        $plugins = $mockedplugins->getValue();
        $plugins['tool'] += ['sampletrigger' => __DIR__ . '/fixtures/fakeplugins/sampletrigger'];
        $mockedplugins->setValue($plugins);

        // The 'fixture' is not autoloaded, so we need to require it.
        require_once(__DIR__ . '/fixtures/fakeplugins/sampletrigger/classes//lifecycle/trigger.php');

        // Check if the trigger is available.
        $triggers = trigger_manager::get_trigger_types();
        $this->assertArrayHasKey('tool_sampletrigger', $triggers);

        // Lib is loadable.
        $this->assertInstanceOf('tool_sampletrigger\lifecycle\trigger',
            lib_manager::get_trigger_lib('tool_sampletrigger'));

        // Unset the fake plugin.
        unset($plugins['tool']['sampletrigger']);
        $mockedplugins->setValue($plugins);
    }

    public function test_additional_steps() {
        $this->resetAfterTest();

        // Add a fake tool plugin, which define a step.
        $mockedcomponent = new ReflectionClass(\core_component::class);
        $mockedplugins = $mockedcomponent->getProperty('plugins');
        $mockedplugins->setAccessible(true);
        $plugins = $mockedplugins->getValue();
        $plugins['tool'] += ['samplestep' => __DIR__ . '/fixtures/fakeplugins/samplestep'];
        $mockedplugins->setValue($plugins);

        // The 'fixture'  is not autoloaded, so we need to require it.
        require_once(__DIR__ . '/fixtures/fakeplugins/samplestep/classes//lifecycle/step.php');
        require_once(__DIR__ . '/fixtures/fakeplugins/samplestep/classes//lifecycle/interaction.php');

        // Check if the step is available.
        $steps = step_manager::get_step_types();
        $this->assertArrayHasKey('tool_samplestep', $steps);

        // Lib is loadable.
        $this->assertInstanceOf('tool_samplestep\lifecycle\step',
            lib_manager::get_step_lib('tool_samplestep'));

        // Lib subtype is loadable.
        $this->assertInstanceOf('tool_samplestep\lifecycle\interaction',
            lib_manager::get_step_interactionlib('tool_samplestep'));

        // Unset the fake plugin.
        unset($plugins['tool']['samplestep']);
        $mockedplugins->setValue($plugins);
    }

}
