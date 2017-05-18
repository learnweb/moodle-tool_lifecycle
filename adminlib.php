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

namespace tool_cleanupcourses;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');

/**
 * External Page for showing active cleanup processes
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class admin_page_active_processes extends \admin_externalpage {

    /**
     * The constructor - calls parent constructor
     *
     */
    public function __construct() {
        $url = new \moodle_url('/admin/tool/cleanupcourses/activeprocesses.php');
        parent::__construct('activeprocesses',
            get_string('active_processes_list_header', 'tool_cleanupcourses'),
            $url);
    }
}

/**
 * External Page for defining settings for subplugins
 *
 * @package tool_cleanupcourses
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class admin_page_sublugins extends \admin_externalpage {

    /**
     * The constructor - calls parent constructor
     *
     */
    public function __construct() {
        $url = new \moodle_url('/admin/tool/cleanupcourses/subpluginssettings.php');
        parent::__construct('subpluginssettings',
            get_string('subpluginssettings_heading', 'tool_cleanupcourses'),
            $url);
    }
}

/**
 * Class that handles the display and configuration of the subplugin settings.
 *
 * @package   tool_cleanupcourses
 * @copyright 2015 Tobias Reischmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subplugin_settings {

    /** @var object the url of the subplugin settings page */
    private $pageurl;

    /**
     * Constructor for this subplugin settings
     */
    public function __construct() {
        $this->pageurl = new \moodle_url('/admin/tool/cleanupcourses/subpluginssettings.php');
    }

    /**
     * Write the HTML for the submission plugins table.
     */
    private function view_plugins_table() {

        // Set up the table.
        $this->view_header();

        $table = new trigger_table('tool_cleanupcourses_triggers');

        $table->out(5000, false);

        $table = new step_table('tool_cleanupcourses_steps');
        $table->out(5000, false);

        $this->view_footer();
    }

    /**
     * Write the page header
     */
    private function view_header() {
        global $OUTPUT;
        admin_externalpage_setup('subpluginssettings');
        // Print the page heading.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('subpluginssettings_heading', 'tool_cleanupcourses'));
    }

    /**
     * Write the page footer
     */
    private function view_footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the subplugin settings
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = \context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * This is the entry point for this controller class.
     */
    public function execute($action, $subplugin) {
        $this->check_permissions();
        $triggermanager = new trigger_manager();
        $triggermanager->handle_action($action, $subplugin);
        $stepmanager = new step_manager();
        $stepmanager->handle_action($action, $subplugin);
        $this->view_plugins_table();
    }

}