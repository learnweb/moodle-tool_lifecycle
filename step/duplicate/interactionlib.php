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
 * Interface for the interactions of the subplugintype step
 * It has to be implemented by all subplugins that want to use the interaction view.
 *
 * @package tool_lifecycle
 * @subpackage step
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use lifecyclestep_duplicate\form_duplicate;
use tool_lifecycle\entity\process;
use tool_lifecycle\entity\step_subplugin;
use tool_lifecycle\manager\process_data_manager;
use tool_lifecycle\manager\step_manager;
use tool_lifecycle\response\step_interactive_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../interactionlib.php');
require_once(__DIR__ . '/lib.php');


class interactionduplicate extends interactionlibbase {

    const ACTION_DUPLICATE_FORM = 'duplicateform';

    /**
     * Returns the capability a user has to have to make decisions for a specific course.
     * @return string capability string.
     */
    public function get_relevant_capability() {
        return 'lifecyclestep/email:preventdeletion';
    }

    /**
     * Returns an array of interaction tools to be displayed to be displayed on the view.php
     * Every entry is itself an array which consist of three elements:
     *  'action' => an action string, which is later passed to handle_action
     *  'alt' => a string text of the button
     * @param process $process process the action tools are requested for
     * @return array of action tools
     */
    public function get_action_tools($process) {
        $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex);
        $shortname = process_data_manager::get_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSESHORTNAME);
        $fullname = process_data_manager::get_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSEFULLNAME);
        if (!empty($fullname) && !empty($shortname)) {
            return array();
        }
        return array(
            array('action' => self::ACTION_DUPLICATE_FORM,
                'alt' => get_string('duplicate_form', 'lifecyclestep_duplicate'),
            ),
        );
    }

    /**
     * Returns the status message for the given process.
     * @param process $process process the status message is requested for
     * @return string status message
     */
    public function get_status_message($process) {
        $step = step_manager::get_step_instance_by_workflow_index($process->workflowid, $process->stepindex);
        $shortname = process_data_manager::get_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSESHORTNAME);
        $fullname = process_data_manager::get_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSEFULLNAME);
        if (!empty($fullname) && !empty($shortname)) {
            return get_string('status_message_duplication', 'lifecyclestep_duplicate');
        }
        return get_string('status_message_form', 'lifecyclestep_duplicate');
    }

    /**
     * Called when a user triggered an action for a process instance.
     * @param process $process instance of the process the action was triggered upon.
     * @param step_subplugin $step instance of the step the process is currently in.
     * @param string $action action string
     * @return step_interactive_response defines if the step still wants to process this course
     *      - proceed: the step has finished and respective controller class can take over.
     *      - stillprocessing: the step still wants to process the course and is responsible for rendering the site.
     *      - noaction: the action is not defined for the step.
     *      - rollback: the step has finished and respective controller class should rollback the process.
     */
    public function handle_interaction($process, $step, $action = 'default') {
        global $PAGE, $DB, $OUTPUT;
        $form = new form_duplicate($PAGE->url, $process->id, $step->id);
        if ($form->is_cancelled()) {
            return step_interactive_response::rollback();
        }
        if ($data = $form->get_submitted_data()) {
            if ($foundcourses = $DB->get_records('course', array('shortname' => $data->shortname))) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }

                $foundcoursenamestring = implode(',', $foundcoursenames);
                echo $OUTPUT->notification(get_string('shortnametaken', '', $foundcoursenamestring));
                $this->render_form($form);
                return step_interactive_response::still_processing();
            }
            process_data_manager::set_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSESHORTNAME, $data->shortname);
            process_data_manager::set_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSEFULLNAME, $data->fullname);
            return step_interactive_response::no_action();
        }
        if ($action == self::ACTION_DUPLICATE_FORM || $action == 'default') {
            $this->render_form($form);
            return step_interactive_response::still_processing();
        }
        return step_interactive_response::no_action();
    }

    /**
     * Renders the duplication form including respective headers and footers.
     * @param $mform \moodleform to be rendered.
     */
    private function render_form($mform) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('tool_lifecycle');

        echo $renderer->header();
        $mform->display();
        echo $renderer->footer();
    }
}