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
 * Table listing all active manually triggered workflows.
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\table;

use tool_cleanupcourses\manager\process_manager;
use tool_cleanupcourses\manager\step_manager;
use tool_cleanupcourses\manager\trigger_manager;
use tool_cleanupcourses\manager\workflow_manager;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/../../lib.php');

class active_manual_workflows_table extends workflow_table {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        global $PAGE, $DB;
        list($sqlwhereactive, $params) = $DB->get_in_or_equal(true);
        list($sqlwheremanual, $paramsmanual) = $DB->get_in_or_equal(true);
        $sqlwhere = 'active ' . $sqlwhereactive . ' AND manual ' . $sqlwheremanual;
        $params[1] = $paramsmanual[0];
        $this->set_sql("id, title, displaytitle, timeactive", '{tool_cleanupcourses_workflow}',
            $sqlwhere, $params);
        $this->define_baseurl($PAGE->url);
        $this->pageable(false);
        $this->init();
    }

    public function init() {
        $this->define_columns(['title', 'timeactive', 'trigger', 'processes', 'tools']);
        $this->define_headers([
            get_string('workflow_title', 'tool_cleanupcourses'),
            get_string('workflow_timeactive', 'tool_cleanupcourses'),
            get_string('trigger', 'tool_cleanupcourses'),
            get_string('workflow_processes', 'tool_cleanupcourses'),
            get_string('workflow_tools', 'tool_cleanupcourses'),
            ]);
        $this->sortable(true, 'title');
        $this->setup();
    }

}