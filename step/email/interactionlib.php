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
 * @package tool_cleanupcourses
 * @subpackage step
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_cleanupcourses\step;

use tool_cleanupcourses\entity\process;
use tool_cleanupcourses\entity\step_subplugin;
use tool_cleanupcourses\manager\process_data_manager;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../interactionlib.php');
require_once(__DIR__ . '/lib.php');


class interactionemail extends interactionlibbase {

    const ACTION_KEEP = 'keep';

    /**
     * Returns the capability a user has to have to make decisions for a specific course.
     * @return string capability string.
     */
    public function get_relevant_capability() {
        return 'cleanupcoursesstep/email:preventdeletion';
    }

    /**
     * Returns an array of interaction tools to be displayed to be displayed on the view.php
     * Every entry is itself an array which consist of three elements:
     *  'action' => an action string, which is later passed to handle_action
     *  'icon' => an icon string
     *  'alt' => a string description of the link
     * @param process $process process the action tools are requested for
     * @return array of action tools
     */
    public function get_action_tools($process) {
        $keep = process_data_manager::get_process_data($process->id, $process->stepid, EMAIL_PROCDATA_KEY_KEEP);
        if ($keep === '1') {
            return array();
        }
        return array(
            array('action' => self::ACTION_KEEP,
                'icon' => 't/locktime',
                'alt' => get_string('keep_course', 'cleanupcoursesstep_email'),
            ),
        );
    }

    /**
     * Returns the status message for the given process.
     * @param process $process process the status message is requested for
     * @return string status message
     */
    public function get_status_message($process) {
        $keep = process_data_manager::get_process_data($process->id, $process->stepid, EMAIL_PROCDATA_KEY_KEEP);
        if ($keep === '1') {
            return get_string('status_message_decision_keep', 'cleanupcoursesstep_email');
        }
        return get_string('status_message_requiresattention', 'cleanupcoursesstep_email');
    }

    /**
     * Called when a user triggered an action for a process instance.
     * @param process $process instance of the process the action was triggered upon.
     * @param step_subplugin $step instance of the step the process is currently in.
     * @param string $action action string
     */
    public function handle_interaction($process, $step, $action) {
        if ($action == self::ACTION_KEEP) {
            process_data_manager::set_process_data($process->id, $step->id, EMAIL_PROCDATA_KEY_KEEP, '1');
        }
    }
}