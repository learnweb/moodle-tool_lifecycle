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
 * Checks for permission and handles breadcrumbs.
 * @package    tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

use moodle_url;
use navigation_node;
use tool_lifecycle\local\entity\workflow;

/**
 * Checks for permission and handles breadcrumbs.
 * @package    tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class permission_and_navigation {

    /**
     * Check permission and setup breadcrumbs for the given workflow.
     * @param workflow $workflow
     * @param bool $includeworkflowname whether to add the title of the workflow to the navbar. Default true.
     */
    public static function setup_workflow(workflow $workflow, bool $includeworkflowname = true) {
        global $PAGE;
        if (!$workflow) {
            throw new \coding_exception('Workflow does not exists!');
        }
        if ($workflow->timedeactive) {
            self::setup_deactived();;
        } else if ($workflow->timeactive) {
            self::setup_active();
        } else {
            self::setup_draft();
        }

        if ($includeworkflowname) {
            $PAGE->navbar->add($workflow->title, new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflow->id]));
        }
    }

    /**
     * Check permission and setup breadcrumbs for workflow drafts.
     * @return void
     */
    public static function setup_draft() {
        navigation_node::override_active_url(new moodle_url(urls::WORKFLOW_DRAFTS));
        admin_externalpage_setup('tool_lifecycle_workflow_drafts');
    }

    /**
     * Check permission and setup breadcrumbs for active workflows.
     * @return void
     */
    public static function setup_active() {
        navigation_node::override_active_url(new moodle_url(urls::ACTIVE_WORKFLOWS));
        admin_externalpage_setup('tool_lifecycle_active_workflows');
    }

    /**
     * Check permission and setup breadcrumbs for deactivated workflows.
     * @return void
     */
    public static function setup_deactived() {
        global $PAGE;

        navigation_node::override_active_url(new moodle_url('/admin/category.php', ['category' => 'lifecycle_category']));
        admin_externalpage_setup('tool_lifecycle_active_workflows');
        $PAGE->navbar->add(get_string('deactivated_workflows_list_header', 'tool_lifecycle'),
            new moodle_url(urls::DEACTIVATED_WORKFLOWS));
    }

}
