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

define(['jquery', 'core/ajax', 'core/str', 'tool_usertours/events', 'core/templates'],
    function ($, Ajax, Str, userTourEventsModule, Templates) {

        let speechBubble = null;
        let miauContainer = null;
        let bubbleContainer = null;
        let miauWrapper = null;
        let storedActionDetails = {};

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
                    let tourData = response.tourdata;
                    listenForTourStart();
                    listenForStepChange();
                    //TODO tourData.tourDetails[0] might not exist when all checks ok
                    if (tourData.tourDetails[0]) {
                        listenForTourEnd(tourData.tourDetails[0].tourId);
                    } else {
                        console.error('Course Audit: Could not find tour details in response');
                        //TODO Handle course audit no results
                    }

                    if (!tourData || !response.status) {
                        let errorMessage = response.message || 'Unknown error creating tour data.';
                        throw new Error(errorMessage);
                    }
                    
                    if (response.actionDetailsMap) {
                        storedActionDetails = response.actionDetailsMap;
                    }

                    $(document).on('click', '.course-audit-action-button', function (event) {
                        event.preventDefault();
                        callAction(event);
                    });

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

        const callAction = function (event) {
            const classList = event.target.classList;
            const auditActionClass = Array.from(classList).find(className => className.startsWith('audit-action-'));
            if (auditActionClass) {
                const idParts = auditActionClass.split('-');
                if (idParts.length === 4) {
                    const sectionId = idParts[2];
                    const ruleKey = idParts[3];
                    const mapKey = `section_${sectionId}_${ruleKey}`;
                    const actionDetails = Object.values(storedActionDetails).find(details => details.mapkey === mapKey);
                    if (actionDetails) {
                        const details = actionDetails;
                        const args = {};
                        const params = details.params.split('&');
                        params.forEach(param => {
                            const [key, value] = param.split('=');
                            args[key] = value;
                        });
                        //TODO then console log response
                        let response = Ajax.call([{
                            methodname: details.endpoint,
                            args: args
                        }])[0];
                        console.log('response', response);
                        return response;
                    } else {
                        console.error('Action details not found for key:', mapKey);
                    }
                }
            }
        };

        const listenForTourEnd = function (tourId) {
            const userTourEvents = userTourEventsModule.eventTypes;
            document.addEventListener(userTourEvents.tourEnded, function () {
                console.log("tourEnded");
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

        const listenForStepChange = function () {
            const userTourEvents = userTourEventsModule.eventTypes;
            document.addEventListener(userTourEvents.stepRendered, function () {
                /*   const stepElement = $('[id^="tour-step-tool_usertours"]');
                   $('[id^="tour-step-tool_usertours"]').each(function() {
                      if ($(this).is(':visible')) {
                          console.log('stepElement', $(this));
                      }
                  });
                  if (stepElement) {
                      stepElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  } else {
                      console.warn('Course Audit: Could not find tour step element to scroll:', stepElement);
                  } */
            });
            document.addEventListener(userTourEvents.stepHide, function (event) {
                // TODO klick neben Tour Element cancelled Tour.
                // Dieses Event hier wird gefeuert, danach wird der nächste Step nicht gerendered.
                // event genauer betrachten wodurch es ausgelöst wird.
                // Die Tour muss dann neu gestartet werde. Fix überlegen?
                console.log("stepHide", event);
            });
        };

        const startTourSummary = function (tourId) {
            const promise = Ajax.call([{
                methodname: 'block_course_audit_get_summary',
                args: {
                    tourid: tourId
                }
            }])[0];

            return promise.then(function (response) {
                if (response && response.status && response.data) {
                    let summaryContainer = $(speechBubble);

                    const processedResults = response.data.map(function (result) {
                        return {
                            ...result,
                            isPassed: result.status === 'pass',
                            isFailed: result.status === 'fail'
                        };
                    });

                    // Organize the data for the template
                    const templateContext = {
                        results: processedResults,
                        tourId: tourId,
                        hasResults: processedResults.length > 0,
                        message: response.message,
                        status: response.status
                    };
                    console.log("templateContext", templateContext);
                    console.log("response.data", response.data);
                    // Render the template and update the container
                    return Templates.render('block_course_audit/block/summary', templateContext)
                        .then(function (html, js) {
                            summaryContainer.empty();
                            Templates.appendNodeContents(summaryContainer, html, js);
                            if (bubbleContainer && !$(bubbleContainer).is(":visible")) {
                                triggerTalkAnimation();
                                $(bubbleContainer).show();
                            }
                            return response;
                        })
                        .catch(function (error) {
                            console.error('Error rendering template:', error);
                            return response;
                        });
                }

                return response;
            }).catch(function (errors) {
                console.error(errors);
            });
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
                $('#start-course-audit').add(miauWrapper).click(function () {
                    if (bubbleContainer && !$(bubbleContainer).is(":visible")) {
                        triggerTalkAnimation();
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