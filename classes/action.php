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
 * Delivers all available action names throughout the plugin.
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle;

/**
 * Delivers all available action names throughout the plugin.
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action {

    /** @var string Moving a trigger one position up. */
    const UP_TRIGGER = 'up_trigger';
    /** @var string Moving a trigger one position down. */
    const DOWN_TRIGGER = 'down_trigger';
    /** @var string Moving a step one position up. */
    const UP_STEP = 'up_step';
    /** @var string Moving a step one position down. */
    const DOWN_STEP = 'down_step';
    /** @var string Moving a workflow one position up. */
    const UP_WORKFLOW = 'up_workflow';
    /** @var string Moving a workflow one position down. */
    const DOWN_WORKFLOW = 'down_workflow';
    /** @var string View the step instance form. */
    const STEP_INSTANCE_FORM = 'step_instance_form';
    /** @var string View the trigger instance form. */
    const TRIGGER_INSTANCE_FORM = 'trigger_instance_form';
    /** @var string Delete a trigger instance. */
    const TRIGGER_INSTANCE_DELETE = 'trigger_instance_delete';
    /** @var string Delete a step instance. */
    const STEP_INSTANCE_DELETE = 'step_instance_delete';
    /** @var string View the workflow instance form. */
    const WORKFLOW_INSTANCE_FROM = 'workflow_instance_form';
    /** @var string Upload a workflow definition file. */
    const WORKFLOW_UPLOAD_FROM = 'workflow_upload_form';
    /** @var string Create a backup for a workflow. */
    const WORKFLOW_BACKUP = 'workflow_instance_backup';
    /** @var string Delete a workflow. */
    const WORKFLOW_DELETE = 'workflow_instance_delete';
    /** @var string Duplicate a workflow. */
    const WORKFLOW_DUPLICATE = 'workflow_instance_duplicate';
    /** @var string Activate a workflow. */
    const WORKFLOW_ACTIVATE = 'workflow_instance_activate';
    /** @var string Disable e a workflow. */
    const WORKFLOW_DISABLE = 'workflow_instance_disable';
    /** @var string Abort and disable a workflow. */
    const WORKFLOW_ABORTDISABLE = 'workflow_instance_abortdisable';
    /** @var string Abort a workflow. */
    const WORKFLOW_ABORT = 'workflow_instance_abort';

}
