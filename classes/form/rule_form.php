<?php

namespace block_course_audit\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class rule_form extends \moodleform {

    protected function definition() {
        $mform = $this->_form;

        // Note: For a dynamic form like this, many of these options would be populated by JS
        // based on selections in other fields. For this initial PHP version, we will use
        // static lists and text fields as placeholders.

        // --- Static options for dropdowns (placeholders) ---
        $scope_options = ['quiz' => 'Quiz', 'lesson' => 'Lesson', 'folder' => 'Folder', 'book' => 'Book']; // From TODO Task 7
        $source_options = ['course' => 'Course', 'section' => 'Section', 'quiz' => 'Quiz', 'assign' => 'Assignment', 'quiz_question' => 'Quiz Question']; // From TODO Tasks 1 & 7
        $condition_type_options = ['setting' => get_string('has_setting', 'block_course_audit'), 'content' => get_string('has_content', 'block_course_audit')];
        $compare_options = [
            'eq' => get_string('equals', 'block_course_audit'),
            'neq' => get_string('notequals', 'block_course_audit'),
            'contains' => get_string('contains', 'block_course_audit'),
            'ncontains' => get_string('doesnotcontain', 'block_course_audit'),
            'regex' => get_string('regexmatches', 'block_course_audit'),
            'gt' => get_string('greaterthan', 'block_course_audit'),
            'lt' => get_string('lessthan', 'block_course_audit'),
            'isempty' => get_string('isempty', 'block_course_audit'),
            'isnotempty' => get_string('isnotempty', 'block_course_audit'),
        ];
        $resolution_type_options = ['hint' => get_string('hint', 'block_course_audit'), 'action' => get_string('action', 'block_course_audit')];
        $action_type_options = ['changesetting' => get_string('changesetting', 'block_course_audit'), 'addcontent' => get_string('addcontent', 'block_course_audit')];

        // --- Rule Details ---
        $mform->addElement('header', 'ruledetails', get_string('ruledetails', 'block_course_audit'));

        $mform->addElement('text', 'rule_name', get_string('rulename', 'block_course_audit'), ['size' => 50]);
        $mform->setType('rule_name', PARAM_TEXT);
        $mform->addRule('rule_name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'rule_description', get_string('ruledescription', 'block_course_audit'), 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('rule_description', PARAM_TEXT);

        // --- Checks ---
        $mform->addElement('header', 'checksheader', get_string('checks', 'block_course_audit'));

        // This group defines the fields for a single check.
        $check_elements = [
            $mform->createElement('select', 'scope', get_string('scope', 'block_course_audit'), $scope_options),
            $mform->createElement('checkbox', 'not', '', get_string('not', 'block_course_audit')),
            $mform->createElement('select', 'source', get_string('source', 'block_course_audit'), $source_options),
            $mform->createElement('select', 'check_type', get_string('conditiontype', 'block_course_audit'), $condition_type_options),
            $mform->createElement('text', 'target', get_string('target', 'block_course_audit'), ['size' => 30]), // Note: Should be a dynamic select based on Source/Check Type.
            $mform->createElement('select', 'comp', get_string('compareoperator', 'block_course_audit'), $compare_options),
            $mform->createElement('text', 'value', get_string('valuetocompare', 'block_course_audit'), ['size' => 30]),
            $mform->createElement('text', 'value_type', get_string('valuetype', 'block_course_audit'), ['size' => 10, 'disabled' => true]), // Note: Should be updated dynamically based on Target.
        ];
        
        // This group defines the logical operator between checks.
        $logic_operator_group = [
            $mform->createElement('select', 'next_logic', get_string('logicaloperator', 'block_course_audit'), ['AND' => 'AND', 'OR' => 'OR'])
        ];

        // Make the check elements repeatable.
        $this->repeat_elements(
            $check_elements,
            5, // Default number of repeats
            [],
            'add_check_button',
            get_string('addcheck', 'block_course_audit'),
            1, // Minimum repeats
            null,
            $logic_operator_group // This is inserted between repeated elements.
        );

        $mform->setType('scope', PARAM_ALPHANUM);
        $mform->setType('not', PARAM_BOOL);
        $mform->setType('source', PARAM_ALPHANUM);
        $mform->setType('check_type', PARAM_ALPHA);
        $mform->setType('target', PARAM_TEXT);
        $mform->setType('comp', PARAM_ALPHA);
        $mform->setType('value', PARAM_TEXT);
        $mform->setType('value_type', PARAM_ALPHA);
        $mform->setType('next_logic', PARAM_ALPHA);

        // --- Resolutions ---
        $mform->addElement('header', 'resolutionsheader', get_string('resolutions', 'block_course_audit'));
        
        // This group defines the fields for a single resolution.
        $resolution_elements = [
            $mform->createElement('select', 'res_scope', get_string('resolutioncontext', 'block_course_audit'), $source_options), // Note: Should also have "same_as..." options from checks.
            $mform->createElement('select', 'res_type', get_string('resolutiontype', 'block_course_audit'), $resolution_type_options),
            $mform->createElement('textarea', 'res_hint', get_string('hintmessage', 'block_course_audit'), 'rows="3" cols="50"'),
            $mform->createElement('select', 'res_actiontype', get_string('actiontype', 'block_course_audit'), $action_type_options),
            $mform->createElement('text', 'res_settingorcontent', get_string('settingorcontent', 'block_course_audit'), ['size' => 30]), // Note: Should be a dynamic select.
            $mform->createElement('text', 'res_value', get_string('newsettingvalue', 'block_course_audit'), ['size' => 30]),
        ];
        
        // Make the resolution elements repeatable.
        $this->repeat_elements(
            $resolution_elements,
            5,
            [],
            'add_resolution_button',
            get_string('addresolution', 'block_course_audit'),
            1
        );

        $mform->setType('res_scope', PARAM_ALPHANUM);
        $mform->setType('res_type', PARAM_ALPHA);
        $mform->setType('res_hint', PARAM_RAW);
        $mform->setType('res_actiontype', PARAM_ALPHA);
        $mform->setType('res_settingorcontent', PARAM_TEXT);
        $mform->setType('res_value', PARAM_TEXT);

        // --- JavaScript Dependencies ---
        // Register buttons so they don't submit the form.
        $mform->registerNoSubmitButton('add_check_button');
        $mform->registerNoSubmitButton('add_resolution_button');
        
        // Use disabledIf for basic show/hide logic. More complex interactions will need custom JS.
        $mform->disabledIf('res_hint', 'res_type', 'neq', 'hint');
        $mform->disabledIf('res_actiontype', 'res_type', 'neq', 'action');
        $mform->disabledIf('res_settingorcontent', 'res_type', 'neq', 'action');
        $mform->disabledIf('res_value', 'res_type', 'neq', 'action');

        // --- Action Buttons ---
        $this->add_action_buttons();
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Add custom validation here if needed.
        return $errors;
    }
} 