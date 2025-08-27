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

                    // Refresh the page with the rule ID parameter to load fresh data
                    var newUrl = new URL(window.location);
                    newUrl.searchParams.set('id', ruleId);
                    window.location.href = newUrl.toString();
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

                    // Handle checks and resolutions with proper dependency order
                    if (data.checks && data.checks.length > 0) {
                        populateChecksWithDependencies(data.checks);
                    }

                    if (data.resolutions && data.resolutions.length > 0) {
                        populateResolutionsWithDependencies(data.resolutions);
                    }

                } catch (error) {
                    window.console.error('Error populating form:', error);
                    alert('Error populating form with rule data.');
                }
            }

            /**
             * Populate checks with proper dependency handling
             * @param {Array} checks - Array of check objects to populate
             */
            function populateChecksWithDependencies(checks) {
                // Ensure we have enough check fields
                while ($('#id_source_' + (checks.length - 1)).length === 0 && checks.length > 1) {
                    $('#id_add_check_button').click();
                }

                // Populate checks in sequence with proper delays
                populateCheckSequentially(checks, 0);
            }

            /**
             * Populate checks one by one with proper dependency handling
             * @param {Array} checks - Array of check objects
             * @param {number} index - Current check index to populate
             */
            function populateCheckSequentially(checks, index) {
                if (index >= checks.length) {
                    // All checks populated, now handle resolutions
                    return;
                }

                var check = checks[index];
                
                // Step 1: Set basic check properties
                $('#id_not_' + index).prop('checked', check.not == 1);
                $('#id_source_instance_first_' + index).prop('checked', check.source_instance_first == 1);
                $('#id_source_instance_last_' + index).prop('checked', check.source_instance_last == 1);
                $('#id_other_source_' + index).prop('checked', check.other_source == 1);

                // Step 2: Set source and trigger change (this updates dependent fields)
                $('#id_source_' + index).val(check.source).trigger('change');
                
                // Step 3: Wait for source change to complete, then set check type
                setTimeout(function() {
                    $('#id_check_type_' + index).val(check.check_type).trigger('change');
                    
                    // Step 4: Wait for check type change, then set target field
                    setTimeout(function() {
                        // Set target field based on check type and source
                        var targetFieldName = 'target_' + check.check_type + '_' + check.source;
                        var targetField = $('#id_' + targetFieldName + '_' + index);
                        if (targetField.length) {
                            targetField.val(check.target);
                        }

                        // Step 5: Set remaining fields
                        $('#id_comp_' + index).val(check.comp);
                        $('#id_value_' + index).val(check.value);
                        $('#id_content_comp_' + index).val(check.content_comp).trigger('change');
                        $('#id_content_count_' + index).val(check.content_count);

                        if (check.next_logic && index < checks.length - 1) {
                            $('#id_next_logic_' + index).val(check.next_logic);
                        }

                        // Step 6: Update form state after this check is complete
                        updateSourceFields();
                        updateLogicalOperatorVisibility();

                        // Step 7: Move to next check
                        setTimeout(function() {
                            populateCheckSequentially(checks, index + 1);
                        }, 100);

                    }, 100); // Wait for check type change
                }, 100); // Wait for source change
            }

            /**
             * Populate resolutions with proper dependency handling
             * @param {Array} resolutions - Array of resolution objects to populate
             */
            function populateResolutionsWithDependencies(resolutions) {
                // Ensure we have enough resolution fields
                while ($('#id_res_scope_' + (resolutions.length - 1)).length === 0 && resolutions.length > 1) {
                    $('#id_add_res_button').click();
                }

                // Populate resolutions in sequence with proper delays
                populateResolutionSequentially(resolutions, 0);
            }

            /**
             * Populate resolutions one by one with proper dependency handling
             * @param {Array} resolutions - Array of resolution objects
             * @param {number} index - Current resolution index to populate
             */
            function populateResolutionSequentially(resolutions, index) {
                if (index >= resolutions.length) {
                    // All resolutions populated, final form update
                    setTimeout(function() {
                        updateResolutionFieldVisibility();
                        updateLogicalOperatorVisibility();
                    }, 100);
                    return;
                }

                var resolution = resolutions[index];
                
                // Step 1: Set basic resolution properties
                $('#id_res_other_target_' + index).prop('checked', resolution.other_target == 1);

                // Step 2: Set scope and trigger change
                $('#id_res_scope_' + index).val(resolution.scope).trigger('change');
                
                // Step 3: Wait for scope change, then set resolution type
                setTimeout(function() {
                    $('#id_res_type_' + index).val(resolution.type).trigger('change');
                    
                    // Step 4: Wait for type change, then handle specific type fields
                    setTimeout(function() {
                        if (resolution.type === 'hint') {
                            $('#id_res_hint_' + index).val(resolution.hint_message);
                        } else if (resolution.type === 'show') {
                            $('#id_res_show_' + index).val(resolution.show_message);
                        } else if (resolution.type === 'action') {
                            $('#id_res_actiontype_' + index).val(resolution.actiontype).trigger('change');
                            
                            // Step 5: Wait for action type change, then set specific action fields
                            setTimeout(function() {
                                if (resolution.actiontype === 'changesetting') {
                                    var settingFieldName = 'res_setting_' + resolution.scope;
                                    var settingField = $('#id_' + settingFieldName + '_' + index);
                                    if (settingField.length) {
                                        settingField.val(resolution.settingorcontent);
                                    }
                                    $('#id_res_value_' + index).val(resolution.value);
                                } else if (resolution.actiontype === 'addcontent') {
                                    var contentFieldName = 'res_addcontent_' + resolution.scope;
                                    var contentField = $('#id_' + contentFieldName + '_' + index);
                                    if (contentField.length) {
                                        contentField.val(resolution.content_type);
                                    }
                                }

                                // Step 6: Move to next resolution
                                setTimeout(function() {
                                    populateResolutionSequentially(resolutions, index + 1);
                                }, 100);

                            }, 100); // Wait for action type change
                        } else {
                            // For non-action types, move to next resolution immediately
                            setTimeout(function() {
                                populateResolutionSequentially(resolutions, index + 1);
                            }, 100);
                        }
                    }, 100); // Wait for type change
                }, 100); // Wait for scope change
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