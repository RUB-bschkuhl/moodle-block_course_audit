<?php

namespace block_course_audit\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class rule_form extends \moodleform
{

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $ajaxformdata = null)
    {
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    protected function definition()
    {
        $mform = $this->_form;

        // --- Static options for dropdowns (placeholders) ---
        $source_options = ['course' => 'Course', 'section' => 'Section', 'quiz' => 'Quiz', 'assign' => 'Assignment', 'quiz_question' => 'Quiz Question']; // From TODO Tasks 1 & 7
        $condition_type_options = ['setting' => get_string('has_setting', 'block_course_audit'), 'content' => get_string('has_content', 'block_course_audit')];
        $compare_options = [
            'eq' => get_string('equals', 'block_course_audit'),
            'contains' => get_string('contains', 'block_course_audit'),
            'regex' => get_string('regexmatches', 'block_course_audit'),
            'gt' => get_string('greaterthan', 'block_course_audit'),
            'lt' => get_string('lessthan', 'block_course_audit'),
            'isempty' => get_string('isempty', 'block_course_audit'),
        ];

        // Content count comparison options (for content checks) - using symbols
        $content_compare_options = [
            'any' => get_string('any', 'block_course_audit'),
            'eq' => 'exactly',
            'gt' => 'more than',
            'lt' => 'less than',
            'gte' => 'at least',
            'lte' => 'at most',
        ];
        $resolution_type_options = [
            'hint' => get_string('hint', 'block_course_audit'), 
            'action' => get_string('action', 'block_course_audit'),
            'show' => get_string('show', 'block_course_audit')
        ];
        $action_type_options = [
            'changesetting' => get_string('changesetting', 'block_course_audit'), 
            'addcontent' => get_string('addcontent', 'block_course_audit')
        ];

        // Define what can be added to each target type
        $add_content_options = [
            'course' => [
                'section' => get_string('addsection', 'block_course_audit'),
                'assign' => get_string('addassignment', 'block_course_audit'),
                'quiz' => get_string('addquiz', 'block_course_audit'),
                'forum' => get_string('addforum', 'block_course_audit'),
                'wiki' => get_string('addwiki', 'block_course_audit'),
                'folder' => get_string('addfolder', 'block_course_audit'),
                'url' => get_string('addurl', 'block_course_audit'),
                'page' => get_string('addpage', 'block_course_audit'),
                'book' => get_string('addbook', 'block_course_audit')
            ],
            'section' => [
                'assign' => get_string('addassignment', 'block_course_audit'),
                'quiz' => get_string('addquiz', 'block_course_audit'),
                'forum' => get_string('addforum', 'block_course_audit'),
                'wiki' => get_string('addwiki', 'block_course_audit'),
                'folder' => get_string('addfolder', 'block_course_audit'),
                'url' => get_string('addurl', 'block_course_audit'),
                'page' => get_string('addpage', 'block_course_audit'),
                'book' => get_string('addbook', 'block_course_audit')
            ],
            'quiz' => [
                'question_multichoice' => get_string('addmultichoice', 'block_course_audit'),
                'question_truefalse' => get_string('addtruefalse', 'block_course_audit'),
                'question_essay' => get_string('addessay', 'block_course_audit'),
                'question_shortanswer' => get_string('addshortanswer', 'block_course_audit'),
                'question_numerical' => get_string('addnumerical', 'block_course_audit'),
                'question_match' => get_string('addmatching', 'block_course_audit'),
                'question_cloze' => get_string('addcloze', 'block_course_audit')
            ],
            'assign' => [], // Not a container, only hints allowed
            'forum' => [], // Not a container, only hints allowed  
            'wiki' => [], // Not a container, only hints allowed
        ];

        // --- Rule Details ---
        $mform->addElement('header', 'ruledetails', get_string('ruledetails', 'block_course_audit'));

        $mform->addElement('text', 'rule_name', get_string('rulename', 'block_course_audit'), ['size' => 50]);
        $mform->setType('rule_name', PARAM_TEXT);
        $mform->addRule('rule_name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'rule_description', get_string('ruledescription', 'block_course_audit'), 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('rule_description', PARAM_TEXT);

        // Get existing rules for preconditions
        global $DB;
        $existing_rules = $DB->get_records('block_course_audit_rule', null, 'rule_name ASC', 'id, rule_name');
        $rule_options = [];
        foreach ($existing_rules as $rule) {
            // Don't include the current rule being edited as a precondition option
            if (!$this->_customdata || !isset($this->_customdata['id']) || $rule->id != $this->_customdata['id']) {
                $rule_options[$rule->id] = format_string($rule->rule_name);
            }
        }

        $mform->addElement('select', 'preconditions', get_string('preconditions', 'block_course_audit'), $rule_options, ['multiple' => 'multiple', 'size' => 5, 'style' => 'min-width: 300px;']);
        $mform->addHelpButton('preconditions', 'preconditions', 'block_course_audit');
        $mform->setType('preconditions', PARAM_INT);

        // Get existing groups for group selection
        $existing_groups = $DB->get_records('block_course_audit_rule_coll', null, 'collection_name ASC', 'id, collection_name');
        $group_options = ['' => get_string('selectgroup', 'block_course_audit')];
        foreach ($existing_groups as $group) {
            $group_options[$group->id] = format_string($group->collection_name);
        }

        $mform->addElement('select', 'existing_group', get_string('existinggroup', 'block_course_audit'), $group_options);
        $mform->setType('existing_group', PARAM_INT);

        $mform->addElement('text', 'new_group_name', get_string('newgroupname', 'block_course_audit'), ['size' => 50]);
        $mform->setType('new_group_name', PARAM_TEXT);
        $mform->addHelpButton('new_group_name', 'newgroupname', 'block_course_audit');

        // --- Checks ---
        $mform->addElement('header', 'checksheader', get_string('checks', 'block_course_audit'));

        $target_setting_options = [
            'course' => [
                'fullname' => 'Full Name',
                'shortname' => 'Short Name',
                'visible' => 'Visibility',
                'startdate' => 'Start Date',
                'enddate' => 'End Date',
                'format' => 'Course Format',
                'maxbytes' => 'Max Upload Size',
                'showgrades' => 'Show Grades'
            ],
            'section' => [
                'name' => 'Section Name',
                'summary' => 'Section Summary',
                'visible' => 'Section Visibility'
            ],
            'quiz' => [
                'name' => 'Quiz Name',
                'intro' => 'Description',
                'timeopen' => 'Open Date',
                'timeclose' => 'Close Date',
                'timelimit' => 'Time Limit',
                'attempts' => 'Number of Attempts',
                'grademethod' => 'Grading Method'
            ],
            'assign' => [
                'name' => 'Assignment Name',
                'intro' => 'Description',
                'duedate' => 'Due Date',
                'cutoffdate' => 'Cut-off Date',
                'grade' => 'Maximum Grade'
            ]
        ];

        $target_content_options = [
            'course' => [
                'sections' => 'Sections',
                'activities' => 'Any Activities',
                'quiz' => 'Quiz Activities',
                'assign' => 'Assignment Activities',
                'forum' => 'Forum Activities',
                'wiki' => 'Wiki Activities',
                'folder' => 'Folder Activities',
                'url' => 'URL Activities',
                'file' => 'File Activities'
            ],
            'section' => [
                'activities' => 'Any Activities',
                'quiz' => 'Quiz Activities',
                'assign' => 'Assignment Activities',
                'forum' => 'Forum Activities',
                'wiki' => 'Wiki Activities',
                'folder' => 'Folder Activities',
                'url' => 'URL Activities',
                'file' => 'File Activities',
                'page' => 'Page Activities',
                'book' => 'Book Activities'
            ],
            'quiz' => [
                'questions' => 'Any Questions',
                'question_multichoice' => 'Multiple Choice Questions',
                'question_truefalse' => 'True/False Questions',
                'question_essay' => 'Essay Questions',
                'question_shortanswer' => 'Short Answer Questions',
                'question_numerical' => 'Numerical Questions',
                'question_match' => 'Matching Questions',
                'question_cloze' => 'Cloze Questions'
            ],
            'assign' => [
                'submissions' => 'Submissions',
                'rubric' => 'Rubric',
                'feedback' => 'Feedback Comments',
                'grades' => 'Grades'
            ]
        ];

        // Dynamically create target elements based on source options
        $check_elements = [
            $mform->createElement('html', '<div class="check-group-container">'),
            $mform->createElement('select', 'next_logic', get_string('logicaloperator', 'block_course_audit'), ['AND' => 'AND', 'OR' => 'OR']),
            $mform->createElement('checkbox', 'not', '', get_string('not', 'block_course_audit')),
            $mform->createElement('select', 'source', get_string('source', 'block_course_audit'), $source_options),
            $mform->createElement('checkbox', 'source_instance_first', '', get_string('firstinstance', 'block_course_audit')),
            $mform->createElement('checkbox', 'source_instance_last', '', get_string('lastinstance', 'block_course_audit')),
            $mform->createElement('checkbox', 'other_source', '', get_string('othersource', 'block_course_audit')),
            $mform->createElement('select', 'check_type', get_string('conditiontype', 'block_course_audit'), $condition_type_options),
        ];

        // Dynamically add target elements for each source type
        foreach ($target_setting_options as $source_type => $settings) {
            $check_elements[] = $mform->createElement('select', 'target_setting_' . $source_type, get_string('target_setting', 'block_course_audit'), ['' => 'Choose...'] + $settings);
        }

        foreach ($target_content_options as $source_type => $contents) {
            $check_elements[] = $mform->createElement('select', 'target_content_' . $source_type, get_string('target_content', 'block_course_audit'), ['' => 'Choose...'] + $contents);
        }

        // Add remaining elements
        $check_elements[] = $mform->createElement('select', 'comp', get_string('compareoperator', 'block_course_audit'), $compare_options);
        $check_elements[] = $mform->createElement('text', 'value', get_string('valuetocompare', 'block_course_audit'), ['size' => 30]);
        $check_elements[] = $mform->createElement('select', 'content_comp', 'With Count', $content_compare_options, ['class' => 'content-comp-inline']);
        $check_elements[] = $mform->createElement('text', 'content_count', '', ['size' => 10, 'class' => 'content-count-inline']);
        $check_elements[] = $mform->createElement('submit', 'delete_check_button', get_string('delete'), [], false);
        $check_elements[] = $mform->createElement('html', '</div>');

        // Make the check elements repeatable.
        // Initially show only 1 check, hide the rest until needed via JS
        $check_options = [
            'not' => ['default' => 0],
            'source' => ['default' => 'section'],
            'source_instance_first' => ['default' => 0],
            'source_instance_last' => ['default' => 0],
            'check_type' => ['default' => 'setting'],
            'comp' => ['default' => 'eq'],
            'value' => ['default' => ''],
            'content_comp' => ['default' => 'eq'],
            'content_count' => ['default' => '1'],
            'next_logic' => ['default' => 'AND'],
        ];

        // Dynamically add options for target fields
        foreach ($target_setting_options as $source_type => $settings) {
            $check_options['target_setting_' . $source_type] = ['default' => ''];
        }

        foreach ($target_content_options as $source_type => $contents) {
            $check_options['target_content_' . $source_type] = ['default' => ''];
        }

        $this->repeat_elements(
            $check_elements,
            1,
            $check_options,
            'hidden_check_no',
            'add_check_button',
            1,
            null,
            true,
            'delete_check_button'
        );

        // Add hidden value_type fields for each possible check (always set to 'string')
        for ($i = 0; $i < 10; $i++) { // Allow up to 10 checks
            $mform->addElement('hidden', 'value_type[' . $i . ']', 'string');
            $mform->setType('value_type[' . $i . ']', PARAM_ALPHA);
        }

        $mform->setType('next_logic', PARAM_ALPHA);
        $mform->setType('not', PARAM_BOOL);
        $mform->setType('source', PARAM_ALPHANUM);
        $mform->setType('source_instance_first', PARAM_BOOL);
        $mform->setType('source_instance_last', PARAM_BOOL);
        $mform->setType('other_source', PARAM_BOOL);
        $mform->setType('check_type', PARAM_ALPHA);
        $mform->setType('comp', PARAM_ALPHA);
        $mform->setType('value', PARAM_TEXT);
        $mform->setType('content_comp', PARAM_ALPHA);
        $mform->setType('content_count', PARAM_INT);

        // Dynamically set types for target fields
        foreach ($target_setting_options as $source_type => $settings) {
            $mform->setType('target_setting_' . $source_type, PARAM_ALPHANUM);
        }

        foreach ($target_content_options as $source_type => $contents) {
            $mform->setType('target_content_' . $source_type, PARAM_ALPHANUM);
        }

        // --- Resolutions ---
        $mform->addElement('header', 'resolutionsheader', get_string('resolutions', 'block_course_audit'));

        // This group defines the fields for a single resolution.
        $resolution_elements = [
            $mform->createElement('html', '<div class="resolution-group-container">'),
            $mform->createElement('select', 'res_scope', get_string('resolutioncontext', 'block_course_audit'), $source_options),
            $mform->createElement('checkbox', 'res_other_target', '', get_string('othertarget', 'block_course_audit')),
            $mform->createElement('select', 'res_type', get_string('resolutiontype', 'block_course_audit'), $resolution_type_options),
            $mform->createElement('textarea', 'res_hint', get_string('hintmessage', 'block_course_audit'), 'rows="3" cols="50"'),
            $mform->createElement('textarea', 'res_show', get_string('showmessage', 'block_course_audit'), 'rows="3" cols="50"'),
            $mform->createElement('select', 'res_actiontype', get_string('actiontype', 'block_course_audit'), $action_type_options),
        ];

        // Add dynamic setting options for each target type (for changesetting action)
        foreach ($target_setting_options as $target_type => $setting_options) {
            $resolution_elements[] = $mform->createElement('select', 'res_setting_' . $target_type, get_string('target_setting', 'block_course_audit'), ['' => get_string('choose', 'block_course_audit')] + $setting_options);
        }

        // Add dynamic content options for each target type (for addcontent action)
        foreach ($add_content_options as $target_type => $content_options) {
            $resolution_elements[] = $mform->createElement('select', 'res_addcontent_' . $target_type, get_string('addcontenttype', 'block_course_audit'), ['' => get_string('choose', 'block_course_audit')] + $content_options);
        }

        $resolution_elements[] = $mform->createElement('text', 'res_value', get_string('newsettingvalue', 'block_course_audit'), ['size' => 30]);
        $resolution_elements[] = $mform->createElement('submit', 'delete_resolution_button', get_string('delete'), [], false);
        $resolution_elements[] = $mform->createElement('html', '</div>');

        // Make the resolution elements repeatable.
        $resolution_options = [
            'res_scope' => ['default' => 'course'],
            'res_other_target' => ['default' => 0],
            'res_type' => ['default' => 'hint'],
            'res_hint' => ['default' => ''],
            'res_show' => ['default' => ''],
            'res_actiontype' => ['default' => 'changesetting'],
            'res_value' => ['default' => ''],
        ];

        // Add defaults for dynamic setting fields
        foreach ($target_setting_options as $target_type => $setting_options) {
            $resolution_options['res_setting_' . $target_type] = ['default' => ''];
        }

        // Add defaults for dynamic add content fields
        foreach ($add_content_options as $target_type => $content_options) {
            $resolution_options['res_addcontent_' . $target_type] = ['default' => ''];
        }

        $this->repeat_elements(
            $resolution_elements,
            1,
            $resolution_options,
            'hidden_res_no',
            'add_res_button',
            1,
            null,
            true,
            'delete_resolution_button'
        );

        $mform->setType('res_scope', PARAM_ALPHANUM);
        $mform->setType('res_other_target', PARAM_BOOL);
        $mform->setType('res_type', PARAM_ALPHA);
        $mform->setType('res_hint', PARAM_RAW);
        $mform->setType('res_show', PARAM_RAW);
        $mform->setType('res_actiontype', PARAM_ALPHA);
        $mform->setType('res_value', PARAM_TEXT);

        // Set types for dynamic setting fields
        foreach ($target_setting_options as $target_type => $setting_options) {
            $mform->setType('res_setting_' . $target_type, PARAM_ALPHANUM);
        }

        // Set types for dynamic add content fields
        foreach ($add_content_options as $target_type => $content_options) {
            $mform->setType('res_addcontent_' . $target_type, PARAM_ALPHANUM);
        }

        // Register buttons so they don't submit the form.
        $mform->registerNoSubmitButton('add_check_button');
        $mform->registerNoSubmitButton('add_resolution_button');
        $mform->registerNoSubmitButton('delete_check_button');
        $mform->registerNoSubmitButton('delete_resolution_button');

        // Group selection conditional logic - show new group field only when no existing group is selected
        $mform->hideIf('new_group_name', 'existing_group', 'neq', '');
        
        // Dynamically generate hideIf logic for target fields
        for ($i = 0; $i < 3; $i++) {
            // For each source type, hide target fields when source doesn't match
            foreach ($target_setting_options as $source_type => $settings) {
                $mform->hideIf('target_setting_' . $source_type . '[' . $i . ']', 'source[' . $i . ']', 'neq', $source_type);
                $mform->hideIf('target_setting_' . $source_type . '[' . $i . ']', 'check_type[' . $i . ']', 'neq', 'setting');
            }

            foreach ($target_content_options as $source_type => $contents) {
                $mform->hideIf('target_content_' . $source_type . '[' . $i . ']', 'source[' . $i . ']', 'neq', $source_type);
                $mform->hideIf('target_content_' . $source_type . '[' . $i . ']', 'check_type[' . $i . ']', 'neq', 'content');
            }

            // Hide comparison and value fields when not using settings
            $mform->hideIf('comp[' . $i . ']', 'check_type[' . $i . ']', 'neq', 'setting');
            $mform->hideIf('value[' . $i . ']', 'check_type[' . $i . ']', 'neq', 'setting');

            // Show content comparison fields only when using content
            $mform->hideIf('content_comp[' . $i . ']', 'check_type[' . $i . ']', 'neq', 'content');
            $mform->hideIf('content_count[' . $i . ']', 'check_type[' . $i . ']', 'neq', 'content');

            // Hide count field when "any" is selected in content comparison
            $mform->hideIf('content_count[' . $i . ']', 'content_comp[' . $i . ']', 'eq', 'any');
        }

        // Resolution conditional logic
        for ($i = 0; $i < 3; $i++) {
            // Show/hide based on resolution type
            $mform->hideIf('res_hint[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'hint');
            $mform->hideIf('res_show[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'show');
            $mform->hideIf('res_actiontype[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'action');
            
            // Show/hide res_value field only for changesetting actions
            $mform->hideIf('res_value[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'action');
            $mform->hideIf('res_value[' . $i . ']', 'res_actiontype[' . $i . ']', 'neq', 'changesetting');
            
            // Note: Content type field visibility is handled entirely by JavaScript
            // to avoid conflicts between Moodle's hideIf logic and our dynamic showing/hiding
        }

        // --- Action Buttons ---
        $this->add_action_buttons();

        // Add CSS and JavaScript to handle logical operators
        $this->add_logic_operator_handling();
    }

    /**
     * Add CSS and JavaScript to handle logical operators properly
     */
    private function add_logic_operator_handling()
    {
        global $PAGE;

        // Load the CSS file
        $PAGE->requires->css('/blocks/course_audit/styles.css');
        
        // Load the JavaScript AMD module
        $PAGE->requires->js_call_amd('block_course_audit/rule_form', 'init');
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        // Add custom validation here if needed.
        return $errors;
    }
}
