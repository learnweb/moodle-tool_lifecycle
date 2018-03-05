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
 * Step definition for cleanup courses.
 *
 * @package    tool_cleanupcourses
 * @category   test
 * @copyright  2018 Tobias Reischmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Step definition for cleanup courses.
 *
 * @package    tool_cleanupcourses
 * @category   test
 * @copyright  2018 Tobias Reischmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_tool_cleanupcourses extends behat_base {

    /**
     * Click on an entry in the tools menu of a table.
     *
     * @When /^I click on the tool "([^"]*)" in the "([^"]*)" row of the "([^"]*)" table$/
     *
     * @param string $nodetext
     * @param string $rowname
     * @param string $tablename
     */
    public function click_on_the_tool_in_the_row_of_the_table($nodetext, $rowname, $tablename) {
        $xpathtarget = "//table/tbody/tr[contains(@id, '$tablename')]//td[contains(text(),'$rowname')]//parent::tr";

        $this->execute('behat_general::i_click_on_in_the', [$this->escape($nodetext), 'link', $xpathtarget, 'xpath_element']);
    }
}
