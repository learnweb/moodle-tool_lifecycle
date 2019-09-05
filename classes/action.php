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

namespace tool_lifecycle;

defined('MOODLE_INTERNAL') || die();

/**
 * Delivers all available action names throughout the plugin.
 * @package    tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action {

    const UP_TRIGGER = 'enable';
    const DOWN_TRIGGER = 'down_trigger';
    const UP_STEP = 'up_step';
    const DOWN_STEP = 'down_step';
    const UP_WORKFLOW = 'up_workflow';
    const DOWN_WORKFLOW = 'down_workflow';
    const STEP_INSTANCE_FORM = 'step_instance_form';
    const TRIGGER_INSTANCE_FORM = 'trigger_instance_form';
    const TRIGGER_INSTANCE_DELETE = 'trigger_instance_delete';
    const STEP_INSTANCE_DELETE = 'step_instance_delete';
    const WORKFLOW_INSTANCE_FROM = 'workflow_instance_form';
    const WORKFLOW_UPLOAD_FROM = 'workflow_upload_form';
    const WORKFLOW_BACKUP = 'workflow_instance_backup';
    const WORKFLOW_DELETE = 'workflow_instance_delete';
    const WORKFLOW_DUPLICATE = 'workflow_instance_duplicate';
    const WORKFLOW_ACTIVATE = 'workflow_instance_activate';
    const WORKFLOW_DISABLE = 'workflow_instance_disable';
    const WORKFLOW_ABORTDISABLE = 'workflow_instance_abortdisable';
    const WORKFLOW_ABORT = 'workflow_instance_abort';

}