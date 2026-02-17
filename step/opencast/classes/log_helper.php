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
 * Helper class to handle logs printing in Opencast Step
 *
 * @package    lifecyclestep_opencast
 * @copyright  2026 Farbod Zamani Boroujeni, elan e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_opencast;

/**
 * Helper class to handle logs printing in Opencast Step
 */
class log_helper {
    /** @var bool $traceenabled Whether tracing is On. */
    private bool $traceenabled = true;
    /**
     * Constructor function.
     * @param bool $traceenabled A flag to make sure that the settings is applied.
     */
    public function __construct(bool $traceenabled) {
        $this->traceenabled = $traceenabled;
    }
    /**
     * Prints mtrace log cleaner.
     *
     * @param string $message The message
     * @param string $prefix the message prefix.
     * @param int $ind The level of indentation for the message prefix.
     * @param bool $newline the flag to set if the mrtace should also print end of line.
     */
    public function print_mtrace(string $message, string $prefix = '', int $ind = 0, bool $newline = true): void {
        // Prevent printing mtrace if disabled.
        if (!$this->traceenabled) {
            return;
        }
        /*
         * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameLowerCase
         */
        global $FULLSCRIPT;
        $ishtml = !empty($FULLSCRIPT) ? str_contains($FULLSCRIPT, 'run.php') : false;
        /*
         * phpcs:enable
         * Enable enables back all sniffer rules!
         */

        $eol = '';
        if ($newline) {
            $eol = "\n";
            // HTML end of line if called by run-command of workflowoverview.
            if ($ishtml) {
                $eol = "<br>";
            }
        }

        $indstr = "";
        if ($ind > 0) {
            for ($i = 0; $i < $ind; $i++) {
                $indannotation = "      ";
                if ($ishtml) {
                    $indannotation = "&nbsp;&nbsp;";
                }
                $indstr .= $indannotation;
            }
        }
        $message = $indstr . $message;

        if (!empty($prefix)) {
            $message = $prefix . $message;
        }

        mtrace($message, $eol);
    }
}
