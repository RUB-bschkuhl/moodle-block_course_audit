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

define(['jquery', 'core/ajax', 'core/str', 'tool_usertours/events'],
    function ($, Ajax, Str, userTourEventsModule) {

        let speechBubble = null;
        let miauContainer = null;
        let bubbleContainer = null;
        let miauWrapper = null;

        const getElements = function () {
            miauWrapper = document.getElementById('miau-wrapper');
            speechBubble = document.getElementById('miau-speech-bubble');
            miauContainer = document.getElementById('miau-gif');
            bubbleContainer = document.getElementById('bubble-container');

            if (!miauWrapper || !speechBubble || !miauContainer || !bubbleContainer) {
                console.error('Course Audit: Could not find one or more required elements. Ensure templates are loaded.');
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

            $(bubbleContainer).find('.btn-minimize').on('click', function (e) {
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
                    var tourData = response.tourdata;
                    listenForTourStart();
                    listenForTourEnd(tourData.tourDetails[0].tourId);
                    if (!tourData || !response.status) {
                        let errorMessage = response.message || 'Unknown error creating tour data.';
                        throw new Error(errorMessage);
                    }
                    if (response.status) {
                        hideBubble();
                        require(['tool_usertours/usertours'], function (usertours) {
                            usertours.init(tourData.tourDetails, tourData.filterNames);
                        });
                        return Str.get_string('toursuccess', 'block_course_audit');
                    }
                    return Str.get_string('tourerror', 'block_course_audit');
                }).then(function (text) {
                    $button.text(text);
                }).catch(function (errors) {
                    console.error(errors);
                    $button.text(originalText);
                    $button.prop('disabled', false);
                });
            });
        };

        const listenForTourEnd = function (tourId) {
            const userTourEvents = userTourEventsModule.eventTypes;
            document.addEventListener(userTourEvents.tourEnded, function () {
                // $(miauWrapper).show(); // TODO doesnt show when tour is cancelled
                startTourSummary(tourId);
            });
        };

        const listenForTourStart = function () {
            const userTourEvents = userTourEventsModule.eventTypes;
            document.addEventListener(userTourEvents.tourStarted, function () {
                // $(miauWrapper).hide();
            });
        };

        const startTourSummary = function (tourId) {
            console.log("startTourSummary");
            const promise = Ajax.call([{
                methodname: 'block_course_audit_get_summary',
                args: {
                    tourid: tourId
                }
            }])[0];
            return promise.then(function (response) {
                console.log(response);
            }).catch(function (errors) {
                console.error(errors);
            });
            // TODO: start summary
            // TODO: get audit results from table
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