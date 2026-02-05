<?php
namespace tool_lifecycle\step;

use tool_lifecycle\local\response\step_response;
use core\task\manager;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class uclcontextdelete extends libbase {

    /**
     * Ensure task only queued once per workflow execution.
     *
     * @var bool
     */
    protected static $taskqueued = false;

    public function process_course($processid, $instanceid, $course) {

        // only queue task once
        if (!self::$taskqueued) {

            // test error message
            if (!class_exists('\tool_catmaintenance\task\batch_course_deletion')) {
                debugging(
                    'Catalyst batch_course_deletion task not available.',
                    DEBUG_DEVELOPER
                );
                return step_response::rollback();
            }

            // initialise task
            $task = new \tool_catmaintenance\task\batch_course_deletion();

            manager::queue_adhoc_task($task);

            // mark as queued
            self::$taskqueued = true;

            debugging(
                'Queued Catalyst batch_course_deletion task.',
                DEBUG_DEVELOPER
            );
        }

        return step_response::proceed();
    }

    public function get_subpluginname() {
        return 'uclcontextdelete';
    }
}
