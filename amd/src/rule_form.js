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
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    'use strict';

    /**
     * Initialize the rule form functionality.
     */
    function init() {
        $(document).ready(function() {

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