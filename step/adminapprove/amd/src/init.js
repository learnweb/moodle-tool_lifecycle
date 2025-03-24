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
        init: function(sesskey, url) {
            $('input[name="checkall"]').click(function() {
                $('input[name="c[]"]').prop('checked', $('input[name="checkall"]').prop('checked'));
            });

            $('.adminapprove-action').each(function() {
                $(this).click(function() {
                    var post = {
                        'act': $(this).attr('data-action'),
                        'c[]': $(this).attr('data-content'),
                        'sesskey': sesskey
                    };
                    var form = document.createElement('form');
                    form.hidden = true;
                    form.method = 'post';
                    form.action = url;
                    for (var k in post) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = k;
                        input.value = post[k];
                        form.append(input);
                    }
                    document.body.append(form);
                    form.submit();
                });
            });
        }
    };
});