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
 * Displays form for creating or editing a new step or trigger.
 *
 * @package tool_lifecycle
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\form\form_step_instance;
use tool_lifecycle\local\form\form_trigger_instance;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_login();

global $OUTPUT, $PAGE, $DB;

$type = required_param('type', PARAM_ALPHA);
$elementid = optional_param('elementid', null, PARAM_INT);

if ($type === settings_type::STEP) {
    $isstep = true;
} else if ($type === settings_type::TRIGGER) {
    $isstep = false;
} else {
    throw new coding_exception('type has to be either "step" or "trigger"!');
}

if ($elementid) {
    if ($isstep) {
        $element = \tool_lifecycle\local\manager\step_manager::get_step_instance($elementid);
    } else {
        $element = \tool_lifecycle\local\manager\trigger_manager::get_instance($elementid);
    }
    if (!$element) {
        throw new coding_exception('Element with that ID and type does not exist!');
    }
    $workflowid = $element->workflowid;
    $subplugin = $element->subpluginname;
} else {
    $workflowid = required_param('wf', PARAM_INT);
    $subplugin = required_param('subplugin', PARAM_ALPHANUMEXT);
    $element = null;
}

$workflow = workflow_manager::get_workflow($workflowid);
\tool_lifecycle\permission_and_navigation::setup_workflow($workflow);

$params = [
    'type' => $type,
];
if ($elementid) {
    $params['elementid'] = $element->id;
} else {
    $params['subplugin'] = $subplugin;
    $params['wf'] = $workflow->id;
}

$PAGE->set_url(new moodle_url(urls::EDIT_ELEMENT, $params));

if ($element) {
    $settings = settings_manager::get_settings($element->id, $type);
} else {
    $settings = null;
}

if ($isstep) {
    $form = new form_step_instance($PAGE->url, $workflow->id, $element, $subplugin, $settings);
} else {
    $form = new form_trigger_instance($PAGE->url, $workflow->id, $element, $subplugin, $settings);
}

$titlestrid = ($element ? 'edit' : 'create') . '_' . $type;
$title = get_string($titlestrid, 'tool_lifecycle');

// Return to drafts, or to deactivated workflows if workflow was deactivated.
$returnurl = new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $workflow->id]);

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title, $PAGE->url);

if ($form->is_cancelled()) {
    // Cancelled, redirect back to workflow drafts.
    redirect($returnurl);
}
if ($data = $form->get_data()) {
    if ($isstep) {
        if (!empty($data->id)) {
            $element = step_manager::get_step_instance($data->id);
            if (isset($data->instancename)) {
                $element->instancename = $data->instancename;
            }
        } else {
            $element = step_subplugin::from_record($data);
        }
        step_manager::insert_or_update($element);
    } else {
        if (!empty($data->id)) {
            $element = trigger_manager::get_instance($data->id);
            if (isset($data->instancename)) {
                $element->instancename = $data->instancename;
            }
        } else {
            $triggers = trigger_manager::get_triggers_for_workflow($workflow->id);
            foreach ($triggers as $trigger) {
                if ($trigger->subpluginname == $data->subpluginname) {
                    throw new coding_exception('Only one instance of each trigger type allowed!');
                }
            }
            $element = trigger_subplugin::from_record($data);
        }
        trigger_manager::insert_or_update($element);
    }
    // Save local subplugin settings.
    settings_manager::save_settings($element->id, $type, $form->subpluginname, $data, true);

    // Workflow updated, redirect back to workflow drafts.
    redirect($returnurl);
}

if (!workflow_manager::is_editable($workflow->id)) {
    \core\notification::add(
        get_string('active_workflow_not_changeable', 'tool_lifecycle'),
        \core\notification::WARNING);
}

$renderer = $PAGE->get_renderer('tool_lifecycle');

echo $renderer->header();

$form->display();

echo $renderer->footer();
