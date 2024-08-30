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
 * Manager for data of Life Cycle Processes.
 *
 * Data means every additional data, which is produced, stored and queried by steps during the process.
 * This class stores and queries the process data using a key/value-store.
 * Only strings can be stored. Every other data has to be parsed manually!
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\local\manager;

use tool_lifecycle\local\entity\process;
use tool_lifecycle\local\entity\trigger_subplugin;

/**
 * Manager for data of Life Cycle Processes.
 *
 * Data means every additional data, which is produced, stored and queried by steps during the process.
 * This class stores and queries the process data using a key/value-store.
 * Only strings can be stored. Every other data has to be parsed manually!
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_data_manager {

    /**
     * Returns the value stored for the process and a step under the respective key.
     * @param int $processid id of the process.
     * @param int $stepid id of the step.
     * @param string $key key the value is stored at.
     * @return string | null value for the given parameters.
     * @throws \dml_exception
     */
    public static function get_process_data($processid, $stepid, $key) {
        global $DB;
        $params = [
            'processid' => $processid,
            'keyname' => $key,
        ];
        if (step_manager::is_process_data_instance_dependent($stepid)) {
            $params['stepid'] = $stepid;
        } else {
            $params['subpluginname'] = step_manager::get_step_instance($stepid)->subpluginname;
        }
        if ($value = $DB->get_record('tool_lifecycle_procdata', $params)) {
            return $value->value;
        }
        return null;
    }

    /**
     * Stores the value for the process and a step under the respective key.
     * @param int $processid id of the process.
     * @param int $stepid id of the step.
     * @param string $key key the value is stored at.
     * @param string $value value for the given parameters.
     * @throws \dml_exception
     */
    public static function set_process_data($processid, $stepid, $key, $value) {
        global $DB;
        $entry = [
            'processid' => $processid,
            'keyname' => $key,
        ];
        if (step_manager::is_process_data_instance_dependent($stepid)) {
            $entry['stepid'] = $stepid;
        } else {
            $entry['subpluginname'] = step_manager::get_step_instance($stepid)->subpluginname;
        }
        if ($oldentry = $DB->get_record('tool_lifecycle_procdata', $entry)) {
            $oldentry->value = $value;
            $DB->update_record('tool_lifecycle_procdata', $oldentry);
        } else {
            $entry['value'] = $value;
            $DB->insert_record('tool_lifecycle_procdata', (object) $entry);
        }
    }

}
