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
 * The process_triggered event.
 *
 * @package    tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\event;

use moodle_url;
use tool_lifecycle\local\entity\process;

/**
 * The process_triggered event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int processid: the id of the process.
 *      - int workflowid: the id of the workflow.
 * }
 *
 * @package    tool_lifecycle
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_triggered extends \core\event\base {

    /**
     * Creates an event with a process.
     *
     * @param process $process
     * @return process_triggered
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function event_from_process($process) {
        $data = [
                'context' => \context_system::instance(),
                'other' => [
                        'processid' => $process->id,
                        'workflowid' => $process->workflowid,
                        'courseid' => $process->courseid,
                ],
        ];
        return self::create($data);
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $processid = $this->other['processid'];
        $workflowid = $this->other['workflowid'];
        $courseid = $this->other['courseid'];

        return "The workflow with id '$workflowid' triggered for course '$courseid' and created process with id '$processid'";
    }

    /**
     * Return localised event name.
     *
     * @return string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('process_triggered_event', 'tool_lifecycle');
    }

    /**
     * Returns relevant URL.
     *
     * @return moodle_url
     * @throws \moodle_exception
     */
    public function get_url() {
        return new moodle_url('/admin/tool/lifecycle/view.php');
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['processid'])) {
            throw new \coding_exception('The \'processid\' value must be set');
        }

        if (!isset($this->other['workflowid'])) {
            throw new \coding_exception('The \'workflowid\' value must be set');
        }

        if (!isset($this->other['courseid'])) {
            throw new \coding_exception('The \'courseid\' value must be set');
        }
    }

    /**
     * Implementation of get_other_mapping.
     */
    public static function get_other_mapping() {
        // No backup and restore.
        return false;
    }
}
