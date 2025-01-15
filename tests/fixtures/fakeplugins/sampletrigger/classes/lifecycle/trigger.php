<?php

namespace tool_sampletrigger\lifecycle;

global $CFG;
require_once($CFG->dirroot . '/admin/tool/lifecycle/trigger/lib.php');

use tool_lifecycle\trigger\base_automatic;

defined('MOODLE_INTERNAL') || die();

class trigger extends base_automatic {

    public function get_subpluginname()
    {
        return 'sample trigger';
    }

    public function get_plugin_description() {
        return "Sample trigger";
    }

    public function check_course($course, $triggerid)
    {
        return null;
    }

}
