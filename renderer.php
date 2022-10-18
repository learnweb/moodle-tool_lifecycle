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
     * @param array $errors
     * @throws coding_exception
     */
    public function render_workflow_upload_form($form, $errors = array()) {
        $this->header(get_string('adminsettings_edit_workflow_definition_heading', 'tool_lifecycle'));
        foreach ($errors as $error) {
            \core\notification::add($error, \core\notification::ERROR);
        }
        echo $form->render();
        $this->footer();
    }
    public function render_extra_navigation () {
        $data2 = new stdClass();
        $children = array();
        $urls = array('/admin/settings.php?section=tool_lifecycle', '/admin/tool/lifecycle/workflowdrafts.php',
            '/admin/tool/lifecycle/activeworkflows.php', '/admin/tool/lifecycle/delayedcourses.php',
            '/admin/tool/lifecycle/step/adminapprove/index.php', '/admin/tool/lifecycle/errors.php',
            '/admin/tool/lifecycle/coursebackups.php');
        $names = array(get_string('edit') . ' ' . get_string('general_settings_header', 'tool_lifecycle'),
            get_string('edit') . ' ' . get_string('workflow_drafts_header', 'tool_lifecycle'),
            get_string('edit') . ' ' . get_string('active_workflows_header', 'tool_lifecycle'),
            get_string('view') . ' ' . get_string('delayed_courses_header', 'tool_lifecycle'),
            get_string('manage-adminapprove', 'lifecyclestep_adminapprove'),
            get_string('process_errors_header', 'tool_lifecycle'),
            get_string('course_backups_list_header', 'tool_lifecycle'));

        for ($i = 0; $i < count($names); $i++) {
            $navitem = new stdClass();
            $navitem->active = true;
            $navitem->showchildreninsubmenu = false;
            $navitem->url = new moodle_url($urls[$i]);
            $navitem->text = $names[$i];
            array_push($children, $navitem);
        }
        $data2->children = $children;
        $moremenu = new \core\navigation\output\more_menu($data2, 'nav', true);
        $secondarynavigation = $moremenu->export_for_template($this->output);
        return $this->output->render_from_template('tool_lifecycle/navigation_helper', $secondarynavigation);
    }

}
