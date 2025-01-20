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
 * Course audit module.
 *
 * @module     block_course_audit/audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/modal', 'core/modal_events', 'core/str', 'core/ajax', 'core/templates', 'core/notification'],
function ($, Modal, ModalEvents, Str, Ajax, Templates, Notification) {
    let currentArrow = null; // Track active arrow
    let currentHighlight = null;
    let floatingAnalysisList = {}; // Track all floating analysis elements
    let displayMode = 'block'; // Default display mode: 'block' or 'inline'
    let loadedSectionData = {}; // Cache for section data

    // Check if there's a stored preference for displayMode
    if (window.localStorage) {
        const storedMode = localStorage.getItem('block_course_audit_display_mode');
        if (storedMode) {
            displayMode = storedMode;
        }
    }

    const updateSectionHighlight = function (sectionNumber) {
        // Alte Hervorhebung entfernen
        if (currentHighlight !== null) {
            $(`#section-${currentHighlight}`).removeClass('block-course-audit-active-section');
        }
        // Neue Hervorhebung setzen
        if ((sectionNumber || sectionNumber === 0) && $('#section-' + sectionNumber).length) {
            currentHighlight = sectionNumber;
            $(`#section-${sectionNumber}`).addClass('block-course-audit-active-section');
        }
    };

    const highlightSection = function (sectionNumber) {
        const targetSection = $('#section-' + sectionNumber);
        if (currentArrow) {
            currentArrow?.remove();
            currentArrow = null;
        }
        if (!targetSection.length) { return; }
        // Scroll to section with navbar offset
        $('html, body').animate({
            scrollTop: targetSection.offset().top - 100
        }, 800);

        // Add temporary highlight
        targetSection.addClass('block-course-audit-highlight');
        // Add arrow indicator
        currentArrow = $(
            '<div class="block-course-audit-arrow">' +
            '<i class="fa fa-arrow-right fa-2x text-muted" aria-hidden="true"></i>' +
            '</div>'
        );
        $('body').append(currentArrow);
        // Position arrow next to section
        const updatePosition = () => {
            const rect = targetSection[0].getBoundingClientRect();
            currentArrow.css({
                left: rect.right + 10,
                top: rect.top + window.scrollY + (rect.height / 2) - 25
            });
        };
        updatePosition();
        // Cleanup after 5 seconds
        setTimeout(() => {
            targetSection.removeClass('block-course-audit-highlight');
            currentArrow?.remove();
            currentArrow = null;
        }, 2000);
    };

    // Function to toggle display mode (block or inline)
    const toggleDisplayMode = function() {
        displayMode = displayMode === 'block' ? 'inline' : 'block';

        // Save preference
        if (window.localStorage) {
            localStorage.setItem('block_course_audit_display_mode', displayMode);
        }

        // Update UI based on the current mode
        if (displayMode === 'inline') {
            $('#block-course-audit').addClass('d-none');
            showAllFloatingAnalysis();
        } else {
            $('#block-course-audit').removeClass('d-none');
            hideAllFloatingAnalysis();
        }

        // Update the toggle button text
        updateModeToggleButton();
    };

    // Function to update the toggle button text
    const updateModeToggleButton = function() {
        const stringKey = displayMode === 'block' ? 'inline_mode' : 'block_mode';
        Str.get_string(stringKey, 'block_course_audit').then(function(str) {
            $('#block-course-audit-toggle-mode').text(str);
            return;
        }).catch(Notification.exception);
    };

    // Function to show floating analysis for a specific section
    const showFloatingAnalysis = function(sectionNumber) {
        if (sectionNumber === undefined || sectionNumber === null) {
            return;
        }

        // If we've already loaded this section data, just show it
        if (floatingAnalysisList[sectionNumber]) {
            floatingAnalysisList[sectionNumber].removeClass('d-none');
            return;
        }

        // Otherwise fetch the data from the server
        const section = $('#section-' + sectionNumber);
        if (!section.length) {
            return;
        }

        const sectionId = section.data('sectionid');
        if (!sectionId) {
            return;
        }
        // Use AJAX to fetch section data
        Ajax.call([{
            methodname: 'block_course_audit_get_section_analysis',
            args: { sectionid: sectionId },
            done: function(data) {
                // Cache the data
                loadedSectionData[sectionNumber] = data;
                renderFloatingAnalysis(data);
            },
            fail: Notification.exception
        }]);
    };

    // Function to render the floating analysis
    const renderFloatingAnalysis = function(data) {
        console.log(data);
        if (!data || !data.section_number) {
            return;
        }

        const sectionNumber = data.section_number;
        const section = $('#section-' + sectionNumber);

        // If floating analysis already exists, just show it
        if (floatingAnalysisList[sectionNumber]) {
            floatingAnalysisList[sectionNumber].removeClass('d-none');
            return;
        }

        // Render the template
        Templates.render('block_course_audit/floating_analysis', data)
            .then(function(html) {
                // Insert after the section
                section.after(html);

                // Store reference to the floating analysis
                floatingAnalysisList[sectionNumber] = $('#block-course-audit-floating-' + sectionNumber);

                // Setup event handlers
                initFloatingAnalysisHandlers(sectionNumber);

                return;
            })
            .catch(Notification.exception);
    };

    // Initialize event handlers for a floating analysis
    const initFloatingAnalysisHandlers = function(sectionNumber) {
        const floatingElement = floatingAnalysisList[sectionNumber];

        // Toggle minimize/maximize
        floatingElement.on('click', '.toggle-minimize', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const isMinimized = floatingElement.hasClass('minimized');
            const iconElement = $(this).find('i');

            if (isMinimized) {
                floatingElement.removeClass('minimized');
                iconElement.removeClass('fa-plus').addClass('fa-minus');
                Str.get_string('minimize', 'block_course_audit').then(function(str) {
                    $(e.currentTarget).attr('title', str);
                    return;
                }).catch(Notification.exception);
            } else {
                floatingElement.addClass('minimized');
                iconElement.removeClass('fa-minus').addClass('fa-plus');
                Str.get_string('maximize', 'block_course_audit').then(function(str) {
                    $(e.currentTarget).attr('title', str);
                    return;
                }).catch(Notification.exception);
            }
        });

        // Handle click on minimized element to maximize
        floatingElement.on('click', function(e) {
            if ($(e.target).closest('.toggle-minimize, .close-analysis').length) {
                return;
            }

            if (floatingElement.hasClass('minimized')) {
                floatingElement.removeClass('minimized');
                floatingElement.find('.toggle-minimize i')
                    .removeClass('fa-plus')
                    .addClass('fa-minus');

                Str.get_string('minimize', 'block_course_audit').then(function(str) {
                    floatingElement.find('.toggle-minimize').attr('title', str);
                    return;
                }).catch(Notification.exception);
            }
        });

        // Close button
        floatingElement.on('click', '.close-analysis', function(e) {
            e.preventDefault();
            e.stopPropagation();

            floatingElement.addClass('d-none');
        });
    };

    // Function to hide all floating analysis elements
    const hideAllFloatingAnalysis = function() {
        for (const sectionNumber in floatingAnalysisList) {
            if (Object.prototype.hasOwnProperty.call(floatingAnalysisList, sectionNumber)) {
                floatingAnalysisList[sectionNumber].addClass('d-none');
            }
        }
    };

    // Function to show all floating analysis elements
    const showAllFloatingAnalysis = function() {
        // Get all visible course sections
        $('.section.main').each(function() {
            const sectionId = $(this).attr('id');
            if (sectionId && sectionId.startsWith('section-')) {
                const sectionNumber = sectionId.replace('section-', '');
                showFloatingAnalysis(sectionNumber);
            }
        });
    };

    return {
        init: function () {
            const container = $('#block-course-audit');
            let currentPage = 1;
            const totalPages = container.data('total-pages');

            // Add toggle button to switch between block and inline modes
            if (container.length) {
                const blockHeader = container.find('.card-header').first();
                Str.get_string('inline_mode', 'block_course_audit').then(function(str) {
                    const toggleButton = $('<button/>', {
                        id: 'block-course-audit-toggle-mode',
                        class: 'btn btn-sm btn-outline-secondary ml-2',
                        text: str,
                        title: ''  // Set via Str.get_string below
                    });

                    blockHeader.append(toggleButton);

                    // Add tooltip
                    return Str.get_string('toggle_analysis_view', 'block_course_audit');
                }).then(function(str) {
                    $('#block-course-audit-toggle-mode').attr('title', str);
                    return;
                }).catch(Notification.exception);

                // Attach toggle mode event handler
                container.on('click', '#block-course-audit-toggle-mode', function(e) {
                    e.preventDefault();
                    toggleDisplayMode();
                });
            }

            // Initialize according to current display mode
            if (displayMode === 'inline') {
                if (container.length) {
                    container.addClass('d-none');
                }
                // Only load for section pages, not for disclaimer or summary
                if (currentPage > 1 && currentPage < totalPages) {
                    showAllFloatingAnalysis();
                }
            }

            container.off('click.pagination').on('click.pagination', '.page-nav', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if ($(this).hasClass('disabled')) { return; }

                const direction = $(this).data('direction');
                const newPage = direction === 'next' ? Math.min(currentPage + 1, totalPages) : Math.max(currentPage - 1, 1);

                if (newPage !== currentPage) {
                    if (currentArrow) {
                        currentArrow?.remove();
                        currentArrow = null;
                    }

                    // Hide any visible floating analysis
                    if (displayMode === 'inline') {
                        hideAllFloatingAnalysis();
                    }

                    // Update page visibility
                    $(`#page-content-${currentPage}`).removeClass('d-block').addClass('d-none');
                    $(`#page-content-${newPage}`).removeClass('d-none').addClass('d-block');
                    $('#current-page').text(newPage);

                    // Update button states
                    $('[data-direction="prev"]').prop('disabled', newPage <= 1);
                    $('[data-direction="next"]').prop('disabled', newPage >= totalPages);

                    // Trigger highlight if it's a section page (not first or last page)
                    const activePage = $(`#page-content-${newPage}`);
                    const sectionNumber = activePage.data('section-number');

                    if (sectionNumber || sectionNumber === 0) {
                        highlightSection(sectionNumber);
                        updateSectionHighlight(sectionNumber);

                        // If in inline mode and it's a section page, show floating analysis
                        if (displayMode === 'inline') {
                            showFloatingAnalysis(sectionNumber);
                        }
                    }

                    currentPage = newPage;
                }
            });

            $('[data-toggle="tooltip"]').tooltip({
                container: 'body',
                boundary: 'viewport'
            });
        }
    };
});