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
 * Generator for the lifecycletrigger_byrole testcase
 * @category   test

 * @copyright  2017 Tobias Reischmann WWU Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package lifecycletrigger_byrole
 */

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\local\entity\workflow;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\trigger_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\settings_type;

/**
 * Generator class for the lifecycletrigger_byrole.
 *
 * @category   test
 * @package    lifecycletrigger_byrole
 * @subpackage byrole
 * @copyright  2017 Tobias Reischmann WWU Nina Herrmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lifecycletrigger_byrole_generator extends testing_data_generator {

    /**
     * Creates a trigger startdatedelay for an artificial workflow without steps.
     * @return trigger_subplugin the created startdatedelay trigger.
     */
    public function create_trigger_with_workflow() {
        // Create Workflow.
        $record = new stdClass();
        $record->id = null;
        $record->title = 'myworkflow';
        $workflow = workflow::from_record($record);
        workflow_manager::insert_or_update($workflow);
        // Create trigger.
        $record = new stdClass();
        $record->subpluginname = 'byrole';
        $record->instancename = 'byrole';
        $record->workflowid = $workflow->id;
        $trigger = trigger_subplugin::from_record($record);
        trigger_manager::insert_or_update($trigger);
        // Set delay setting.
        $settings = new stdClass();
        $settings->roles = '1,3';
        $settings->delay = 2000;
        settings_manager::save_settings($trigger->id, settings_type::TRIGGER, $trigger->subpluginname, $settings);

        return $trigger;
    }

    /**
     * Creates data to test the trigger subplugin lifecycletrigger_byrole.
     */
    public function test_create_preparation() {
        global $DB;
        $generator = advanced_testcase::getDataGenerator();
        $data = [];

        $data['trigger'] = $this->create_trigger_with_workflow();

        // Creates different users.
        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        // Creates a course with one student one teacher.
        $teachercourse = $generator->create_course(['name' => 'teachercourse']);
        $generator->enrol_user($user1->id, $teachercourse->id, 3);
        $generator->enrol_user($user2->id, $teachercourse->id, 5);
        $data['teachercourse'] = $teachercourse;

        // Creates a course with one student one manager.
        $managercourse = $generator->create_course(['name' => 'managercourse']);
        $manager = $generator->create_user();
        $data['manager'] = $manager;
        $generator->enrol_user($user1->id, $managercourse->id, 1);
        $generator->enrol_user($user2->id, $managercourse->id, 5);
        $data['managercourse'] = $managercourse;

        // Create a course without any role.
        $norolecourse = $generator->create_course(['name' => 'norolecourse']);
        $data['norolecourse'] = $norolecourse;

        // Create a course already marked for deletion with one student and old.
        $norolefoundcourse = $generator->create_course(['name' => 'norolefoundcourse']);
        $generator->enrol_user($user3->id, $norolefoundcourse->id, 5);
        $dataobject = new \stdClass();
        $dataobject->courseid = $norolefoundcourse->id;
        $dataobject->triggerid = $data['trigger']->id;
        $dataobject->timecreated = time() - 31536000;
        $DB->insert_record('lifecycletrigger_byrole', $dataobject);
        $data['norolefoundcourse'] = $norolefoundcourse;

        // Create a course already marked for deletion with one student and really old.
        $norolefoundcourse2 = $generator->create_course(['name' => 'norolefoundcourse2']);
        $generator->enrol_user($user3->id, $norolefoundcourse2->id, 5);
        $dataobject = new \stdClass();
        $dataobject->courseid = $norolefoundcourse2->id;
        $dataobject->triggerid = $data['trigger']->id;
        $dataobject->timecreated = time() - 32536001;
        $DB->insert_record('lifecycletrigger_byrole', $dataobject);
        $data['norolefoundcourse2'] = $norolefoundcourse2;

        // Create a course already marked for deletion with one student and one teacher and old.
        $rolefoundagain = $generator->create_course(['name' => 'rolefoundagain']);
        $generator->enrol_user($user3->id, $rolefoundagain->id, 3);
        $generator->enrol_user($user2->id, $rolefoundagain->id, 5);
        $dataobject = new \stdClass();
        $dataobject->courseid = $rolefoundagain->id;
        $dataobject->triggerid = $data['trigger']->id;
        $dataobject->timecreated = time() - 31536000;
        $DB->insert_record('lifecycletrigger_byrole', $dataobject);
        $data['rolefoundagain'] = $rolefoundagain;
        return $data;
    }

}
