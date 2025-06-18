<?php

namespace block_course_audit\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

class rule_form extends \moodleform {

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $ajaxformdata = null) {
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    protected function definition() {
        $mform = $this->_form;

        // Note: For a dynamic form like this, many of these options would be populated by JS
        // based on selections in other fields. For this initial PHP version, we will use
        // static lists and text fields as placeholders.

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
            $mform->createElement('select', 'next_logic', get_string('logicaloperator', 'block_course_audit'), ['AND' => 'AND', 'OR' => 'OR']),
            $mform->createElement('checkbox', 'not', '', get_string('not', 'block_course_audit')),
            $mform->createElement('select', 'source', get_string('source', 'block_course_audit'), $source_options),
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

        // Make the check elements repeatable.
        // Initially show only 1 check, hide the rest until needed via JS
        $check_options = [
            'not' => ['default' => 0],
            'source' => ['default' => 'section'],
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
            $mform->createElement('select', 'res_scope', get_string('resolutioncontext', 'block_course_audit'), $source_options), // Note: Should also have "same_as..." options from checks.
            $mform->createElement('select', 'res_type', get_string('resolutiontype', 'block_course_audit'), $resolution_type_options),
            $mform->createElement('textarea', 'res_hint', get_string('hintmessage', 'block_course_audit'), 'rows="3" cols="50"'),
            $mform->createElement('select', 'res_actiontype', get_string('actiontype', 'block_course_audit'), $action_type_options),
            $mform->createElement('text', 'res_settingorcontent', get_string('settingorcontent', 'block_course_audit'), ['size' => 30]), // Note: Should be a dynamic select.
            $mform->createElement('text', 'res_value', get_string('newsettingvalue', 'block_course_audit'), ['size' => 30]),
            $mform->createElement('submit', 'delete_resolution_button', get_string('delete'), [], false),
        ];

        // Make the resolution elements repeatable.
        $resolution_options = [
            'res_scope' => ['default' => 'course'],
            'res_type' => ['default' => 'hint'],
            'res_hint' => ['default' => ''],
            'res_actiontype' => ['default' => 'changesetting'],
            'res_settingorcontent' => ['default' => ''],
            'res_value' => ['default' => ''],
        ];
        
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
        $mform->setType('res_type', PARAM_ALPHA);
        $mform->setType('res_hint', PARAM_RAW);
        $mform->setType('res_actiontype', PARAM_ALPHA);
        $mform->setType('res_settingorcontent', PARAM_TEXT);
        $mform->setType('res_value', PARAM_TEXT);

        // Register buttons so they don't submit the form.
        $mform->registerNoSubmitButton('add_check_button');
        $mform->registerNoSubmitButton('add_resolution_button');
        $mform->registerNoSubmitButton('delete_check_button');
        $mform->registerNoSubmitButton('delete_resolution_button');
        
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
            $mform->hideIf('res_hint[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'hint');
            $mform->hideIf('res_actiontype[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'action');
            $mform->hideIf('res_settingorcontent[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'action');
            $mform->hideIf('res_value[' . $i . ']', 'res_type[' . $i . ']', 'neq', 'action');
        }

        // --- Action Buttons ---
        $this->add_action_buttons();
        
        // Add CSS and JavaScript to handle logical operators
        $this->add_logic_operator_handling();
    }
    
    /**
     * Add CSS and JavaScript to handle logical operators properly
     */
    private function add_logic_operator_handling() {
        global $PAGE;
        
        // Add JavaScript to handle form interactions
        $js = '
        require(["jquery"], function($) {
            $(document).ready(function() {
                // Add inline CSS to hide first elements and style multiple checks
                var style = document.createElement("style");
                style.textContent = `
                    /* Hide the logical operator for the first check */
                    #fitem_id_next_logic_0 {
                        display: none !important;
                    }
                    /* Hide the delete button for the first check */
                    #fitem_id_delete_check_button_0 {
                        display: none !important;
                    }
                    /* Hide the delete button for the first resolution */
                    #fitem_id_delete_resolution_button_0 {
                        display: none !important;
                    }
                    
                    /* Visual styling for multiple checks */
                    .check-group {
                        border: 1px solid #ddd;
                        border-radius: 5px;
                        padding: 15px;
                        margin-bottom: 15px;
                 /*        background-color: #f9f9f9; */
                    }
                    
                    .check-group-first {
                        background-color: #fff;
                        border-color: #ccc;
                    }
                    
                    .check-group-additional {
                       /*  background-color: #f0f8ff; */
                        border-color: #87ceeb;
                        position: relative;
                    }
                    
                    .check-separator {
                        border-top: 2px solid #007cba;
                        margin: 20px 0 15px 0;
                        position: relative;
                    }
                    
                    .check-separator::before {
                        content: "Additional Check";
                        position: absolute;
                        top: -10px;
                        left: 10px;
                        background: white;
                        padding: 0 10px;
                        color: #007cba;
                        font-size: 12px;
                        font-weight: bold;
                    }
                    
                    /* Content comparison inline styling - only when visible */
                    [id*="fitem_id_content_comp_"]:not([hidden]):not([style*="display: none"]) {
                        width: auto !important;
                        margin-right: 10px !important;
                        vertical-align: top !important;
                    }
                    [id*="fitem_id_content_count_"]:not([hidden]):not([style*="display: none"]) {
                        width: auto !important;
                        vertical-align: top !important;
                    }
                    [id*="fitem_id_content_comp_"] .felement,
                    [id*="fitem_id_content_count_"] .felement {
                        width: auto !important;
                    }
                    .content-comp-inline {
                        margin-right: 5px !important;
                    }
                    .content-count-inline {
                        width: 80px !important;
                    }
                    
                    /* Ensure hidden elements stay hidden */
                    [id*="fitem_id_content_comp_"][hidden],
                    [id*="fitem_id_content_count_"][hidden] {
                        display: none !important;
                    }
                `;
                document.head.appendChild(style);
                
                // When checks are added/removed, ensure first logical operator stays hidden
                updateLogicalOperatorVisibility();
                
                // Monitor for changes to the form structure
                $(document).on("click", "[id*=add_check_button], [id*=delete_check_button], [id*=add_resolution_button], [id*=delete_resolution_button]", function() {
                    setTimeout(updateLogicalOperatorVisibility, 100);
                });
                
                function updateLogicalOperatorVisibility() {
                    // Always hide the first logical operator and first delete buttons
                    $("#fitem_id_next_logic_0").hide();
                    $("#fitem_id_delete_check_button_0").hide();
                    $("#fitem_id_delete_resolution_button_0").hide();
                    
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
                    
                    // Add visual styling for multiple checks
                    styleCheckGroups();
                    
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
                
                function styleCheckGroups() {
                    // Remove existing styling
                    $(".check-group, .check-separator").remove();
                    
                    // Find all check groups
                    for (var i = 0; i < 4; i++) {
                        var sourceElement = $("#fitem_id_source_" + i);
                        if (sourceElement.length) {
                            var checkElements = [];
                            
                            // Collect all elements for this check in correct order
                            if (i > 0) {
                                var logicElement = $("#fitem_id_next_logic_" + i);
                                if (logicElement.length) checkElements.push(logicElement);
                            }
                            
                            // Find the NOT checkbox form item by looking for the specific fitem pattern
                            var notElement = $("[class*=fitem]").filter(function() {
                                return $(this).find("#id_not_" + i).length > 0;
                            });
                            if (notElement.length) {
                                checkElements.push(notElement);
                            }
                            
                            checkElements.push(sourceElement);
                            checkElements.push($("#fitem_id_check_type_" + i));
                            
                            // Dynamically add all target field elements
                            var targetElements = ["course", "section", "quiz", "assign"];
                            targetElements.forEach(function(sourceType) {
                                checkElements.push($("#fitem_id_target_setting_" + sourceType + "_" + i));
                                checkElements.push($("#fitem_id_target_content_" + sourceType + "_" + i));
                            });
                            
                            checkElements.push($("#fitem_id_comp_" + i));
                            checkElements.push($("#fitem_id_value_" + i));
                            checkElements.push($("#fitem_id_content_comp_" + i));
                            checkElements.push($("#fitem_id_content_count_" + i));
                            checkElements.push($("#fitem_id_delete_check_button_" + i));

                            // Filter out elements that dont exist
                            checkElements = checkElements.filter(function(elem) {
                                return elem.length > 0;
                            });
                            
                            if (checkElements.length > 0) {
                                // Add separator for additional checks
                                if (i > 0) {
                                    $("<div class=\\"check-separator\\"></div>").insertBefore(checkElements[0]);
                                }
                                
                                // Wrap elements in a group
                                var groupClass = i === 0 ? "check-group check-group-first" : "check-group check-group-additional";
                                var wrapper = $("<div class=\\"" + groupClass + "\\"></div>");
                                
                                checkElements[0].before(wrapper);
                                checkElements.forEach(function(elem) {
                                    wrapper.append(elem);
                                });
                            }
                        }
                    }
                }
            });
        });
        ';
        
        $PAGE->requires->js_amd_inline($js);
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