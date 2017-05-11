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
 * tool_cleanupcourses generator tests
 *
 * @package    tool_cleanupcourses
 * @category   test
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class tool_cleanupcourses_generator extends testing_module_generator {

    public static function setup_test_plugins() {
        global $DB;
        $DB->delete_records('tool_cleanupcourses_plugin');
        for ($i = 1; $i <= 3; $i++) {
            $record = array(
                    'id' => $i,
                    'name' => 'subplugin'.$i,
                    'type' => 'cleanupcoursestrigger',
                    'enabled' => 1,
                    'sortindex' => $i,
            );
            $DB->insert_record_raw('tool_cleanupcourses_plugin', $record, true, true, true);
        }
    }
}
