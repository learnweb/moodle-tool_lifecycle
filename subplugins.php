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
 * Displays the installed subplugins (steps and trigger).
 *
 * @package tool_lifecycle
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2022 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\tabs;
use tool_lifecycle\urls;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

$syscontext = context_system::instance();
$PAGE->set_url(new \moodle_url(urls::SUBPLUGINS));
$PAGE->set_context($syscontext);

$PAGE->set_pagetype('admin-setting-' . 'tool_lifecycle');
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('tool_lifecycle');

$heading = get_string('pluginname', 'tool_lifecycle')." / ".get_string('subplugins', 'tool_lifecycle');
echo $renderer->header($heading);
$tabrow = tabs::get_tabrow();
$id = optional_param('id', 'subplugins', PARAM_TEXT);
$renderer->tabs($tabrow, $id);

echo html_writer::link('https://github.com/learnweb/moodle-tool_lifecycle/wiki/List-of-Installed-Subplugins',
    get_string('documentationlink', 'tool_lifecycle'), ['target' => '_blank']);

$triggers = core_component::get_plugin_list('lifecycletrigger');
if ($triggers) {
    echo html_writer::div(get_string('triggers_installed', 'tool_lifecycle'), 'h2 mt-2');
    foreach ($triggers as $trigger => $path) {
        echo html_writer::div(get_string('pluginname', 'lifecycletrigger_' . $trigger),
            "font-weight-bold");
        try {
            $plugindescription = get_string('plugindescription', 'lifecycletrigger_' . $trigger);
        } catch (Exception $e) {
            $plugindescription = "";
        }
        if ($plugindescription) {
            echo html_writer::start_div().$plugindescription;
            if ($trigger == 'sitecourse' || $trigger == 'delayedcourses') {
                echo html_writer::span(' Depracated. Will be removed with version 5.0.', 'text-danger');
            }
            echo html_writer::end_div();
        }
    }
} else {
    echo html_writer::div(get_string('adminsettings_notriggers', 'tool_lifecycle'));
}

$steps = core_component::get_plugin_list('lifecyclestep');
if ($steps) {
    echo html_writer::div(get_string('steps_installed', 'tool_lifecycle'), 'h2 mt-2');
    foreach ($steps as $step => $path) {
        echo html_writer::div(get_string('pluginname', 'lifecyclestep_' . $step),
            "font-weight-bold");
        try {
            $plugindescription = get_string('plugindescription', 'lifecyclestep_' . $step);
        } catch (Exception $e) {
            $plugindescription = "";
        }
        if ($plugindescription) {
            echo html_writer::div($plugindescription);
        }
    }
} else {
    echo html_writer::div(get_string('adminsettings_nosteps', 'tool_lifecycle'));
}

echo $renderer->footer();
