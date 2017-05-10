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
 * Manager for Subplugins
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses;

defined('MOODLE_INTERNAL') || die();

class subplugin_manager {

    /**
     * Registers a trigger subplugin.
     * This has to be called, when installing trigger plugins!
     * @param string $subpluginname name of the plugin
     */
    public function register_trigger($subpluginname) {
        $this->register_subplugin($subpluginname, 'cleanupcoursestrigger');
    }

    /**
     * Deregisters a trigger subplugin.
     * This has to be called, when uninstalling trigger plugins!
     * @param string $subpluginname name of the plugin
     */
    public function deregister_trigger($subpluginname) {
        $this->deregister_subplugin($subpluginname, 'cleanupcoursestrigger');
    }

    /**
     * Determines if there exists a subplugin for the given name and type
     * @param $subpluginname
     * @param $subplugintype
     * @return bool
     */
    private function is_subplugin($subpluginname, $subplugintype) {
        $subplugintypes = \core_component::get_subplugins('tool_cleanupcourses');
        if (array_key_exists($subplugintype, $subplugintypes)) {
            $subplugins = $subplugintypes[$subplugintype];
            if (in_array($subpluginname, $subplugins)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Registers a subplugin.
     * @param string $subpluginname name of the plugin
     * @param string $subplugintype type of the plugin
     */
    private function register_subplugin($subpluginname, $subplugintype) {
        if ($this->is_subplugin($subpluginname, $subplugintype)) {
            $subplugin = new subplugin($subpluginname, $subplugintype);
            $this->persist($subplugin);
        }
    }

    /**
     * Deregisters a subplugin.
     * @param string $subpluginname name of the plugin
     * @param string $subplugintype type of the plugin
     */
    private function deregister_subplugin($subpluginname, $subplugintype) {
        if ($this->is_subplugin($subpluginname, $subplugintype)) {
            $subplugin = new subplugin($subpluginname, $subplugintype);
            $this->remove($subplugin);
        }
    }

    /**
     * Persists a subplugin to the database.
     * @param subplugin $subplugin
     */
    private function persist(subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $record = array(
            'name' => $subplugin->name,
            'type' => $subplugin->type,
        );
        if (!$DB->record_exists('tool_cleanupcourses_plugin', $record)) {
            $subplugin->id = $DB->insert_record('tool_cleanupcourses_plugin', $record);
            $record = $DB->get_record('tool_cleanupcourses_plugin', (array) $subplugin);
            $subplugin = subplugin::from_record($record);
        }
        $transaction->allow_commit();
    }

    /**
     * Removes a subplugin from the database.
     * @param subplugin $subplugin
     */
    private function remove(subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $record = array(
            'name' => $subplugin->name,
            'type' => $subplugin->type,
        );
        if ($record = $DB->get_record('tool_cleanupcourses_plugin', $record)) {
            $DB->delete_records('tool_cleanupcourses_plugin', (array) $record);
            $subplugin = subplugin::from_record($record);
        }
        $transaction->allow_commit();
    }

}
