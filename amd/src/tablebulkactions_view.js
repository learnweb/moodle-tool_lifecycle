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
 * Javascript controller for submitting (table with) select fields.
 * @module     tool_lifecycle/tablebulkactions_view
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Helper function to redirect via POST
 */
function redirectPost() {

    let data = [];
    data.push({k: 'sesskey', v: M.cfg.sesskey});
    data.push({k: 'bulkedit', v: 1});

    let datatosubmit = false;
    const selects = document.querySelectorAll('select[name="bulkactions"]');
    selects.forEach((s) => {
        if (s.value) {
            data.push({k: 'bulkactions[]', v: s.value});
            datatosubmit = true;
        }
    });

    if (datatosubmit) {
        const form = document.createElement('form');
        document.body.appendChild(form);
        form.method = 'post';
        form.action = '';
        for (const pair of data) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = pair.k;
            input.value = pair.v;
            form.appendChild(input);
        }
        form.submit();
    }
}

/**
 * Init function
 */
export function init() {
    const submitbuttons = document.querySelectorAll('input[name="button_submit_action_table"]');

    submitbuttons.forEach((s) => {
        s.onclick = (e) => {
            e.preventDefault();
            redirectPost();
        };
    });
}