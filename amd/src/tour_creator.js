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
* @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
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

            initMinimizeButton();

            bindStartAudit(courseId);

            initToggleDetails();

            listenForTourStart();
            listenForStepChange();
            listenForTourEnd(courseId);
        };

        const bindStartAudit = function (courseId) {
            console.log("bindStartAudit", courseId);
            $('.audit-start-button').on('click', function (e) {
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
                    //TODO tourData.tourDetails[0] might not exist when all checks ok
                    if (tourData.tourDetails[0]) {
                    } else {
                        //TODO Handle course audit no results
                    }
                    //TODO check if there is a running tour and then get the actionDetailsMap,
                    // else it doesnt work when tour is started in any other way than clicking the button
                    // e.g. continuing after reload
                    if (response.actionDetailsMap) {
                        storedActionDetails = response.actionDetailsMap;
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
                }).catch(function () {
                    $button.text(originalText);
                    $button.prop('disabled', false);
                });
            });
        };

        const callAction = function (event) {
            const currentButton = $(event.target);
            const classList = event.target.classList;
            const auditActionClass = Array.from(classList).find(className => className.startsWith('audit-action-'));
            if (auditActionClass) {
                const idParts = auditActionClass.split('-');
                if (idParts.length === 3) {
                    const mapKey = idParts[2];
                    const actionDetails = Object.values(storedActionDetails).find(details => details.mapkey === mapKey);
                    if (actionDetails) {
                        const details = actionDetails;
                        const args = {};
                        const params = details.params.split('&');
                        params.forEach(param => {
                            const [key, value] = param.split('=');
                            args[key] = value;
                        });
                        //TODO show loading state
                        //TODO wenn action ok dann sollte summary das reflektieren
                        currentButton.off('click');
                        currentButton.addClass('disabled-button-visuals');

                        Ajax.call([{
                            methodname: details.endpoint,
                            args: args
                        }])[0].then(function (response) {
                            console.log("response", response, response.status);
                            if (response && response.status) {
                                currentButton.html('&#10004; Done');
                                currentButton.removeClass('btn-primary');
                            }
                        });
                    } else {
                        currentButton.off('click');
                        currentButton.addClass('disabled-button-visuals');
                    }
                }
            }
        };

        const listenForTourEnd = function (courseId) {
            const userTourEvents = userTourEventsModule.eventTypes;
            document.addEventListener(userTourEvents.tourEnded, function () {
                startTourSummary(courseId);
            });
        };

        const listenForTourStart = function () {
            const userTourEvents = userTourEventsModule.eventTypes;
            //tourStart never fires?
            document.addEventListener(userTourEvents.tourStarted, function () {
                expandAllSections();
                $(document).on('click', '.course-audit-action-button', function (event) {
                    event.preventDefault();
                    callAction(event);
                });
            });
        };

        const listenForStepChange = function () {
            const userTourEvents = userTourEventsModule.eventTypes;
            /*             document.addEventListener(userTourEvents.stepRendered, function () {
                        }); */
            document.addEventListener(userTourEvents.stepHide, function () {
                console.log("stepHide");
                // TODO klick neben Tour Element cancelled Tour.
                // Dieses Event hier wird gefeuert, danach wird der nächste Step nicht gerendered.
                // event genauer betrachten wodurch es ausgelöst wird.
                // Die Tour muss dann neu gestartet werde. Fix überlegen?
            });
        };

        const startTourSummary = function (courseId) {
            const promise = Ajax.call([{
                methodname: 'block_course_audit_get_summary',
                args: {
                    courseid: courseId
                }
            }])[0];

            return promise.then(async function (response) {
                if (response && response.status && response.data) {
                    let summaryContainer = $(speechBubble);

                    const processedResultsPromises = response.data.map(async function (result) {
                        const ruleNameKey = 'rule_' + result.rulekey + '_name';
                        let ruleNameDisplay = '';
                        let parsedMessages = [];

                        try {
                            ruleNameDisplay = await Str.get_string(ruleNameKey, 'block_course_audit');
                        } catch (e) {
                            ruleNameDisplay = result.rulekey;
                        }

                        try {
                            parsedMessages = JSON.parse(result.messages);
                            if (!Array.isArray(parsedMessages)) {
                                parsedMessages = [String(parsedMessages)];
                            }

                            // Process messages to handle language strings
                            const messagePromises = parsedMessages.map(async msg => {
                                // Check if message is a language key pattern like [[key]]
                                const langKeyMatch = msg.match(/^\[\[(.*?)\]\]$/);
                                if (langKeyMatch) {
                                    const langKey = langKeyMatch[1];
                                    try {
                                        let str = await Str.get_string(langKey, 'block_course_audit');
                                        return str;
                                    } catch (e) {
                                        return 'Error: ' + langKey;
                                    }
                                }
                                return msg;
                            });
                            parsedMessages = await Promise.all(messagePromises);
                        } catch (e) {
                            parsedMessages = [result.messages || 'Error displaying message.'];
                        }

                        return {
                            ...result,
                            ruleNameDisplay: ruleNameDisplay,
                            messages: parsedMessages,
                            isTodo: result.status === '0',
                            isDone: result.status === '1',
                        };
                    });
                    const processedResults = await Promise.all(processedResultsPromises);
                    const templateContext = {
                        results: processedResults,
                        hasResults: processedResults.length > 0,
                        timecreated: new Date(response.timecreated * 1000).toLocaleDateString('de-DE', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit'
                        }),
                        rulecount: processedResults.length,
                        passedcount: processedResults.filter(result => result.status === '1').length,
                        failedcount: processedResults.filter(result => result.status === '0').length,
                    };

                    return Templates.render('block_course_audit/block/summary', templateContext)
                        .then(function (html, js) {
                            summaryContainer.empty();
                            Templates.appendNodeContents(summaryContainer, html, js);
                            if (bubbleContainer && !$(bubbleContainer).is(":visible")) {
                                triggerTalkAnimation();
                                $(bubbleContainer).show();
                            }
                            initMinimizeButton();
                            initToggleDetails();
                            return response;
                        })
                        .catch(function () {
                            return response;
                        });
                }

                return response;
            });
        };
        const expandAllSections = function () {
            const allSections = document.querySelectorAll('[id^="collapsesections"]');
            allSections.forEach(section => {
                //check if all closed
                if (section.getAttribute('aria-expanded') === 'false') {
                    section.click();
                } else {
                    const individualSections = document.querySelectorAll('[id^="collapsesectionid"]');
                    individualSections.forEach(i => {
                        //check if some closed
                        if (i.getAttribute('aria-expanded') === 'false') {
                            i.click();
                        }
                    });
                }
            });
        };
        const initMinimizeButton = function () {
            $(bubbleContainer).find('.btn-minimize').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                hideBubble();
            });
        };
        const initToggleDetails = function () {
            const headers = document.querySelectorAll('.check-header');
            headers.forEach(header => {
                header.addEventListener('click', function () {
                    toggleRuleCheck(this);
                });
            });
        };
        const toggleRuleCheck = function (header) {
            const ruleCheck = header.parentElement;
            ruleCheck.classList.toggle('expanded');
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