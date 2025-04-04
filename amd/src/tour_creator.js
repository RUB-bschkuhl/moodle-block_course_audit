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
 * JavaScript for handling tour creation via button click.
 *
 * @module     block_course_audit/tour_creator
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str', 'core/config'], function ($, Ajax, Notification, Str, Config) {
    /**
     * Initialize the module.
     *
     * @param {int} courseId The course ID
     */
    const init = function (courseId) {
        addMiauSprite(); //For fun. Ggf ausklammern

        // Add click handler to the audit-start button
        $('#audit-start').on('click', function (e) {
            e.preventDefault();

            // Show loading indicator
            const $button = $(this);
            const originalText = $button.text();
            $button.prop('disabled', true);

            Str.get_string('creatingtour', 'block_course_audit').then(function (loadingText) {
                $button.text(loadingText);

                // Call AJAX to create the tour
                return Ajax.call([{
                    methodname: 'block_course_audit_create_tour',
                    args: {
                        courseid: courseId
                    }
                }])[0];
            }).then(function (response) {
                console.log('Tour created successfully:', response);

                // Show success message briefly before reloading
                return Str.get_string('toursuccess', 'block_course_audit');
            }).then(function (successText) {
                $button.text(successText);

                // Reload the page after a short delay to start the tour
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            }).catch(function (error) {
                console.error('Error creating tour:', error);
                Notification.exception(error);
                $button.text(originalText);
                $button.prop('disabled', false);
            });
        });
    };

    const moveBlockToSprite = function () {
        var $original = $('#block-course-audit');
        var $copy = $original.clone();
        $original.hide();
        $('#miau-speech-bubble').append($copy);
    };

    const addMiauSprite = function () {
        const $speechBubble = $('<div>').attr({
            'id': 'miau-speech-bubble',
            'aria-hidden': 'true'
        }).html(`<a href="` + Config.wwwroot + `/blocks/course_audit/wiki.php" target="_blank">
            <span id="miau-title">MIau.nrw</span></a>`);
        $speechBubble.append(
            $('<div>').attr({ 'id': 'miau-speech-bubble-inner' })
        );

        const $miauContainer = $('<div>').attr({
            'id': 'miau-gif',
            'aria-hidden': 'true',
            'role': 'presentation',
            'tabindex': '0'
        });

        const $bubbleContainer = $('<div>').attr({
            'id': 'bubble-container',
            'aria-hidden': 'true',
            'role': 'presentation',
        });

        $bubbleContainer.append($speechBubble);
        $('#page').append($miauContainer);
        $miauContainer.click(function () {
            if ($speechBubble.is(":visible")) {
                $bubbleContainer.remove();
            } else {
                $miauContainer.append($bubbleContainer);
                moveBlockToSprite();
            }
        });
    };

    return {
        init: init
    };
});