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
  * Trigger subplugin to delete frozen courses based on long inactivity + age.
  *
  * @package lifecycletrigger_coursedelete
  * @copyright  2025
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 namespace tool_lifecycle\trigger;

 use tool_lifecycle\local\manager\settings_manager;
 use tool_lifecycle\local\response\trigger_response;
 use tool_lifecycle\settings_type;
 use tool_lifecycle\trigger\instance_setting;

 defined('MOODLE_INTERNAL') || die();
 require_once(__DIR__ . '/../lib.php');

 /**
  * Class which implements a trigger for deleting frozen courses.
  *
  * Logic (workaround while no "frozen since" timestamp exists):
  *  - Course context is locked (frozen): {context}.locked = 1 for course contextlevel 50
  *  - Last access (enrolled users) older than inactivitydelay (default 48 months)
  *  - Course creation older than creationdelay (default 60 months)
  */
 class coursedelete extends base_automatic {

     public function check_course($course, $triggerid) {
         return trigger_response::trigger();
     }

     /**
      * Instance settings for this trigger.
      *
      * @return instance_setting[]
      */
     public function instance_settings() {
         return [
             // Last access must be older than this (default 48 months).
             new instance_setting('inactivitydelay', PARAM_INT),

             // Course creation must be older than this (default 60 months).
             new instance_setting('creationdelay', PARAM_INT),
         ];
     }

     /**
      * Returns the WHERE clause and params selecting courses to be deleted.
      *
      * @param int $triggerid
      * @return array [string $where, array $params]
      * @throws \coding_exception
      * @throws \dml_exception
      */
     public function get_course_recordset_where($triggerid) {
         // Load instance settings.
         $settings = settings_manager::get_settings($triggerid, settings_type::TRIGGER);

         // Defaults (approx months as 365-day years for now):
         // - 48 months ≈ 4 years
         // - 60 months ≈ 5 years
         $inactivitydelay = isset($settings['inactivitydelay']) ? (int)$settings['inactivitydelay'] : (4 * 365 * DAYSECS);
         $creationdelay   = isset($settings['creationdelay'])   ? (int)$settings['creationdelay']   : (5 * 365 * DAYSECS);

         $now = time();
         $lastaccessthreshold = $now - $inactivitydelay;
         $creationthreshold   = $now - $creationdelay;

         // Frozen courses: course context locked.
         // Inactive courses: max(lastaccess.timeaccess) for enrolled users is older than threshold.
         // Old courses: timecreated older than threshold.
         $where = "c.timecreated < :creationthreshold
                   AND EXISTS (
                         SELECT 1
                           FROM {context} ctx
                          WHERE ctx.contextlevel = 50
                            AND ctx.instanceid = c.id
                            AND ctx.locked = 1
                   )
                   AND c.id IN (
                         SELECT la.courseid
                           FROM {user_enrolments} ue
                           JOIN {enrol} e ON ue.enrolid = e.id
                           JOIN {user_lastaccess} la ON ue.userid = la.userid
                          WHERE e.courseid = la.courseid
                          GROUP BY la.courseid
                          HAVING MAX(la.timeaccess) < :lastaccessthreshold
                   )";

         $params = [
             'creationthreshold'   => $creationthreshold,
             'lastaccessthreshold' => $lastaccessthreshold,
         ];

         return [$where, $params];
     }

     /**
      * Add instance settings elements to the add-instance form.
      *
      * @param \MoodleQuickForm $mform
      * @return void
      * @throws \coding_exception
      */
     public function extend_add_instance_form_definition($mform) {

         $elementname = 'inactivitydelay';
         $mform->addElement('duration', $elementname, get_string($elementname, 'lifecycletrigger_coursedelete'));
         $mform->addHelpButton($elementname, $elementname, 'lifecycletrigger_coursedelete');
         $mform->setDefault($elementname, 4 * 365 * DAYSECS); // ~48 months.

         $elementname = 'creationdelay';
         $mform->addElement('duration', $elementname, get_string($elementname, 'lifecycletrigger_coursedelete'));
         $mform->addHelpButton($elementname, $elementname, 'lifecycletrigger_coursedelete');
         $mform->setDefault($elementname, 5 * 365 * DAYSECS); // ~60 months.
     }

     /**
      * After data is loaded, set defaults from existing settings if present.
      *
      * @param \MoodleQuickForm $mform
      * @param array $settings
      * @return void
      */
     public function extend_add_instance_form_definition_after_data($mform, $settings) {
         if (!is_array($settings)) {
             return;
         }
         if (array_key_exists('inactivitydelay', $settings)) {
             $mform->setDefault('inactivitydelay', $settings['inactivitydelay']);
         }
         if (array_key_exists('creationdelay', $settings)) {
             $mform->setDefault('creationdelay', $settings['creationdelay']);
         }
     }

     public function get_subpluginname() {
         return 'coursedelete';
     }
 }
