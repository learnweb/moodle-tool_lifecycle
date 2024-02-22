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

/**
 * Renderer for life cycle
 *
 * @package tool_lifecycle
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_renderer extends plugin_renderer_base {

    /**
     * Write the page footer
     */
    public function footer() {
        echo $this->output->footer();
    }

    /**
     * Write the page header
     *
     * @param string $title optional page title.
     */
    public function header($title = null) {
        echo $this->output->header();
        if ($title) {
            echo $this->output->heading($title);
        }
    }

    /**
     * Renders the workflow upload form including errors, which occured during upload.
     * @param \tool_lifecycle\local\form\form_upload_workflow $form
     * @throws coding_exception
     */
    public function render_workflow_upload_form($form) {
        $this->header(get_string('adminsettings_edit_workflow_definition_heading', 'tool_lifecycle'));
        $form->display();
        $this->footer();
    }

}
