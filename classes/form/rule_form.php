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

        // Add JavaScript to handle form interactions
        $js = '
        require(["jquery"], function($) {
            $(document).ready(function() {
                
                // Handle source field dependencies - first check determines all others
                function updateSourceFields() {
                
                    var firstSource = $("#id_source_0").val();
                    var firstSourceText = $("#id_source_0 option:selected").text();
                    
                    // Update all subsequent source fields
                    $("[id^=id_source_]:not(#id_source_0):not([id^=id_source_instance_])").each(function() {
                        $(this).val(firstSource);
                        $(this).prop("disabled", true);
                    });
                    
                    // Update the "Other source" checkbox labels
                    $("[id^=id_other_source_]:not(#id_other_source_0)").each(function() {
                        var checkboxId = $(this).attr("id");
                        var index = checkboxId.replace("id_other_source_", "");
                        var label = $("label[for=\'" + checkboxId + "\']");
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
                            var label = $("label[for=\'" + checkboxId + "\']");
                            if (firstSourceText && label.length) {
                                var newText = "Other " + firstSourceText.toLowerCase() + "(s)";
                                if (label.text() !== newText) {
                                    label.text(newText);
                                }
                            }
                        });
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
                
                // Function to handle resolution field visibility
                function updateResolutionFieldVisibility() {
                    // For each resolution, show/hide the appropriate selectors
                    for (var i = 0; i < 3; i++) {
                        var resTypeSelect = $("#id_res_type_" + i);
                        var resActionTypeSelect = $("#id_res_actiontype_" + i);
                        var resScopeSelect = $("#id_res_scope_" + i);
                        
                        if (!resTypeSelect.length || !resActionTypeSelect.length || !resScopeSelect.length) continue;
                        
                        var resType = resTypeSelect.val();
                        var actionType = resActionTypeSelect.val();
                        var scope = resScopeSelect.val();
                        
                        console.log("Resolution " + i + ": type=" + resType + ", action=" + actionType + ", scope=" + scope);
                        
                        // Hide all setting and content type selectors for this resolution
                        $("[id^=id_res_setting_][id$=_" + i + "], [id^=id_res_addcontent_][id$=_" + i + "]").each(function() {
                            $(this).closest(".fitem").hide();
                            $(this).prop("disabled", true); // Disable when hidden
                        });
                        
                        // Show appropriate selector based on action type and scope
                        if (resType === "action" && scope) {
                            if (actionType === "changesetting") {
                                var settingSelector = $("#id_res_setting_" + scope + "_" + i);
                                console.log("Looking for setting selector: id_res_setting_" + scope + "_" + i + ", found: " + settingSelector.length);
                                if (settingSelector.length) {
                                    settingSelector.closest(".fitem").show();
                                    settingSelector.prop("disabled", false); // Enable when shown
                                    console.log("Showing setting selector for " + scope);
                                }
                            } else if (actionType === "addcontent") {
                                var contentSelector = $("#id_res_addcontent_" + scope + "_" + i);
                                console.log("Looking for content selector: id_res_addcontent_" + scope + "_" + i + ", found: " + contentSelector.length);
                                if (contentSelector.length) {
                                    contentSelector.closest(".fitem").show();
                                    contentSelector.prop("disabled", false); // Enable when shown
                                    console.log("Showing content type selector for " + scope);
                                }
                            }
                        }
                    }
                }
                
                // Add event listeners for resolution type changes
                $(document).on("change", "[id^=id_res_type_], [id^=id_res_actiontype_], [id^=id_res_scope_]", function() {
                    setTimeout(updateResolutionFieldVisibility, 50);
                });
                
                // Initial resolution field visibility setup
                setTimeout(updateResolutionFieldVisibility, 150);
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
                    
                    /* Hide "Other source" checkbox for first check */
                    #fitem_id_other_source_0 {
                        display: none !important;
                    }
                
                    
                    /* Style disabled source fields */
                    select[id^="id_source_"]:disabled,
                    select[id^="id_res_scope_"]:disabled {
                        background-color: #f5f5f5;
                        color: #666;
                        cursor: not-allowed;
                    }
                    
                    /* Visual styling for check and resolution containers */
                    .check-group-container {
                        border: 1px solid #ddd;
                        border-radius: 5px;
                        padding: 15px;
                        margin-bottom: 15px;
                        background-color: #f9f9f9;
                        position: relative;
                    }
                    
                    .check-group-container:first-of-type {
                        background-color: #fff;
                        border-color: #ccc;
                    }
                    
                    .check-group-container:not(:first-of-type) {
                        background-color: #f0f8ff;
                        border-color: #87ceeb;
                    }
                    
                    .check-group-container:not(:first-of-type)::before {
                        content: "Additional Check";
                        position: absolute;
                        top: -10px;
                        left: 10px;
                        background: white;
                        padding: 0 10px;
                        color: #007cba;
                        font-size: 12px;
                        font-weight: bold;
                        border: 1px solid #87ceeb;
                        border-radius: 3px;
                    }
                    
                    .resolution-group-container {
                        border: 1px solid #e0e0e0;
                        border-radius: 5px;
                        padding: 15px;
                        margin-bottom: 15px;
                        background-color: #fafafa;
                    }
                    
                    .resolution-group-container:not(:first-of-type)::before {
                        content: "Additional Resolution";
                        position: absolute;
                        top: -10px;
                        left: 10px;
                        background: white;
                        padding: 0 10px;
                        color: #666;
                        font-size: 12px;
                        font-weight: bold;
                        border: 1px solid #e0e0e0;
                        border-radius: 3px;
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
                    setTimeout(function() {
                        updateLogicalOperatorVisibility();
                        updateResolutionFieldVisibility();
                    }, 100);
                });
                
                function updateLogicalOperatorVisibility() {
                    // Always hide the first logical operator and first delete buttons
                    $("#fitem_id_next_logic_0").hide();
                    $("#fitem_id_delete_check_button_0").hide();
                    $("#fitem_id_delete_resolution_button_0").hide();

                    // Update the "Other source" checkbox labels
                    $("[id^=id_source_instance_first_]:not([id^=id_source_instance_first_0])").each(function() {
                        $(this).closest(".fitem").hide();
                    });

                    $("[id^=id_source_instance_last_]:not([id^=id_source_instance_last_0])").each(function() {
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
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        // Add custom validation here if needed.
        return $errors;
    }
}
