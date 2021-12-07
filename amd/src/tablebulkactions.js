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
 * Javascript controller for checkboxed table.
 * @module     tool_lifecycle/tablebulkactions
 * @copyright  2021 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Helper function to redirect via POST
 * @param {String} url redirect to
 * @param {Array} data redirect with data
 */
function redirectPost(url, data) {
    const form = document.createElement('form');
    document.body.appendChild(form);
    form.method = 'post';
    form.action = url;
    for (const pair of data) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = pair.k;
        input.value = pair.v;
        form.appendChild(input);
    }
    form.submit();
}

/**
 * Init function
 */
export function init() {
    const checkboxes = document.querySelectorAll('input[name="procerror-select"]');

    const action = document.querySelectorAll('*[data-lifecycle-action]');
    action.forEach((a) => {
        a.onclick = (e) => {
            e.preventDefault();
            let data = [
                {k: 'action', v: a.getAttribute('data-lifecycle-action')},
                {k: 'sesskey', v: M.cfg.sesskey}
            ];
            if (a.getAttribute('data-lifecycle-forall') === '1') {
                data.push({k: 'all', v: '1'});
                redirectPost(window.location, data);
            } else  {
                checkboxes.forEach((c) => {
                    if (c.checked) {
                        data.push({k: 'id[]', v: c.value});
                    }
                });
                redirectPost(window.location, data);
            }
        };
    });
}