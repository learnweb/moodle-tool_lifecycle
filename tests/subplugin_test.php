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

namespace tool_lifecycle;

use tool_lifecycle\local\manager\lib_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;

/**
 * Tests the subplugin handling.
 * @package    tool_lifecycle
 * @category   test
 * @group      tool_lifecycle
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class subplugin_test extends \advanced_testcase {

    /**
     * Tests that all builtin triggers are registered and loadable.
     *
     * @covers \tool_lifecycle\local\manager\trigger_manager::get_trigger_types
     * @covers \tool_lifecycle\local\manager\lib_manager::get_trigger_lib
     */
    public function test_builtin_triggers(): void {
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

    /**
     * Tests that all builtin steps are registered and loadable.
     *
     * @covers \tool_lifecycle\local\manager\step_manager::get_step_types
     * @covers \tool_lifecycle\local\manager\lib_manager::get_step_lib
     */
    public function test_builtin_steps(): void {
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

    /**
     * Tests that additional external triggers can be registered and loaded.
     *
     * @covers \tool_lifecycle\local\manager\trigger_manager::get_trigger_types
     * @covers \tool_lifecycle\local\manager\lib_manager::get_trigger_lib
     */
    public function test_additional_triggers(): void {
        $this->resetAfterTest();

        // Add a fake tool plugin, which define a trigger.
        $mockedcomponent = new \ReflectionClass(\core_component::class);
        $mockedplugins = $mockedcomponent->getProperty('plugins');
        $mockedplugins->setAccessible(true);
        $plugins = $mockedplugins->getValue();
        $plugins['tool'] += ['sampletrigger' => __DIR__ . '/fixtures/fakeplugins/sampletrigger'];
        $mockedplugins->setValue(null, $plugins);

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
        $mockedplugins->setValue(null, $plugins);
    }

    /**
     * Tests that additional external steps and their interaction libs can be registered and loaded.
     *
     * @covers \tool_lifecycle\local\manager\step_manager::get_step_types
     * @covers \tool_lifecycle\local\manager\lib_manager::get_step_lib
     * @covers \tool_lifecycle\local\manager\lib_manager::get_step_interactionlib
     */
    public function test_additional_steps(): void {
        $this->resetAfterTest();

        // Add a fake tool plugin, which define a step.
        $mockedcomponent = new \ReflectionClass(\core_component::class);
        $mockedplugins = $mockedcomponent->getProperty('plugins');
        $mockedplugins->setAccessible(true);
        $plugins = $mockedplugins->getValue();
        $plugins['tool'] += ['samplestep' => __DIR__ . '/fixtures/fakeplugins/samplestep'];
        $mockedplugins->setValue(null, $plugins);

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
        $mockedplugins->setValue(null, $plugins);
    }

}
