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
 * Rule form JavaScript functionality.
 *
 * @module     block_course_audit/rule_form
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/str'], function($, str) {
    'use strict';

    /**
     * Initialize the rule form functionality.
     */
    function init() {
        $(document).ready(function() {

            /**
             * Initialize collapsible functionality for existing rules section
             */
            function initCollapsibleRules() {
                var $header = $('#rules-header');
                var $content = $('#rules-content');
                var $icon = $('#collapse-icon');

                // Check saved state - default to collapsed if no preference saved
                var savedState = localStorage.getItem('rules-section-collapsed');
                var isCollapsed = savedState === null ? true : savedState === 'true';

                if (isCollapsed) {
                    $content.addClass('collapsed');
                    $icon.addClass('collapsed');
                }

                // Handle header click
                $header.on('click', function(e) {
                    // Don't collapse when clicking the New Rule button
                    if ($(e.target).closest('.new-rule-button').length) {
                        return;
                    }

                    var isCurrentlyCollapsed = $content.hasClass('collapsed');

                    if (isCurrentlyCollapsed) {
                        // Expand
                        $content.removeClass('collapsed');
                        $icon.removeClass('collapsed');
                        localStorage.setItem('rules-section-collapsed', 'false');
                    } else {
                        // Collapse
                        $content.addClass('collapsed');
                        $icon.addClass('collapsed');
                        localStorage.setItem('rules-section-collapsed', 'true');
                    }
                });
            }

            /**
             * Handle clicking on existing rules to load them into the form
             */
            function initRuleLoader() {
                $('.existing-rule-item').on('click', function() {
                    var ruleId = $(this).data('rule-id');
                    var courseid = new URLSearchParams(window.location.search).get('courseid');

                    if (!ruleId || !courseid) {
                        return;
                    }

                    // Visual feedback
                    $('.existing-rule-item').removeClass('current-rule');
                    $(this).addClass('current-rule loading');

                    // Load rule data via AJAX
                    $.ajax({
                        url: M.cfg.wwwroot + '/blocks/course_audit/edit_rule.php',
                        type: 'GET',
                        data: {
                            ajax_load: 1,
                            ruleid: ruleId,
                            courseid: courseid
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                populateFormWithRuleData(response.data);

                                // Update hidden form fields
                                $('#id_courseid').val(courseid);
                                $('#id_id').val(ruleId);

                                // Update submit button text to "Update"
                                var submitBtn = $('#id_submitbutton');
                                if (submitBtn.length) {
                                    str.get_string('update', 'moodle').then(function(updateString) {
                                        submitBtn.val(updateString);
                                    }).catch(function() {
                                        submitBtn.val('Update'); // Fallback
                                    });
                                }

                                // Update URL to reflect the loaded rule
                                var newUrl = new URL(window.location);
                                newUrl.searchParams.set('id', ruleId);
                                window.history.pushState({}, '', newUrl);
                            } else {
                                window.console.error('Failed to load rule: ' + (response.error || 'Unknown error'));
                                alert('Failed to load rule data. Please try again.');
                            }
                        },
                        error: function() {
                            window.console.error('AJAX request failed when loading rule');
                            alert('Failed to load rule data. Please check your connection.');
                        },
                        complete: function() {
                            $('.existing-rule-item').removeClass('loading');
                        }
                    });
                });

                // Handle New Rule button click
                $('#new-rule-button').on('click', function() {
                    // Clear the form
                    clearForm();

                    // Remove current rule highlighting
                    $('.existing-rule-item').removeClass('current-rule');

                    // Update URL to remove rule ID (for new rule)
                    var newUrl = new URL(window.location);
                    var courseid = newUrl.searchParams.get('courseid');
                    newUrl.searchParams.delete('id');
                    window.history.pushState({}, '', newUrl);

                    // Update hidden form fields
                    $('#id_courseid').val(courseid);
                    $('#id_id').val('');

                    // Reset form to default state and trigger visibility updates
                    setTimeout(function() {
                        // Update submit button text to "Save Changes" after form reset
                        var submitBtn = $('#id_submitbutton');
                        if (submitBtn.length) {
                            str.get_string('savechanges', 'moodle').then(function(saveString) {
                                submitBtn.val(saveString);
                            }).catch(function() {
                                submitBtn.val('Save changes'); // Fallback
                            });
                        }
                        // Set default values
                        $('#id_source_0').val('section').trigger('change');
                        $('#id_check_type_0').val('setting').trigger('change');
                        $('#id_res_scope_0').val('course').trigger('change');
                        $('#id_res_type_0').val('hint').trigger('change');

                        // Update form state
                        updateSourceFields();
                        updateResolutionFieldVisibility();
                        updateLogicalOperatorVisibility();
                    }, 100);

                    // Focus on rule name field
                    $('#id_rule_name').focus();
                });
            }

            /**
             * Populate the form with rule data
             * @param {Object} data - The rule data object containing all rule information
             */
            function populateFormWithRuleData(data) {
                try {
                    // Clear form first
                    clearForm();

                    // Basic rule info
                    $('#id_rule_name').val(data.rule_name || '');
                    $('#id_rule_description').val(data.rule_description || '');

                    // Handle preconditions
                    if (data.preconditions && data.preconditions.length > 0) {
                        $('#id_preconditions option').prop('selected', false);
                        data.preconditions.forEach(function(precondId) {
                            $('#id_preconditions option[value="' + precondId + '"]').prop('selected', true);
                        });
                    }

                    // Handle group selection
                    if (data.existing_group) {
                        $('#id_existing_group').val(data.existing_group);
                        $('#id_new_group_name').val('');
                    }

                    // Handle checks
                    if (data.checks && data.checks.length > 0) {
                        populateChecks(data.checks);
                    }

                    // Handle resolutions
                    if (data.resolutions && data.resolutions.length > 0) {
                        populateResolutions(data.resolutions);
                    }

                    // Trigger change events to update field visibility
                    setTimeout(function() {
                        // Trigger change events for all form elements that affect visibility
                        $('[id^="id_source_"]').trigger('change');
                        $('[id^="id_check_type_"]').trigger('change');
                        $('[id^="id_res_type_"]').trigger('change');
                        $('[id^="id_res_actiontype_"]').trigger('change');
                        $('[id^="id_res_scope_"]').trigger('change');
                        $('#id_existing_group').trigger('change');

                        // Update internal functions
                        updateSourceFields();
                        updateResolutionFieldVisibility();
                        updateLogicalOperatorVisibility();

                        // Additional trigger for dependent fields after initial setup
                        setTimeout(function() {
                            $('[id^="id_source_0"]').trigger('change');
                            $('[id^="id_check_type_"]').trigger('change');
                            $('[id^="id_content_comp_"]').trigger('change');
                        }, 50);
                    }, 150);

                } catch (error) {
                    window.console.error('Error populating form:', error);
                    alert('Error populating form with rule data.');
                }
            }

            /**
             * Populate checks section
             * @param {Array} checks - Array of check objects to populate
             */
            function populateChecks(checks) {
                // Ensure we have enough check fields
                while ($('#id_source_' + (checks.length - 1)).length === 0 && checks.length > 1) {
                    $('#id_add_check_button').click();
                }

                checks.forEach(function(check, index) {
                    $('#id_not_' + index).prop('checked', check.not == 1);
                    $('#id_source_' + index).val(check.source).trigger('change');
                    $('#id_source_instance_first_' + index).prop('checked', check.source_instance_first == 1);
                    $('#id_source_instance_last_' + index).prop('checked', check.source_instance_last == 1);
                    $('#id_other_source_' + index).prop('checked', check.other_source == 1);
                    $('#id_check_type_' + index).val(check.check_type).trigger('change');

                    // Set target field based on check type and source
                    var targetFieldName = 'target_' + check.check_type + '_' + check.source;
                    $('#id_' + targetFieldName + '_' + index).val(check.target);

                    $('#id_comp_' + index).val(check.comp);
                    $('#id_value_' + index).val(check.value);
                    $('#id_content_comp_' + index).val(check.content_comp).trigger('change');
                    $('#id_content_count_' + index).val(check.content_count);

                    if (check.next_logic && index < checks.length - 1) {
                        $('#id_next_logic_' + index).val(check.next_logic);
                    }
                });
            }

            /**
             * Populate resolutions section
             * @param {Array} resolutions - Array of resolution objects to populate
             */
            function populateResolutions(resolutions) {
                // Ensure we have enough resolution fields
                while ($('#id_res_scope_' + (resolutions.length - 1)).length === 0 && resolutions.length > 1) {
                    $('#id_add_res_button').click();
                }

                resolutions.forEach(function(resolution, index) {
                    $('#id_res_scope_' + index).val(resolution.scope).trigger('change');
                    $('#id_res_other_target_' + index).prop('checked', resolution.other_target == 1);
                    $('#id_res_type_' + index).val(resolution.type).trigger('change');

                    if (resolution.type === 'hint') {
                        $('#id_res_hint_' + index).val(resolution.hint_message);
                    } else if (resolution.type === 'show') {
                        $('#id_res_show_' + index).val(resolution.show_message);
                    } else if (resolution.type === 'action') {
                        $('#id_res_actiontype_' + index).val(resolution.actiontype).trigger('change');

                        if (resolution.actiontype === 'changesetting') {
                            var settingFieldName = 'res_setting_' + resolution.scope;
                            $('#id_' + settingFieldName + '_' + index).val(resolution.settingorcontent);
                            $('#id_res_value_' + index).val(resolution.value);
                        } else if (resolution.actiontype === 'addcontent') {
                            var contentFieldName = 'res_addcontent_' + resolution.scope;
                            $('#id_' + contentFieldName + '_' + index).val(resolution.content_type);
                        }
                    }
                });
            }

            /**
             * Clear the form
             */
            function clearForm() {
                // Clear basic fields
                $('#id_rule_name').val('');
                $('#id_rule_description').val('');
                $('#id_preconditions option').prop('selected', false);
                $('#id_existing_group').val('');
                $('#id_new_group_name').val('');

                // Clear all check and resolution fields
                $('[id^="id_"][id*="_"]').each(function() {
                    if ($(this).is(':checkbox')) {
                        $(this).prop('checked', false);
                    } else if ($(this).is('select') || $(this).is('input[type="text"]') || $(this).is('textarea')) {
                        $(this).val('');
                    }
                });
            }

            /**
             * Handle source field dependencies - first check determines all others
             */
            function updateSourceFields() {
                var firstSource = $("#id_source_0").val();
                var firstSourceText = $("#id_source_0 option:selected").text();

                // Update all subsequent source fields
                var sourceSelector = "[id^=id_source_]:not(#id_source_0):not([id^=id_source_instance_])";
                $(sourceSelector).each(function() {
                    $(this).val(firstSource);
                    $(this).prop("disabled", true);
                });

                // Update the "Other source" checkbox labels
                $("[id^=id_other_source_]:not(#id_other_source_0)").each(function() {
                    var checkboxId = $(this).attr("id");
                    var label = $("label[for='" + checkboxId + "']");
                    if (firstSourceText) {
                        label.text("Other " + firstSourceText.toLowerCase() + "(s)");
                    }
                });

                // Hide the "Other source" checkbox for the first check
                $("#id_other_source_0").closest(".fitem").hide();

                // Update resolution scopes to match the first check source and disable them
                $("[id^=id_res_scope_]").each(function() {
                    if ($(this).val() !== firstSource) {
                        $(this).val(firstSource);
                    }
                    $(this).prop("disabled", true);
                });

                // Update the "Other target" checkbox labels for resolutions
                $("[id^=id_res_other_target_]").each(function() {
                    var checkboxId = $(this).attr("id");
                    var label = $("label[for='" + checkboxId + "']");
                    if (firstSourceText && label.length) {
                        var newText = "Other " + firstSourceText.toLowerCase() + "(s)";
                        if (label.text() !== newText) {
                            label.text(newText);
                        }
                    }
                });
            }

            /**
             * Function to handle resolution field visibility
             */
            function updateResolutionFieldVisibility() {
                // For each resolution, show/hide the appropriate selectors
                for (var i = 0; i < 3; i++) {
                    var resTypeSelect = $("#id_res_type_" + i);
                    var resActionTypeSelect = $("#id_res_actiontype_" + i);
                    var resScopeSelect = $("#id_res_scope_" + i);

                    if (!resTypeSelect.length || !resActionTypeSelect.length || !resScopeSelect.length) {
                        continue;
                    }

                    var resType = resTypeSelect.val();
                    var actionType = resActionTypeSelect.val();
                    var scope = resScopeSelect.val();

                    // Hide all setting and content type selectors for this resolution
                    var selectorPattern = "[id^=id_res_setting_][id$=_" + i + "], " +
                        "[id^=id_res_addcontent_][id$=_" + i + "]";
                    $(selectorPattern).each(function() {
                        $(this).closest(".fitem").hide();
                        $(this).prop("disabled", true); // Disable when hidden
                    });

                    // Show appropriate selector based on action type and scope
                    if (resType === "action" && scope) {
                        if (actionType === "changesetting") {
                            var settingSelector = $("#id_res_setting_" + scope + "_" + i);
                            if (settingSelector.length) {
                                settingSelector.closest(".fitem").show();
                                settingSelector.prop("disabled", false); // Enable when shown
                            }
                        } else if (actionType === "addcontent") {
                            var contentSelector = $("#id_res_addcontent_" + scope + "_" + i);
                            if (contentSelector.length) {
                                contentSelector.closest(".fitem").show();
                                contentSelector.prop("disabled", false); // Enable when shown
                            }
                        }
                    }
                }
            }

            /**
             * Update logical operator visibility for form elements
             */
            function updateLogicalOperatorVisibility() {
                // Always hide the first logical operator and first delete buttons
                $("#fitem_id_next_logic_0").hide();
                $("#fitem_id_delete_check_button_0").hide();
                $("#fitem_id_delete_resolution_button_0").hide();

                // Hide source instance checkboxes for non-first checks
                var firstInstanceSelector = "[id^=id_source_instance_first_]" +
                    ":not([id^=id_source_instance_first_0])";
                $(firstInstanceSelector).each(function() {
                    $(this).closest(".fitem").hide();
                });

                var lastInstanceSelector = "[id^=id_source_instance_last_]" +
                    ":not([id^=id_source_instance_last_0])";
                $(lastInstanceSelector).each(function() {
                    $(this).closest(".fitem").hide();
                });

                // Manage add button visibility
                if ($("#fitem_id_source_2").length) {
                    $("#id_add_check_button").hide();
                } else {
                    $("#id_add_check_button").show();
                }
                if ($("#fitem_id_res_scope_2").length) {
                    $("#id_add_res_button").hide();
                } else {
                    $("#id_add_res_button").show();
                }

                for (var i = 1; i < 4; i++) {
                    var logicElement = $("#fitem_id_next_logic_" + i);
                    var deleteCheckElement = $("#fitem_id_delete_check_button_" + i);
                    var deleteResElement = $("#fitem_id_delete_resolution_button_" + i);

                    if (logicElement.length && $("#fitem_id_source_" + i).length) {
                        logicElement.show();
                        // Highlight the logical operator
                        logicElement.find("select").addClass("logic-operator-highlight");
                    }
                    if (deleteCheckElement.length && $("#fitem_id_source_" + i).length) {
                        deleteCheckElement.show();
                    }
                    if (deleteResElement.length && $("#fitem_id_res_scope_" + i).length) {
                        deleteResElement.show();
                    }
                }
            }

            // Initial setup with delay to ensure DOM is ready
            setTimeout(function() {
                updateSourceFields();
                updateResolutionFieldVisibility();
                initRuleLoader(); // Initialize rule loading functionality
                initCollapsibleRules(); // Initialize collapsible rules section
            }, 100);

            // Listen for changes in the first source field only
            $(document).off("change", "#id_source_0").on("change", "#id_source_0", function() {
                setTimeout(function() {
                    updateSourceFields();
                    updateResolutionFieldVisibility();
                }, 50);
            });

            // Add event listeners for resolution type changes
            var resolutionChangeSelector = "[id^=id_res_type_], [id^=id_res_actiontype_], [id^=id_res_scope_]";
            $(document).on("change", resolutionChangeSelector, function() {
                setTimeout(updateResolutionFieldVisibility, 50);
            });

            // Initial resolution field visibility setup
            setTimeout(updateResolutionFieldVisibility, 150);

            // When checks are added/removed, ensure first logical operator stays hidden
            updateLogicalOperatorVisibility();

            // Monitor for changes to the form structure
            var buttonSelector = "[id*=add_check_button], [id*=delete_check_button], " +
                "[id*=add_resolution_button], [id*=delete_resolution_button]";
            $(document).on("click", buttonSelector, function() {
                setTimeout(function() {
                    updateLogicalOperatorVisibility();
                    updateResolutionFieldVisibility();
                }, 100);
            });
        });
    }

    return {
        init: init
    };
});