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
 * Step definition for life cycle step adminapprove.
 *
 * @package    lifecyclestep_adminapprove
 * @category   test
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../../../lib/behat/behat_base.php');

/**
 * Step definition for life cycle.
 *
 * @package    lifecyclestep_adminapprove
 * @category   test
 * @copyright  2025 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_lifecyclestep_adminapprove extends behat_base {
    /**
     * Opens Admin Approvals page.
     *
     * @Given /^I am on adminapprove page$/
     */
    public function i_am_on_adminapprove_page() {
        $this->getSession()->visit($this->locate_path('/admin/tool/lifecycle/step/adminapprove/index.php'));
    }
}
