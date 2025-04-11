// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
* JavaScript for handling tour creation via button click.
*
* @module block_course_audit/tour_creator
* @copyright 2024 Your Name <your.email@example.com>
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

define(['jquery', 'core/ajax', 'core/notification', 'core/str', 'core/config'], function ($, Ajax, Notification, Str) {

    let speechBubble = null;
    let miauContainer = null;
    let bubbleContainer = null;
    let speechBubbleInner = null;
    let miauWrapper = null;

    const getElements = function () {
        miauWrapper = document.getElementById('miau-wrapper');
        speechBubble = document.getElementById('miau-speech-bubble');
        miauContainer = document.getElementById('miau-gif');
        bubbleContainer = document.getElementById('bubble-container');
        speechBubbleInner = document.getElementById('miau-speech-bubble-inner');

        if (!miauWrapper || !speechBubble || !miauContainer || !bubbleContainer || !speechBubbleInner) {
            console.error('Course Audit: Could not find one or more required sprite/bubble elements. Ensure templates are loaded.');
        }
    };

    /**
    * Initialize the module.
    *
    * @param {int} courseId The course ID
    */
    const init = function (courseId) {
        getElements();

        if (!miauWrapper) {
            return;
        }

        addMiauSprite();

        $(bubbleContainer).find('.btn-minimize').on('click', function (e) { // Assuming button is inside bubble
            e.preventDefault();
            e.stopPropagation();
            hideBubble();
        });
        $('#audit-start').on('click', function (e) {
            e.preventDefault();

            const $button = $(this);
            const originalText = $button.text();
            $button.prop('disabled', true);
            Str.get_string('creatingtour', 'block_course_audit').then(function (loadingText) {
                $button.text(loadingText);

                return Ajax.call([{
                    methodname: 'block_course_audit_create_tour',
                    args: {
                        courseid: courseId
                    }
                }])[0];
            }).then(function (response) {
                var tourdata = response.tourdata;
                if (!tourdata || !response.status) {
                    let errorMessage = response.message || 'Unknown error creating tour data.';
                    console.error('Tour creation failed:', errorMessage);
                    throw new Error(errorMessage);
                }
                if (response.status) {
                    hideBubble();
                    // TODO use usertour external:
                    // * @param   int     $tourid     The ID of the tour to fetch.
                    // * @param   int     $context    The Context ID of the current page.
                    // * @param   string  $pageurl    The path of the current page.
                    // * @return  array               As described in fetch_and_start_tour_returns
                    // */
                    // public static function fetch_and_start_tour($tourid, $context, $pageurl) {
                    // tool_usertours_fetch_and_start_tour

                    // return Ajax.call([{
                    //     methodname: 'tool_usertours_fetch_and_start_tour',
                    //     args: {
                    //     tourid,
                    //     context: moodleConfig.contextid,
                    //     pageurl: window.location.href,
                    // }
                    // }])[0];
                    require(['tool_usertours/usertours'], function (usertours) {
                        usertours.init(tourdata.tourDetails, tourdata.filterNames);
                    });
                    return Str.get_string('toursuccess', 'block_course_audit');
                }
            }).then(function (successText) {
                $button.text(successText);
            }).catch(function (error) {
                Notification.exception(error);
                $button.text(originalText);
                $button.prop('disabled', false);
            });
        });
    };
    const moveBlockToSprite = function () {
        // TODO rework
        // if (!speechBubble) {
        //     getElements();
        // }
        // if (speechBubble) {
        //     var $block = $('#block-course-audit');
        //     $(speechBubble).append($block);
        //     $block.show();
        // } else {
        //     console.error('Course Audit: Cannot move block, speechBubble element not found.');
        // }
    };
    const hideBubble = function () {
        if (bubbleContainer && $(bubbleContainer).is(":visible")) {
            $(miauContainer).removeClass('miau-talk');
            $(bubbleContainer).hide();
        }
    };
    const addMiauSprite = function () {
        setTimeout(function () {
            if (miauWrapper) {
                $(miauWrapper).css('opacity', '1');
                $(miauWrapper).removeClass('slide-in');
            }
        }, 2000);
        moveBlockToSprite();
        if (miauWrapper) {
            $(miauWrapper).click(function () {
                triggerTalkAnimation();
                if (bubbleContainer && !$(bubbleContainer).is(":visible")) {
                    $(bubbleContainer).show();
                }
            });
        }
    };
    const triggerTalkAnimation = function () {
        if (miauContainer) {
            $(miauContainer).removeClass('miau-talk');
            $(miauContainer).addClass('miau-talk');
            setTimeout(function () {
                $(miauContainer).removeClass('miau-talk');
            }, 8000);
        }
    };
    return {
        init: init
    };
});