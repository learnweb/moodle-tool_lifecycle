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
 * Controls the edit switch.
 *
 * @module     tool_lifecycle/edit_switch
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Toggle the edit switch
 *
 * @method
 * @protected
 * @param {HTMLElement} editSwitch
 * @param {string} formid to identify/diversify the surrounding form
 */
const toggleEditSwitch = (editSwitch, formid) => {
    if (editSwitch.checked) {
        editSwitch.setAttribute('aria-checked', true);
    } else {
        editSwitch.setAttribute('aria-checked', false);
    }

    if (!event.defaultPrevented) {
        editSwitch.setAttribute('disabled', true);
        let frm = document.getElementsByName(formid + 'form')[0];
        frm.submit();
    }
};

/**
 * Add the eventlistener for the editswitch.
 *
 * @param {string} editingSwitchId The id of the editing switch to listen for
 * @param {string} formid to identify/diversify the surrounding form
 */
export const init = (editingSwitchId, formid) => {
    const editSwitch = document.getElementById(editingSwitchId);
    editSwitch.addEventListener('change', () => {
        toggleEditSwitch(editSwitch, formid);
    });
};
