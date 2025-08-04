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
 * Displays form for uploading a backed up workflow.
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;
use tool_lifecycle\local\backup\restore_lifecycle_workflow;
use tool_lifecycle\local\form\form_upload_workflow;
use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::UPLOAD_WORKFLOW));

$PAGE->set_context($syscontext);
$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('upload_workflow', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$renderer->tabs($tabrow, '');

$form = new form_upload_workflow();
if ($form->is_cancelled()) {
    // Cancelled, redirect back to workflow drafts.
    redirect(new moodle_url(urls::WORKFLOW_DRAFTS));
} else if ($data = $form->get_data()) {
    $xmldata = $form->get_file_content('backupfile');
    $restore = new restore_lifecycle_workflow($xmldata);
    $force = $data->force ?? false;
    $errors = $restore->execute($force);
    if (count($errors) != 0) {
        notification::add(get_string('workflow_was_not_imported', 'tool_lifecycle'), notification::ERROR);
        foreach (array_unique($errors) as $error) {
            notification::add($error, notification::ERROR);
        }
        $form = new form_upload_workflow(null, ['showforce' => true]);
    } else {
        // Redirect to workflow page.
        redirect(new moodle_url(urls::WORKFLOW_DETAILS, ['wf' => $restore->get_workflow()->id]));
    }
}

$form->display();
echo $renderer->footer();
