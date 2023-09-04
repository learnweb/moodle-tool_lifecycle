<?php

namespace tool_samplestep\lifecycle;

global $CFG;
require_once($CFG->dirroot . '/admin/tool/lifecycle/step/interactionlib.php');

use tool_lifecycle\step\interactionlibbase;

defined('MOODLE_INTERNAL') || die();

class interaction extends interactionlibbase {

    public function get_relevant_capability()
    {
    }

    public function get_action_tools($process)
    {
    }

    public function get_status_message($process)
    {
    }

    public function get_action_string($action, $user)
    {
    }

    public function handle_interaction($process, $step, $action = 'default')
    {
    }
}
