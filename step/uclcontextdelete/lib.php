<?php
namespace tool_lifecycle\step;

use stdClass;
use tool_lifecycle\local\response\step_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class uclcontextdelete extends libbase {

    /**
     * Queue course for deletion via Catalyst batch task.
     */
    public function process_course($processid, $instanceid, $course) {
        global $DB, $USER;

        $manager = $DB->get_manager();
        if (!$manager->table_exists('tool_catmaintenance_delcourse')) {
            debugging('Catalyst maintenance plugin not installed — cannot queue course deletion.', DEBUG_DEVELOPER);
            return step_response::rollback();
        }

        // Avoid duplicate queue entries.
        if (!$DB->record_exists('tool_catmaintenance_delcourse', ['courseid' => $course->id])) {
            $record = new stdClass();
            $record->courseid    = $course->id;
            $record->timecreated = time();
            $record->createdby   = $USER->id ?? 0;

            $DB->insert_record('tool_catmaintenance_delcourse', $record);
        }

        return step_response::proceed();
    }

    public function get_subpluginname() {
        return 'uclcontextdelete';
    }
}
