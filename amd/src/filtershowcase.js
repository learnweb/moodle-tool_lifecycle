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
 * Javascript to filter workflows in showcase page by used triggers or steps.
 * @module     tool_lifecycle/filtershowcase
 * @copyright  2026 Thomas Niedermaier University Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Helper function to iterate through the visible cards and filter them by a classname
 * @param {String} classname by which to filter the cards
 */
function iteratecards(classname) {
    const cards = document.getElementsByClassName('lifecycle-card');
    cards.forEach(card => {
        if (!card.classList.contains(classname)) {
            card.style.visibility = "hidden";
        }
    });
}

/**
 * Helper function to reset all cards to visible.
 */
function setallcardsvisible() {
    let cards = document.getElementsByClassName('lifecycle-card');
    cards.forEach(card => {
        card.style.visibility = "visible";
    });
}

/**
 * Init function
 */
export function init() {
    const filterselects = document.getElementsByClassName('showcasefilterselect');

    filterselects.forEach((s) => {
        s.onchange = (event) => {
            let classname = event.target.value;
            iteratecards(classname);
        };
    });

    const resetbutton = document.getElementById('lifecycle-filter-resetbutton');
    resetbutton.onclick = () => {
        setallcardsvisible();
        filterselects.forEach((s) => {
            s.selectedIndex = 0;
        });
    };
}
