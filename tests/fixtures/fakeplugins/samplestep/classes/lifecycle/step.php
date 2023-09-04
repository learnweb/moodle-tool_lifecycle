<?php

namespace tool_samplestep\lifecycle;

global $CFG;
require_once($CFG->dirroot . '/admin/tool/lifecycle/step/lib.php');

use tool_lifecycle\step\libbase;

defined('MOODLE_INTERNAL') || die();

class step extends libbase {
    public function get_subpluginname()
    {
        return 'sample step';
    }

    public function get_plugin_description() {
        return "Sample step plugin";
    }

    public function process_course($processid, $instanceid, $course)
    {
        return null;
    }

}
