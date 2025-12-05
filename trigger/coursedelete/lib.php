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
 * @package lifecycletrigger_coursedelete
 * @copyright  2025 Gifty Wanzola (ccaewan)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\trigger;

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;
use tool_lifecycle\trigger\instance_setting;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Class which implements a trigger for deleting frozen/archive courses.
 *
 * For local testing we approximate:
 *  - "frozen" as c.visible = 0
 *  - "frozen since" as c.timecreated
 * At UCL you will replace the WHERE clause to use the real "frozen since"
 * timestamp from the context freeze implementation.
 */
class coursedelete extends base_automatic {

    /**
     * For each course returned by the WHERE clause, just trigger.
     *
     * @param \stdClass $course
     * @param int $triggerid
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        // Decision is already in the WHERE clause.
        return trigger_response::trigger();
    }

    /**
     * Instance settings for this trigger.
     *
     * @return instance_setting[]
     */
    public function instance_settings() {
        return [
            // "How long has the course been frozen" before deletion (36 months)
            new instance_setting('frozendelay', PARAM_INT),
        ];
    }

    /**
     * Returns the WHERE clause and params selecting courses to be deleted.
     *
     * Code to be changes when moving back to UCL container:
     *  - course is "frozen" if c.visible = 0 (hidden / read-only)
     *  - use c.timecreated as the frozen timestamp for testing
     *
     * On 45-Extend, replace c.timecreated with the real "archived since" field or
     * join against the context-freeze table.
     *
     * @param int $triggerid
     * @return string[]
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;

        // Load instance settings.
        $settings = settings_manager::get_settings($triggerid, settings_type::TRIGGER);

        // Default: 3 years (36 months).
        $frozendelay = isset($settings['frozendelay']) ? $settings['frozendelay'] : (3 * 365 * DAYSECS);

        $now = time();
        $frozenthreshold = $now - $frozendelay;

        // WHERE clause:
        //  - c.visible = 0 → archived/frozen (approximation)
        //  - c.timecreated < :frozenthreshold → has been "frozen" long enough
        //
        // NOTE: 45-Extend: replace c.timecreated with the "archived since"
        //       timestamp or a join to the freeze tracking table.
        $where = 'c.visible = 0
                  AND c.timecreated < :frozenthreshold';

        $params = [
            'frozenthreshold' => $frozenthreshold,
        ];

        return [$where, $params];
    }

    /**
     * Add instance settings elements to the add-instance form.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'frozendelay';
        $mform->addElement(
            'duration',
            $elementname,
            get_string($elementname, 'lifecycletrigger_coursedelete')
        );
        $mform->addHelpButton($elementname, $elementname, 'lifecycletrigger_coursedelete');

        // Default to 3 years.
        $mform->setDefault($elementname, 3 * 365 * DAYSECS);
    }

    /**
     * After data is loaded, set defaults from existing settings if present.
     *
     * @param \MoodleQuickForm $mform
     * @param array $settings
     * @return void
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        if (is_array($settings) && array_key_exists('frozendelay', $settings)) {
            $mform->setDefault('frozendelay', $settings['frozendelay']);
        }
    }

    /**
     * Return subplugin name (folder name under trigger/).
     *
     * @return string
     */
    public function get_subpluginname() {
        return 'coursedelete';
    }
}