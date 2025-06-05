define(['jquery', 'core/log'], function($, log) {
    'use strict';

    var SELECTORS = {
        RULE_SET_ID: 'select[name="rulesetid"]',
        NEW_RULE_SET_NAME: 'input[name="newrulesetname"]',
        NEW_RULE_SET_DESCRIPTION: 'textarea[name="newrulesetdescription"]',
        // Condition Segment Selectors (base for cloning)
        SEGMENT_WRAPPER: '.conditionsegment-wrapper',
        TARGET_TYPE: 'select[name^="chain_"][name$="_target_type"]', // Starts with chain_, ends with _target_type
        TARGET_IDENTIFIER: 'input[name^="chain_"][name$="_target_identifier"]',
        CHECK_TYPE: 'select[name^="chain_"][name$="_check_type"]',
        CONTENT_CHILD_TYPE: 'select[name^="chain_"][name$="_content_child_type"]',
        CONTENT_CHILD_IDENTIFIER: 'input[name^="chain_"][name$="_content_child_identifier"]',
        SETTING_NAME: 'input[name^="chain_"][name$="_setting_name"]',
        SETTING_OPERATOR: 'select[name^="chain_"][name$="_setting_operator"]',
        SETTING_EXPECTED_VALUE: 'input[name^="chain_"][name$="_setting_expected_value"]',
        // Action Selectors (base for cloning)
        ACTION_WRAPPER: '.action-wrapper',
        ACTION_TYPE: 'select[name^="action_"][name$="_type"]',
        ACTION_CHANGE_SETTING_NAME: 'input[name^="action_"][name$="_change_setting_name"]',
        ACTION_CHANGE_SETTING_NEW_VALUE: 'input[name^="action_"][name$="_change_setting_new_value"]',
        ACTION_ADD_CONTENT_CHILD_TYPE: 'select[name^="action_"][name$="_add_content_child_type"]',
        ACTION_ADD_CONTENT_CHILD_IDENTIFIER: 'input[name^="action_"][name$="_add_content_child_identifier"]',
        ACTION_ADD_CONTENT_INITIAL_SETTINGS: 'textarea[name^="action_"][name$="_add_content_initial_settings"]',
        // Buttons
        ADD_SEGMENT_BUTTON_PREFIX: 'button[name^="add_segment_to_chain_"]', // e.g. add_segment_to_chain_0
        ADD_CHAIN_BUTTON: 'button[name="add_condition_chain"]',
        ADD_ACTION_BUTTON: 'button[name="add_failure_action"]'
    };

    // Store parent form elements for repeated searches
    var form = null;

    const getField = function(selector, baseElement) {
        return baseElement ? baseElement.find(selector) : form.find(selector);
    };

    const getFieldRow = function(selector, baseElement) {
        return getField(selector, baseElement).closest('.fitem, .form-group'); // Moodle forms use fitem, Boost uses form-group
    };

    const toggleRuleSetFields = function() {
        var ruleSetId = getField(SELECTORS.RULE_SET_ID).val();
        var $newNameField = getField(SELECTORS.NEW_RULE_SET_NAME);
        var $newDescField = getField(SELECTORS.NEW_RULE_SET_DESCRIPTION);

        if (ruleSetId === '0') {
            $newNameField.prop('disabled', false);
            $newDescField.prop('disabled', false);
            getFieldRow(SELECTORS.NEW_RULE_SET_NAME).show();
            getFieldRow(SELECTORS.NEW_RULE_SET_DESCRIPTION).show();
        } else {
            $newNameField.prop('disabled', true);
            $newDescField.prop('disabled', true);
            getFieldRow(SELECTORS.NEW_RULE_SET_NAME).hide();
            getFieldRow(SELECTORS.NEW_RULE_SET_DESCRIPTION).hide();
        }
    };

    const toggleSegmentFields = function(segmentWrapper) {
        var $segment = $(segmentWrapper);
        var targetType = getField(SELECTORS.TARGET_TYPE, $segment).val();
        var checkType = getField(SELECTORS.CHECK_TYPE, $segment).val();

        // Target Identifier visibility (Module type/name)
        var $targetIdentifierRow = getFieldRow(SELECTORS.TARGET_IDENTIFIER, $segment);
        if (targetType === 'MODULE' || targetType === 'SUB_ELEMENT') { // Assuming SUB_ELEMENT might also need it
            $targetIdentifierRow.show();
            // TODO: Change label for target_identifier based on targetType (e.g., "Module Type", "Sub-element Type")
        } else {
            $targetIdentifierRow.hide();
        }

        // HAS_CONTENT / NOT_HAS_CONTENT fields
        var $contentChildTypeRow = getFieldRow(SELECTORS.CONTENT_CHILD_TYPE, $segment);
        var $contentChildIdentifierRow = getFieldRow(SELECTORS.CONTENT_CHILD_IDENTIFIER, $segment);

        // HAS_SETTING / NOT_HAS_SETTING fields
        var $settingNameRow = getFieldRow(SELECTORS.SETTING_NAME, $segment);
        var $settingOperatorRow = getFieldRow(SELECTORS.SETTING_OPERATOR, $segment);
        var $settingExpectedValueRow = getFieldRow(SELECTORS.SETTING_EXPECTED_VALUE, $segment);

        if (checkType === 'HAS_CONTENT' || checkType === 'NOT_HAS_CONTENT') {
            $contentChildTypeRow.show();
            // TODO: Potentially show/hide $contentChildIdentifierRow based on $contentChildTypeRow selection
            $contentChildIdentifierRow.show(); // Show by default for now

            $settingNameRow.hide();
            $settingOperatorRow.hide();
            $settingExpectedValueRow.hide();
        } else if (checkType === 'HAS_SETTING' || checkType === 'NOT_HAS_SETTING') {
            $contentChildTypeRow.hide();
            $contentChildIdentifierRow.hide();

            $settingNameRow.show();
            $settingOperatorRow.show();
            $settingExpectedValueRow.show();
            // TODO: Dynamically populate setting_name dropdown based on target_type
            // TODO: Change setting_expected_value input type based on setting_name
        } else { // Neither (or error), hide all optional
            $contentChildTypeRow.hide();
            $contentChildIdentifierRow.hide();
            $settingNameRow.hide();
            $settingOperatorRow.hide();
            $settingExpectedValueRow.hide();
        }
    };

    const toggleActionFields = function(actionWrapper) {
        var $action = $(actionWrapper);
        var actionType = getField(SELECTORS.ACTION_TYPE, $action).val();

        var $changeSettingNameRow = getFieldRow(SELECTORS.ACTION_CHANGE_SETTING_NAME, $action);
        var $changeSettingNewValueRow = getFieldRow(SELECTORS.ACTION_CHANGE_SETTING_NEW_VALUE, $action);

        var $addContentChildTypeRow = getFieldRow(SELECTORS.ACTION_ADD_CONTENT_CHILD_TYPE, $action);
        var $addContentChildIdentifierRow = getFieldRow(SELECTORS.ACTION_ADD_CONTENT_CHILD_IDENTIFIER, $action);
        var $addContentInitialSettingsRow = getFieldRow(SELECTORS.ACTION_ADD_CONTENT_INITIAL_SETTINGS, $action);

        if (actionType === 'CHANGE_SETTING') {
            $changeSettingNameRow.show();
            $changeSettingNewValueRow.show();
            // TODO: Dynamically populate action_change_setting_name based on the rule's target context
            $addContentChildTypeRow.hide();
            $addContentChildIdentifierRow.hide();
            $addContentInitialSettingsRow.hide();
        } else if (actionType === 'ADD_CONTENT') {
            $changeSettingNameRow.hide();
            $changeSettingNewValueRow.hide();

            $addContentChildTypeRow.show();
            // TODO: Potentially show/hide $addContentChildIdentifierRow based on $addContentChildTypeRow selection
            $addContentChildIdentifierRow.show(); // Show for now
            $addContentInitialSettingsRow.show();
        } else { // Neither selected, hide all
            $changeSettingNameRow.hide();
            $changeSettingNewValueRow.hide();
            $addContentChildTypeRow.hide();
            $addContentChildIdentifierRow.hide();
            $addContentInitialSettingsRow.hide();
        }
    };

    const addConditionSegment = function(chainId) {
        log.debug('Attempting to add condition segment to chain: ' + chainId);
        var $lastSegment = form.find('#conditionchain_' + chainId + '_wrapper ' + SELECTORS.SEGMENT_WRAPPER).last();
        if (!$lastSegment.length) {
            log.error('Could not find last segment for chain ' + chainId);
            return;
        }
        var $newSegment = $lastSegment.clone();
        // TODO: IMPORTANT - Update all IDs and names in $newSegment to be unique.
        // This requires a robust way to increment the segment index in names like 'chain_0_segment_0_target_type'.
        // For example, 'chain_0_segment_0_target_type' -> 'chain_0_segment_1_target_type'
        // And update wrapper IDs like 'conditionchain_0_segment_0_wrapper'
        // Reset field values in the new segment.
        $newSegment.find('input[type="text"], textarea').val('');
        $newSegment.find('select').prop('selectedIndex', 0);

        $newSegment.insertAfter($lastSegment);
        // Re-initialize toggles for the new segment
        toggleSegmentFields($newSegment);
        // Re-attach event listeners if they are segment-specific (like onchange for target_type)
        // For simplicity, we're using delegated events where possible.
        log.debug('New segment cloned (NEEDS ID/NAME UPDATES)');
    };

    const addConditionChain = function() {
        log.debug('Attempting to add condition chain');
        var $lastChain = form.find('.conditionchain-wrapper').last();
         if (!$lastChain.length) {
            log.error('Could not find last chain wrapper');
            return;
        }
        var $newChain = $lastChain.clone();
        // TODO: IMPORTANT - Update all IDs and names in $newChain.
        // This includes chain wrapper IDs, segment wrapper IDs within, and all form field names/IDs.
        // e.g. 'conditionchain_0_wrapper' -> 'conditionchain_1_wrapper'
        // 'chain_0_segment_0_target_type' -> 'chain_1_segment_0_target_type'
        // 'chain_0_logical_operator_to_next' -> 'chain_1_logical_operator_to_next'
        // Reset field values.
        $newChain.find('input[type="text"], textarea').val('');
        $newChain.find('select').prop('selectedIndex', 0);
        // Remove extra segments if the original had multiple, start with one.
        $newChain.find(SELECTORS.SEGMENT_WRAPPER).not(':first').remove();

        $newChain.insertAfter($lastChain);
        // Re-initialize toggles for the new chain's first segment
        toggleSegmentFields($newChain.find(SELECTORS.SEGMENT_WRAPPER).first());
        log.debug('New chain cloned (NEEDS ID/NAME UPDATES)');
    };

    const addFailureAction = function() {
        log.debug('Attempting to add failure action');
        var $lastAction = form.find(SELECTORS.ACTION_WRAPPER).last();
        if (!$lastAction.length) {
            log.error('Could not find last action wrapper');
            return;
        }
        var $newAction = $lastAction.clone();
        // TODO: IMPORTANT - Update all IDs and names in $newAction.
        // e.g. 'action_0_wrapper' -> 'action_1_wrapper'
        // 'action_0_button_label' -> 'action_1_button_label'
        // Reset field values.
        $newAction.find('input[type="text"], textarea').val('');
        $newAction.find('select').prop('selectedIndex', 0);

        $newAction.insertAfter($lastAction);
        toggleActionFields($newAction);
        log.debug('New action cloned (NEEDS ID/NAME UPDATES)');
    };


    return {
        init: function() {
            log.debug('block_course_audit/rule_form: Initializing');
            // Find the form element, assuming it's the closest form to the rule name input
            // This could be made more robust if the form has a specific ID
            form = $('input[name="rulename"]').closest('form');
            if (!form.length) {
                log.error('block_course_audit/rule_form: Could not find the Moodle form.');
                return;
            }

            // --- Initial setup ---
            toggleRuleSetFields();
            // Initial toggle for all existing segments on the page
            form.find(SELECTORS.SEGMENT_WRAPPER).each(function() {
                toggleSegmentFields(this);
            });
            // Initial toggle for all existing actions on the page
            form.find(SELECTORS.ACTION_WRAPPER).each(function() {
                toggleActionFields(this);
            });

            // --- Event Listeners ---
            // Rule Set
            form.on('change', SELECTORS.RULE_SET_ID, toggleRuleSetFields);

            // Delegated event listeners for condition segments (works for cloned segments too)
            form.on('change', SELECTORS.TARGET_TYPE, function() {
                toggleSegmentFields($(this).closest(SELECTORS.SEGMENT_WRAPPER));
            });
            form.on('change', SELECTORS.CHECK_TYPE, function() {
                toggleSegmentFields($(this).closest(SELECTORS.SEGMENT_WRAPPER));
            });

            // Delegated event listeners for actions (works for cloned actions too)
            form.on('change', SELECTORS.ACTION_TYPE, function() {
                toggleActionFields($(this).closest(SELECTORS.ACTION_WRAPPER));
            });

            // Add buttons (handle multiple segment buttons if chains are cloned)
            form.on('click', SELECTORS.ADD_SEGMENT_BUTTON_PREFIX + '_0', function() { // Attach specifically to chain 0 for now.
                // This needs to be more dynamic if chains are added.
                // We'd need to extract the chain index from the button's name/ID.
                var chainId = 0; // Example, needs to be dynamic for multiple chains
                addConditionSegment(chainId);
            });
            // More robust way if we have multiple "add segment" buttons:
            // form.on('click', 'button[name^="add_segment_to_chain_"]', function() {
            //     var name = $(this).attr('name');
            //     var chainId = name.replace('add_segment_to_chain_', '');
            //     addConditionSegment(chainId);
            // });


            form.on('click', SELECTORS.ADD_CHAIN_BUTTON, addConditionChain);
            form.on('click', SELECTORS.ADD_ACTION_BUTTON, addFailureAction);

            log.debug('block_course_audit/rule_form: Initialization complete.');
        }
    };
});