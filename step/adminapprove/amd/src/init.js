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
 * Life Cycle Admin Approve Step AMD Module
 *
 * @module     lifecyclestep_adminapprove/init
 * @copyright  2019 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    return {
        init: function(totalrows) {

            $('#adminapprove_totalrows').html(totalrows);

            $('input[name="checkall"]').click(function() {
                $('input[name="c[]"]').prop('checked', $('input[name="checkall"]').prop('checked'));
            });

            $('.selectedbutton').click(function() {
                const sesskey = this.getAttribute('sesskey');
                const stepid = this.getAttribute('stepid');
                const action = this.getAttribute('action');
                const checkboxes = document.querySelectorAll('input[name="c[]"]');
                let data = [];
                let input;
                for (let i = 0; checkboxes[i]; ++i) {
                    if (checkboxes[i].checked) {
                        data.push(checkboxes[i].value);
                    }
                }
                let datalength = data.length;
                if (datalength > 0) {
                    let form = document.createElement('form');
                    form.hidden = true;
                    form.method = 'post';
                    form.action = '';
                    for (let i = 0; i < datalength; i++) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'c[]';
                        input.value = data[i];
                        form.append(input);
                    }
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'action';
                    input.value = action;
                    form.append(input);
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'stepid';
                    input.value = stepid;
                    form.append(input);
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'sesskey';
                    input.value = sesskey;
                    form.append(input);
                    document.body.append(form);
                    form.submit();
                }
            });
        }
    };
});