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
 * Constants for urls used within this plugin.
 * @package    tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

/**
 * Constants for urls used within this plugin.
 * @package    tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class urls {

    /** @var string Lists active workflows. */
    const ACTIVE_WORKFLOWS = '/admin/tool/lifecycle/activeworkflows.php?id=activeworkflows';
    /** @var string Lists workflow drafts. */
    const WORKFLOW_DRAFTS = '/admin/tool/lifecycle/workflowdrafts.php?id=workflowdrafts';
    /** @var string Lists deactivated workflows. */
    const DEACTIVATED_WORKFLOWS = '/admin/tool/lifecycle/deactivatedworkflows.php?id=deactivatedworkflows';
    /** @var string Edits general settings of workflow. */
    const EDIT_WORKFLOW = '/admin/tool/lifecycle/editworkflow.php?id=workflowdrafts';
    /** @var string Lets the user upload a workflow definition. */
    const UPLOAD_WORKFLOW = '/admin/tool/lifecycle/uploadworkflow.php?id=workflowdrafts';
    /** @var string Displays a nice visual representation of the workflow. */
    const WORKFLOW_DETAILS = '/admin/tool/lifecycle/workflowoverview.php';
    /** @var string Edits settings of triggers and steps */
    const EDIT_ELEMENT = '/admin/tool/lifecycle/editelement.php';
    /** @var string Edits settings of triggers and steps */
    const CREATE_FROM_EXISTING = '/admin/tool/lifecycle/createworkflowfromexisting.php?id=workflowdrafts';
    /** @var string Lists active processes and finds courses */
    const ACTIVE_PROCESSES = '/admin/tool/lifecycle/activeprocesses.php';
    /** @var string Lists and search for delayed courses, deleting of delays */
    const DELAYED_COURSES = '/admin/tool/lifecycle/delayedcourses.php?id=delayedcourses';
    /** @var string Lists course backups */
    const COURSE_BACKUPS = '/admin/tool/lifecycle/coursebackups.php?id=coursebackups';
    /** @var string Lists and process errors */
    const PROCESS_ERRORS = '/admin/tool/lifecycle/errors.php?id=errors';

}
