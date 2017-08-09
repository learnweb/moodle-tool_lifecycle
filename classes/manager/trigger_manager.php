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
 * Manager for Trigger subplugins
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\manager;

use tool_cleanupcourses\entity\trigger_subplugin;

defined('MOODLE_INTERNAL') || die();

class trigger_manager extends subplugin_manager {

    /**
     * Registers a trigger subplugin.
     * This has to be called, when installing trigger plugins!
     * @param string $subpluginname name of the plugin
     */
    public static function register($subpluginname) {
        if (self::is_subplugin($subpluginname, 'cleanupcoursestrigger')) {
            $subplugin = new trigger_subplugin($subpluginname);
            self::insert_or_update($subplugin);
        }
    }

    /**
     * Deregisters a trigger subplugin.
     * This has to be called, when uninstalling trigger plugins!
     * @param string $subpluginname name of the plugin
     */
    public static function deregister($subpluginname) {
        if (self::is_subplugin($subpluginname, 'cleanupcoursestrigger')) {
            $subplugin = new trigger_subplugin($subpluginname);
            self::remove($subplugin);
        }
    }

    /**
     * Changes the state of a subplugin.
     * @param int $subpluginid id of the subplugin
     * @param bool $enabled new state
     */
    public static function change_enabled($subpluginid, $enabled) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $subplugin = self::get_subplugin_by_id($subpluginid);
        if ($subplugin) {
            $subplugin->enabled = $enabled;
            if ($enabled) {
                $subplugin->sortindex = self::count_enabled_trigger() + 1;
            } else {
                self::remove_from_sortindex($subplugin);
            }
            self::insert_or_update($subplugin);
        }
        $transaction->allow_commit();
    }

    /**
     * Changes the sortindex of a subplugin by swapping it with another.
     * @param int $subpluginid id of the subplugin
     * @param bool $up tells if the subplugin should be set up or down
     */
    public static function change_sortindex($subpluginid, $up) {
        global $DB;
        $subplugin = self::get_subplugin_by_id($subpluginid);
        // Prevent first entry to be put up even more.
        if ($subplugin->sortindex == 1 && $up) {
            return;
        }
        // Prevent last entry to be put down even more.
        if ($subplugin->sortindex == self::count_enabled_trigger() && !$up) {
            return;
        }
        $index = $subplugin->sortindex;
        if ($up) {
            $otherindex = $index - 1;
        } else {
            $otherindex = $index + 1;
        }
        $transaction = $DB->start_delegated_transaction();

        $otherrecord = $DB->get_record('tool_cleanupcourses_trigger', array('sortindex' => $otherindex));
        $othersubplugin = trigger_subplugin::from_record($otherrecord);

        $subplugin->sortindex = $otherindex;
        $othersubplugin->sortindex = $index;
        self::insert_or_update($subplugin);
        self::insert_or_update($othersubplugin);

        $transaction->allow_commit();
    }

    /**
     * Changes the followedby of a trigger.
     * @param int $subpluginid id of the trigger
     * @param int $followedby id of the step
     */
    public static function change_followedby($subpluginid, $followedby) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        $subplugin = self::get_subplugin_by_id($subpluginid);
        if (!$subplugin) {
            return; // TODO: Throw error.
        }

        $step = step_manager::get_subplugin_by_instance_id($followedby);

        // If step is not defined clear followedby.
        if ($step) {
            $subplugin->followedby = $step->id;
        } else {
            $subplugin->followedby = null;
        }

        self::insert_or_update($subplugin);

        $transaction->allow_commit();
    }

    /**
     * Removes a subplugin from the sortindex and adjusts all other indizes.
     * @param trigger_subplugin $toberemoved
     */
    private static function remove_from_sortindex(&$toberemoved) {
        global $DB;
        if (isset($toberemoved->sortindex)) {
            $subplugins = $DB->get_records_select('tool_cleanupcourses_trigger', "sortindex > $toberemoved->sortindex");
            foreach ($subplugins as $record) {
                $subplugin = trigger_subplugin::from_record($record);
                $subplugin->sortindex--;
                self::insert_or_update($subplugin);
            }
            $toberemoved->sortindex = null;
        }
    }

    /**
     * Returns a subplugin object.
     * @param int $subpluginid id of the subplugin
     * @return trigger_subplugin
     */
    private static function get_subplugin_by_id($subpluginid) {
        global $DB;
        $record = $DB->get_record('tool_cleanupcourses_trigger', array('id' => $subpluginid));
        if ($record) {
            $subplugin = trigger_subplugin::from_record($record);
            return $subplugin;
        } else {
            return null;
        }
    }

    /**
     * Persists a subplugin to the database.
     * @param trigger_subplugin $subplugin
     */
    public static function insert_or_update(trigger_subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if ($subplugin->id !== null) {
            $DB->update_record('tool_cleanupcourses_trigger', $subplugin);
        }
        $record = array(
            'subpluginname' => $subplugin->subpluginname,
        );
        if (!$DB->record_exists('tool_cleanupcourses_trigger', $record)) {
            $subplugin->id = $DB->insert_record('tool_cleanupcourses_trigger', $record);
            $record = $DB->get_record('tool_cleanupcourses_trigger', array('id' => $subplugin->id));
            $subplugin = trigger_subplugin::from_record($record);
        }
        $transaction->allow_commit();
    }

    /**
     * Removes a subplugin from the database.
     * @param trigger_subplugin $subplugin
     */
    private static function remove(trigger_subplugin &$subplugin) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $record = array(
            'subpluginname' => $subplugin->subpluginname,
        );
        if ($record = $DB->get_record('tool_cleanupcourses_trigger', $record)) {
            $subplugin = trigger_subplugin::from_record($record);
            self::remove_from_sortindex($subplugin);
            $DB->delete_records('tool_cleanupcourses_trigger', (array) $record);
        }
        $transaction->allow_commit();
    }

    /**
     * Gets the count of currently enabled trigger subplugins.
     * @return int count of enabled trigger subplugins.
     */
    public static function count_enabled_trigger() {
        global $DB;
        return $DB->count_records('tool_cleanupcourses_trigger',
            array(
                'enabled' => 1)
        );
    }

    /**
     * Gets the list of currently enabled trigger subplugins.
     * @return array of enabled trigger subplugins.
     */
    public static function get_enabled_trigger() {
        global $DB;
        return $DB->get_records('tool_cleanupcourses_trigger',
            array(
                'enabled' => 1),
            'sortindex ASC'
        );
    }

    /**
     * Handles an action of the subplugin_settings.
     * @param string $action action to be executed
     * @param int $subplugin id of the subplugin
     */
    public static function handle_action($action, $subplugin) {
        if ($action === ACTION_ENABLE_TRIGGER) {
            self::change_enabled($subplugin, true);
        }
        if ($action === ACTION_DISABLE_TRIGGER) {
            self::change_enabled($subplugin, false);
        }
        if ($action === ACTION_UP_TRIGGER) {
            self::change_sortindex($subplugin, true);
        }
        if ($action === ACTION_DOWN_TRIGGER) {
            self::change_sortindex($subplugin, false);
        }
        if ($action === ACTION_FOLLOWEDBY_TRIGGER) {
            self::change_followedby($subplugin, optional_param('followedby', null, PARAM_INT));
        }
    }

}
