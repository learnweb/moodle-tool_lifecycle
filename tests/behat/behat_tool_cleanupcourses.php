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

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;

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
     * @param $tool string identifier of the tool
     * @param $rowname string identifier of the row
     * @param $tablename string identifier of the table
     * @throws Exception
     */
    public function click_on_the_tool_in_the_row_of_the_table($tool, $rowname, $tablename) {
        $xpathelement = $this->i_should_see_the_tool_in_the_row_of_the_table($tool, $rowname, $tablename);

        $this->execute('behat_general::i_click_on', [$xpathelement, 'xpath_element']);
    }

    /**
     * I should see a tool for a entry of a table.
     *
     * @When /^I should see the tool "([^"]*)" in the "([^"]*)" row of the "([^"]*)" table$/
     *
     * @param $tool string identifier of the tool
     * @param $rowname string identifier of the row
     * @param $tablename string identifier of the table
     * @return string the selector of the searched tool
     * @throws Exception
     */
    public function i_should_see_the_tool_in_the_row_of_the_table($tool, $rowname, $tablename) {
        $xpathelement = $this->get_xpath_of_tool_in_table($tool, $rowname, $tablename);

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"The tool "' . $tool . '"  was not found for the row "'. $rowname.
                '" of the table ' . $tablename, $this->getSession());
        }

        return $xpathelement;
    }

    /**
     * I should see a tool for a entry of a table.
     *
     * @When /^I should see the tool "([^"]*)" in all rows of the "([^"]*)" table$/
     *
     * @param $tool string identifier of the tool
     * @param $tablename string identifier of the table
     * @throws Exception
     */
    public function i_should_see_the_tool_in_all_rows_of_the_table($tool, $tablename) {
        $xpathelement = "//table/tbody/tr[contains(@id, '$tablename') and not(contains(@class, 'emptyrow'))]";

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"The table ' . $tablename . ' was not found.', $this->getSession());
        }

        $xpathelement = $xpathelement . "//a[@title = '$tool']" .
            " | " . $xpathelement. "//span[text() = '$tool']/parent::a".
            " | " . $xpathelement. "//button[text() = '$tool']";

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"The tool "' . $tool .
                '"  was not found for at least one row of the table ' . $tablename, $this->getSession());
        }
    }

    /**
     * I should not see a tool for a entry of a table.
     *
     * @When /^I should not see the tool "([^"]*)" in the "([^"]*)" row of the "([^"]*)" table$/
     *
     * @param $tool string identifier of the tool
     * @param $rowname string identifier of the row
     * @param $tablename string identifier of the table
     * @throws Exception
     */
    public function i_should_not_see_the_tool_in_the_row_of_the_table($tool, $rowname, $tablename) {
        $xpathelement = $this->get_xpath_of_tool_in_table($tool, $rowname, $tablename);

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            return;
        }
        throw new ExpectationException('"The tool "' . $tool . '"  was found for the row "'. $rowname.
            '" of the table ' . $tablename, $this->getSession());
    }

    /**
     * Build the xpath to the tool element and throws exceptions if either the table or the row are not present.
     * @param $tool string identifier of the tool
     * @param $rowname string identifier of the row
     * @param $tablename string identifier of the table
     * @return string xpath of the tool
     * @throws ExpectationException
     */
    private function get_xpath_of_tool_in_table($tool, $rowname, $tablename) {
        $xpathelement = "//table/tbody/tr[contains(@id, '$tablename')]";

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"The table ' . $tablename . ' was not found.', $this->getSession());
        }

        $xpathelement = $xpathelement . "//*[contains(text(),'$rowname')]/ancestor::tr";

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"The row "'. $rowname.
                '" of the table ' . $tablename . ' was not found.', $this->getSession());
        }

        $xpathelement = $xpathelement . "//a[@title = '$tool']" .
            " | " . $xpathelement. "//span[text() = '$tool']/parent::a".
            " | " . $xpathelement. "//button[text() = '$tool']";

        return $xpathelement;
    }

    /**
     * I should not see a tool for a entry of a table.
     *
     * @When /^I should not see the tool "([^"]*)" in any row of the "([^"]*)" table$/
     *
     * @param $tool string identifier of the tool
     * @param $tablename string identifier of the table
     * @throws Exception
     */
    public function i_should_not_see_the_tool_in_any_row_of_the_table($tool, $tablename) {
        $xpathelement = "//table/tbody/tr[contains(@id, '$tablename')]";

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"The table ' . $tablename . ' was not found.', $this->getSession());
        }

        $xpathelement = $xpathelement . "//a[@title = '$tool']" .
            " | " . $xpathelement. "//span[text() = '$tool']/parent::a".
            " | " . $xpathelement. "//button[text() = '$tool']";

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            return;
        }
        throw new ExpectationException('"The tool "' . $tool . '"  was found for a row  of the table ' .
            $tablename, $this->getSession());
    }

    /**
     * Assume a step being at a certain position
     *
     * @When /^the step "([^"]*)" should be at the ([^"]*) position$/
     *
     * @param string $stepname
     * @param int $position
     * @throws ExpectationException
     */
    public function the_step_should_be_at_the_position($stepname, $position) {
        $xpathelement = "//table/tbody/tr[@id = 'tool_cleanupcourses_workflows_r$position']/td[contains(text(),'$stepname')]";

        try {
            $this->find('xpath', $xpathelement);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"' . $stepname . '" step was not found at position ' .
                $position . ' of the workflow.', $this->getSession());
        }
    }


    /**
     * Opens Teacher's Courses Overview.
     *
     * @Given /^I am on cleanupcourses view$/
     */
    public function i_am_on_cleanupcourses_view() {
        $this->getSession()->visit($this->locate_path('/admin/tool/cleanupcourses/view.php'));
    }
}
