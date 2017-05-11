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
            $this->insert_or_update($subplugin);
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
     * Changes the state of a subplugin.
     * @param int $subpluginid id of the subplugin
     * @param bool $enabled new state
     */
    public function change_enabled($subpluginid, $enabled) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $subplugin = $this->get_subplugin_by_id($subpluginid);
        if ($subplugin) {
            $subplugin->enabled = $enabled;
            if ($enabled) {
                $subplugin->sortindex = $this->count_enabled_trigger() + 1;
            } else {
                if (isset($subplugin->sortindex)) {
                    $this->remove_from_sortindex($subplugin);
                }
            }
            $this->insert_or_update($subplugin);
        }
        $transaction->allow_commit();
    }

    /**
     * Changes the sortindex of a subplugin by swapping it with another.
     * @param int $subpluginid id of the subplugin
     * @param bool $up tells if the subplugin should be set up or down
     */
    public function change_sortindex($subpluginid, $up) {
        global $DB;
        $subplugin = $this->get_subplugin_by_id($subpluginid);
        if ($subplugin->sortindex === 1 && $up) {
            return;
        }
        if ($subplugin->sortindex === $this->count_enabled_trigger() && !$up) {
            return;
        }
        $index = $subplugin->sortindex;
        if ($up) {
            $otherindex = $index - 1;
        } else {
            $otherindex = $index + 1;
        }
        $transaction = $DB->start_delegated_transaction();

        $otherrecord = $DB->get_record('tool_cleanupcourses_plugin', array('sortindex' => $otherindex));
        $othersubplugin = $subplugin::from_record($otherrecord);

        $subplugin->sortindex = $otherindex;
        $othersubplugin->sortindex = $index;
        $this->insert_or_update($subplugin);
        $this->insert_or_update($othersubplugin);

        $transaction->allow_commit();
    }

    /**
     * Removes a subplugin from the sortindex and adjusts all other indizes.
     * @param subplugin $toberemoved
     */
    private function remove_from_sortindex(&$toberemoved) {
        global $DB;
        $subplugins = $DB->get_records_select('tool_cleanupcourses_plugin', "sortindex > $toberemoved->sortindex");
        foreach ($subplugins as $record) {
            $subplugin = subplugin::from_record($record);
            $subplugin->sortindex--;
            $this->insert_or_update($subplugin);
        }
        $toberemoved->sortindex = null;
    }

    /**
     * Returns a subplugin object.
     * @param int $subpluginid id of the subplugin
     * @return subplugin
     */
    private function get_subplugin_by_id($subpluginid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_plugin', array('id' => $subpluginid));
        $subplugin = subplugin::from_record($record);
        return $subplugin;
    }

    /**
     * Persists a subplugin to the database.
     * @param subplugin $subplugin
     */
    private function insert_or_update(subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($subplugin->id !== null) {
            $DB->update_record('tool_cleanupcourses_plugin', $subplugin);
        }
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

    /**
     * Gets the count of currently enabled trigger subplugins.
     * @return int count of enabled trigger subplugins.
     */
    public function count_enabled_trigger(){
        global $DB;
        return $DB->count_records('tool_cleanupcourses_plugin',
            array(
                'enabled' => 1,
                'type' => 'cleanupcoursestrigger')
        );
    }

}
