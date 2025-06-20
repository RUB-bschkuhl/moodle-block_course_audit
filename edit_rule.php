<?php

require_once('../../config.php');
require_once(__DIR__ . '/classes/form/rule_form.php');

use block_course_audit\form\rule_form;

// Get params.
$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT); // Rule ID, 0 for new rule.

// Setup page.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id);
require_login($course);
require_capability('block/course_audit:managerules', $context);

$PAGE->set_url('/blocks/course_audit/edit_rule.php', ['courseid' => $courseid, 'id' => $id]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

// Instantiate the form.
$formurl = new moodle_url('/blocks/course_audit/edit_rule.php', ['courseid' => $courseid, 'id' => $id]);
$customdata = ['id' => $id];
$mform = new rule_form($formurl, $customdata);

// Handle form submission.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($fromform = $mform->get_data()) {
    $transaction = $DB->start_delegated_transaction();
    try {
        // 1. Save the main rule object.
        $rule = new stdClass();
        $rule->rule_name = $fromform->rule_name;
        $rule->rule_description = $fromform->rule_description;
        $rule->createdby = $USER->id;
        $rule->timemodified = time();

        if ($id) { // Existing rule.
            $rule->id = $id;
            $DB->update_record('block_course_audit_rule', $rule);
            // Delete old checks and resolutions before inserting new ones.
            $DB->delete_records('block_course_audit_check', ['rule_id' => $id]);
            $DB->delete_records('block_course_audit_resolution', ['rule_id' => $id]);
        } else { // New rule.
            $rule->timecreated = time();
            $id = $DB->insert_record('block_course_audit_rule', $rule);
        }

        // 2. Save the checks.
        $numchecks = count($fromform->source);
        for ($i = 0; $i < $numchecks; $i++) {
            $check = new stdClass();
            $check->rule_id = $id;
            $check->sort_order = $i;
            $check->not_check = !empty($fromform->not[$i]);
            $check->source = $fromform->source[$i];
            $check->source_instance_first = !empty($fromform->source_instance_first[$i]);
            $check->source_instance_last = !empty($fromform->source_instance_last[$i]);
            $check->other_source = !empty($fromform->other_source[$i]);
            $check->check_type = $fromform->check_type[$i];
            
            // Determine target based on check_type and source
            if ($fromform->check_type[$i] == 'setting') {
                $target_field = 'target_setting_' . $fromform->source[$i];
                $check->target = $fromform->$target_field[$i];
            } else { // content
                $target_field = 'target_content_' . $fromform->source[$i];
                $check->target = $fromform->$target_field[$i];
            }
            
            $check->comp = $fromform->comp[$i];
            $check->value = $fromform->value[$i];
            $check->value_type = $fromform->value_type[$i];
            $check->content_comp = $fromform->content_comp[$i];
            $check->content_count = $fromform->content_count[$i];

            if ($i < ($numchecks - 1)) {
                $check->next_logic = $fromform->next_logic[$i];
            } else {
                 $check->next_logic = null;
            }
            $DB->insert_record('block_course_audit_check', $check);
        }

        // 3. Save the resolutions.
        $numresolutions = count($fromform->res_scope);
        for ($i = 0; $i < $numresolutions; $i++) {
            $resolution = new stdClass();
            $resolution->rule_id = $id;
            $resolution->scope = $fromform->res_scope[$i];
            $resolution->other_target = !empty($fromform->res_other_target[$i]);
            $resolution->type = $fromform->res_type[$i];

            // Handle different resolution types
            if ($resolution->type == 'hint') {
                $resolution->hint_message = $fromform->res_hint[$i];
            } else if ($resolution->type == 'show') {
                $resolution->show_message = $fromform->res_show[$i];
            } else { // 'action'
                $resolution->actiontype = $fromform->res_actiontype[$i];
                
                if ($resolution->actiontype == 'changesetting') {
                    // Find the appropriate setting field based on scope
                    $setting_field = 'res_setting_' . $resolution->scope;
                    if (isset($fromform->$setting_field[$i])) {
                        $resolution->settingorcontent = $fromform->$setting_field[$i];
                    }
                    $resolution->value = $fromform->res_value[$i];
                } else if ($resolution->actiontype == 'addcontent') {
                    // Find the appropriate content type field based on scope
                    $content_type_field = 'res_addcontent_' . $resolution->scope;
                    if (isset($fromform->$content_type_field[$i])) {
                        $resolution->content_type = $fromform->$content_type_field[$i];
                    }
                }
            }
            $DB->insert_record('block_course_audit_resolution', $resolution);
        }

        // 4. Save preconditions.
        if ($id) {
            // Delete existing preconditions before inserting new ones.
            $DB->delete_records('block_course_audit_precond', ['rule_id' => $id]);
        }
        
        if (!empty($fromform->preconditions)) {
            foreach ($fromform->preconditions as $precondition_rule_id) {
                $precondition = new stdClass();
                $precondition->rule_id = $id;
                $precondition->precondition_rule_id = $precondition_rule_id;
                $DB->insert_record('block_course_audit_precond', $precondition);
            }
        }

        // 5. Handle group assignment.
        $group_id = null;
        
        // Create new group if specified
        if (!empty($fromform->new_group_name)) {
            $group = new stdClass();
            $group->collection_name = $fromform->new_group_name;
            $group->createdby = $USER->id;
            $group->timecreated = time();
            $group->timemodified = time();
            $group_id = $DB->insert_record('block_course_audit_rule_coll', $group);
        } else if (!empty($fromform->existing_group)) {
            $group_id = $fromform->existing_group;
        }
        
        // Add rule to group if a group was selected or created
        if ($group_id) {
            // Remove existing group associations for this rule
            $DB->delete_records('block_course_audit_coll_rule', ['rule_id' => $id]);
            
            // Add new group association
            $group_rule = new stdClass();
            $group_rule->rule_collection_id = $group_id;
            $group_rule->rule_id = $id;
            $DB->insert_record('block_course_audit_coll_rule', $group_rule);
        }

        $transaction->commit();
        \core\notification::add(get_string('rulesaved', 'block_course_audit'), 'success');
    } catch (Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else {
    // If we are editing an existing rule, load its data into the form.
    if ($id) {
        $rule_data = $DB->get_record('block_course_audit_rule', ['id' => $id], '*', MUST_EXIST);
        $checks = $DB->get_records('block_course_audit_check', ['rule_id' => $id], 'sort_order ASC');
        $resolutions = $DB->get_records('block_course_audit_resolution', ['rule_id' => $id]);

        $toform = new stdClass();
        $toform->rule_name = $rule_data->rule_name;
        $toform->rule_description = $rule_data->rule_description;

        // Load preconditions
        $preconditions = $DB->get_records('block_course_audit_precond', ['rule_id' => $id], '', 'precondition_rule_id');
        $toform->preconditions = array_keys($preconditions);

        // Load group association
        $group_association = $DB->get_record('block_course_audit_coll_rule', ['rule_id' => $id]);
        if ($group_association) {
            $toform->existing_group = $group_association->rule_collection_id;
        }

        $i = 0;
        foreach ($checks as $check) {
            $toform->not[$i] = $check->not_check;
            $toform->source[$i] = $check->source;
            $toform->source_instance_first[$i] = $check->source_instance_first ?? 0;
            $toform->source_instance_last[$i] = $check->source_instance_last ?? 0;
            $toform->other_source[$i] = $check->other_source ?? 0;
            $toform->check_type[$i] = $check->check_type;
            
            // Populate the appropriate target field based on check_type and source
            $source_types = ['course', 'section', 'quiz', 'assign'];
            foreach ($source_types as $source_type) {
                if ($check->check_type == 'setting') {
                    if ($check->source == $source_type) {
                        $target_field = 'target_setting_' . $source_type;
                        $toform->$target_field[$i] = $check->target;
                    } else {
                        $target_field = 'target_setting_' . $source_type;
                        if (!isset($toform->$target_field)) {
                            $toform->$target_field = [];
                        }
                        $toform->$target_field[$i] = '';
                    }
                } else { // content
                    if ($check->source == $source_type) {
                        $target_field = 'target_content_' . $source_type;
                        $toform->$target_field[$i] = $check->target;
                    } else {
                        $target_field = 'target_content_' . $source_type;
                        if (!isset($toform->$target_field)) {
                            $toform->$target_field = [];
                        }
                        $toform->$target_field[$i] = '';
                    }
                }
            }
            
            $toform->comp[$i] = $check->comp;
            $toform->value[$i] = $check->value;
            $toform->value_type[$i] = $check->value_type;
            $toform->content_comp[$i] = $check->content_comp ?? 'eq';
            $toform->content_count[$i] = $check->content_count ?? '1';
            if (isset($check->next_logic)) {
                $toform->next_logic[$i] = $check->next_logic;
            }
            $i++;
        }

        $i = 0;
        foreach ($resolutions as $resolution) {
            $toform->res_scope[$i] = $resolution->scope;
            $toform->res_other_target[$i] = $resolution->other_target ?? 0;
            $toform->res_type[$i] = $resolution->type;
            
            if ($resolution->type == 'hint') {
                $toform->res_hint[$i] = $resolution->hint_message ?? $resolution->settingorcontent ?? '';
            } else if ($resolution->type == 'show') {
                $toform->res_show[$i] = $resolution->show_message ?? '';
            } else { // action
                $toform->res_actiontype[$i] = $resolution->actiontype ?? '';
                
                if ($resolution->actiontype == 'changesetting') {
                    // Load setting into appropriate field
                    $setting_field = 'res_setting_' . $resolution->scope;
                    if (!isset($toform->$setting_field)) {
                        $toform->$setting_field = [];
                    }
                    $toform->$setting_field[$i] = $resolution->settingorcontent ?? '';
                    $toform->res_value[$i] = $resolution->value ?? '';
                } else if ($resolution->actiontype == 'addcontent') {
                    // Load content type into appropriate field
                    $content_type_field = 'res_addcontent_' . $resolution->scope;
                    if (!isset($toform->$content_type_field)) {
                        $toform->$content_type_field = [];
                    }
                    $toform->$content_type_field[$i] = $resolution->content_type ?? '';
                }
            }
            $i++;
        }

        $mform->set_data($toform);
    }
}

// Set up page title and breadcrumbs.
$pagetitle = get_string('editrule', 'block_course_audit');
if ($id) {
    $rule_name = $DB->get_field('block_course_audit_rule', 'rule_name', ['id' => $id]);
    $pagetitle .= ': ' . format_string($rule_name);
}
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add(get_string('rules', 'block_course_audit'));
$PAGE->navbar->add($pagetitle);


// Display the page.
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer(); 