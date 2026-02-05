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

        // Only queue once per workflow run
        if (!self::$taskqueued) {

            if (class_exists('\tool_catmaintenance\task\batch_course_deletion')) {

                $task = new \tool_catmaintenance\task\batch_course_deletion();
                manager::queue_adhoc_task($task);

                self::$taskqueued = true;

                debugging(
                    'Lifecycle queued Catalyst batch_course_deletion task.',
                    DEBUG_DEVELOPER
                );

            } else {

                debugging(
                    'Catalyst batch_course_deletion task not available — skipping queue.',
                    DEBUG_DEVELOPER
                );
            }
        }

        return step_response::proceed();
    }

    public function get_subpluginname() {
        return 'uclcontextdelete';
    }
}
