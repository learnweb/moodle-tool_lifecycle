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
 * Renderer for life cycle
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class tool_lifecycle_renderer extends plugin_renderer_base {

    /**
     * Write the page footer
     *
     * @return string
     */
    public function footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    /**
     * Write the page header
     *
     * @return string
     */
    public function header($title) {
        global $OUTPUT, $PAGE;
        echo $OUTPUT->header();
        if ($title) {
            echo $OUTPUT->heading($title);
        } else {
            echo $OUTPUT->heading($PAGE->heading);
        }
    }

    /**
     * Renders the workflow upload form including errors, which occured during upload.
     * @param \tool_lifecycle\form\form_upload_workflow $form
     * @param array $errors
     */
    public function render_workflow_upload_form($form, $errors = array()) {
        global $OUTPUT;
        $this->header(get_string('adminsettings_edit_workflow_definition_heading', 'tool_lifecycle'));
        foreach ($errors as $error) {
            echo $OUTPUT->notification($error);
        }
        echo $form->render();
        $this->footer();
    }

}