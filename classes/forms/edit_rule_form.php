<?php
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
 * Form for creating and editing dynamic audit rules.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class block_course_audit_edit_rule_form extends moodleform {

    protected function definition() {
        global $DB, $COURSE; // $COURSE might be available if context is course

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'] ?? 0;

        // --- Rule Set Management --- //
        $mform->addElement('header', 'rulesetheader', get_string('ruleset', 'block_course_audit'));

        // TODO: Populate this from the database block_course_audit_rule_sets table.
        $rulesets = [0 => get_string('createnewruleset', 'block_course_audit')]; // Example
        // $dbsets = $DB->get_records_menu('block_course_audit_rule_sets', [], 'name ASC', 'id, name');
        // if ($dbsets) { $rulesets = $rulesets + $dbsets; }
        $mform->addElement('select', 'rulesetid', get_string('selectruleset', 'block_course_audit'), $rulesets);

        $mform->addElement('text', 'newrulesetname', get_string('rulesetname', 'block_course_audit'));
        $mform->setType('newrulesetname', PARAM_TEXT);
        $mform->disabledIf('newrulesetname', 'rulesetid', 'neq', 0);

        $mform->addElement('textarea', 'newrulesetdescription', get_string('rulesetdescription', 'block_course_audit'), 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('newrulesetdescription', PARAM_TEXT);
        $mform->disabledIf('newrulesetdescription', 'rulesetid', 'neq', 0);

        // --- Rule Details --- //
        $mform->addElement('header', 'ruledetailsheader', get_string('rulename', 'block_course_audit'));
        $mform->addElement('text', 'rulename', get_string('rulename', 'block_course_audit'));
        $mform->setType('rulename', PARAM_TEXT);
        $mform->addRule('rulename', null, 'required', null, 'client');

        // --- Condition Chains and Segments (Repeatable via JS) --- //
        $mform->addElement('header', 'conditionchainheader_0', get_string('conditiongroup', 'block_course_audit', 1));
        // Wrapper for the first condition chain.
        // JS will clone this entire group for additional chains.
        $mform->addElement('html', '<div id="conditionchain_0_wrapper" class="conditionchain-wrapper">');

        // Wrapper for the first segment in the first chain.
        // JS will clone this for additional segments within a chain.
        $mform->addElement('html', '<div id="conditionchain_0_segment_0_wrapper" class="conditionsegment-wrapper well mb-3">'); 

        $targetoptions = [
            'COURSE' => get_string('target_course', 'block_course_audit'),
            'SECTION' => get_string('target_section', 'block_course_audit'),
            'MODULE' => get_string('target_module', 'block_course_audit'),
            // 'SUB_ELEMENT' => get_string('target_subelement', 'block_course_audit'), // Requires more complex handling.
        ];
        $mform->addElement('select', 'chain_0_segment_0_target_type', get_string('target', 'block_course_audit'), $targetoptions);
        $mform->addRule('chain_0_segment_0_target_type', null, 'required', null, 'client');

        // TODO: Add conditional selects for module type / sub-element type via JS.
        $mform->addElement('text', 'chain_0_segment_0_target_identifier', get_string('moduletypeorname', 'block_course_audit'));
        $mform->setType('chain_0_segment_0_target_identifier', PARAM_PLUGIN);
        // This field would be shown/hidden and its label changed by JS based on target_type.

        $checktypes = [
            'HAS_CONTENT' => get_string('checktype_hascontent', 'block_course_audit'),
            'NOT_HAS_CONTENT' => get_string('checktype_nothascontent', 'block_course_audit'),
            'HAS_SETTING' => get_string('checktype_hassetting', 'block_course_audit'),
            'NOT_HAS_SETTING' => get_string('checktype_nothassetting', 'block_course_audit'),
        ];
        $mform->addElement('select', 'chain_0_segment_0_check_type', get_string('conditiontype', 'block_course_audit'), $checktypes);
        $mform->addRule('chain_0_segment_0_check_type', null, 'required', null, 'client');

        // Fields for HAS_CONTENT / NOT_HAS_CONTENT (shown/hidden by JS).
        $mform->addElement('select', 'chain_0_segment_0_content_child_type', get_string('hascontent_targetchild', 'block_course_audit'), $targetoptions); // Re-use target options for child types.
        $mform->addElement('text', 'chain_0_segment_0_content_child_identifier', get_string('moduletypeorname', 'block_course_audit'));
        $mform->setType('chain_0_segment_0_content_child_identifier', PARAM_PLUGIN);

        // Fields for HAS_SETTING / NOT_HAS_SETTING (shown/hidden by JS).
        // TODO: Setting name dropdown should be populated dynamically by JS based on target.
        $mform->addElement('text', 'chain_0_segment_0_setting_name', get_string('settingname', 'block_course_audit'));
        $mform->setType('chain_0_segment_0_setting_name', PARAM_ALPHANUMEXT); 

        $operators = [
            'EQUALS' => get_string('operator_equals', 'block_course_audit'),
            'NOT_EQUALS' => get_string('operator_notequals', 'block_course_audit'),
            'CONTAINS' => get_string('operator_contains', 'block_course_audit'),
            // Add all other operators here from your lang file.
            'IS_TRUE' => get_string('operator_istrue', 'block_course_audit'),
            'IS_FALSE' => get_string('operator_isfalse', 'block_course_audit'),
        ];
        $mform->addElement('select', 'chain_0_segment_0_setting_operator', get_string('comparisonoperator', 'block_course_audit'), $operators);
        $mform->addElement('text', 'chain_0_segment_0_setting_expected_value', get_string('expectedvalue', 'block_course_audit'));
        $mform->setType('chain_0_segment_0_setting_expected_value', PARAM_RAW); // Type might change based on setting_name (JS).

        // TODO: Add button here for JS to add more segments to this chain.
        // $mform->addElement('button', 'add_segment_to_chain_0', get_string('addconditionsegment', 'block_course_audit'));
        $mform->addElement('html', '</div>'); // End segment wrapper.

        $mform->addElement('html', '</div>'); // End chain wrapper.

        $logicaloperators = ['AND' => 'AND', 'OR' => 'OR'];
        $mform->addElement('select', 'chain_0_logical_operator_to_next', get_string('logicaloperator', 'block_course_audit'), $logicaloperators);

        // TODO: Add button here for JS to add more condition chains.
        // $mform->addElement('button', 'add_condition_chain', get_string('addconditionchain', 'block_course_audit'));

        // --- Failure Actions (Repeatable via JS) --- //
        $mform->addElement('header', 'failureactionsheader', get_string('failureactions', 'block_course_audit'));
        $mform->addElement('textarea', 'failurehinttext', get_string('failurehint', 'block_course_audit'), 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('failurehinttext', PARAM_TEXT);

        // Wrapper for the first action. JS will clone this.
        $mform->addElement('html', '<div id="action_0_wrapper" class="action-wrapper well mb-3">');

        $mform->addElement('text', 'action_0_button_label', get_string('actionbuttonlabel', 'block_course_audit'));
        $mform->setType('action_0_button_label', PARAM_TEXT);

        $actiontypes = [
            'CHANGE_SETTING' => get_string('actiontype_changesetting', 'block_course_audit'),
            'ADD_CONTENT' => get_string('actiontype_addcontent', 'block_course_audit'),
        ];
        $mform->addElement('select', 'action_0_type', get_string('actiontype', 'block_course_audit'), $actiontypes);

        // Fields for CHANGE_SETTING (shown/hidden by JS).
        $mform->addElement('text', 'action_0_change_setting_name', get_string('actionchangesetting', 'block_course_audit'));
        $mform->setType('action_0_change_setting_name', PARAM_ALPHANUMEXT);
        $mform->addElement('text', 'action_0_change_setting_new_value', get_string('actionnewvalue', 'block_course_audit'));
        $mform->setType('action_0_change_setting_new_value', PARAM_RAW);

        // Fields for ADD_CONTENT (shown/hidden by JS).
        $mform->addElement('select', 'action_0_add_content_child_type', get_string('actionaddcontenttype', 'block_course_audit'), $targetoptions); // Re-use target options for child types.
        $mform->addElement('text', 'action_0_add_content_child_identifier', get_string('moduletypeorname', 'block_course_audit'));
        $mform->setType('action_0_add_content_child_identifier', PARAM_PLUGIN);
        $mform->addElement('textarea', 'action_0_add_content_initial_settings', get_string('initialsettingsjson', 'block_course_audit'), 'wrap="virtual" rows="2" cols="50"');
        $mform->setType('action_0_add_content_initial_settings', PARAM_RAW);

        $mform->addElement('html', '</div>'); // End action wrapper.
        // TODO: Add button here for JS to add more actions.
        // $mform->addElement('button', 'add_failure_action', get_string('addaction', 'block_course_audit'));


        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // TODO: Add server-side validation, especially for new rule set name if selected.
        if (isset($data['rulesetid']) && $data['rulesetid'] == 0 && empty($data['newrulesetname'])) {
            // $errors['newrulesetname'] = get_string('required');
        }
        // Complex validation of dynamic parts might be tricky here without JS data structure.
        return $errors;
    }
} 