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
$mform = new rule_form();

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
        $numchecks = count($fromform->scope);
        for ($i = 0; $i < $numchecks; $i++) {
            $check = new stdClass();
            $check->rule_id = $id;
            $check->sort_order = $i;
            $check->scope = $fromform->scope[$i];
            $check->not_check = !empty($fromform->not[$i]);
            $check->source = $fromform->source[$i];
            $check->check_type = $fromform->check_type[$i];
            $check->target = $fromform->target[$i];
            $check->comp = $fromform->comp[$i];
            $check->value = $fromform->value[$i];
            $check->value_type = $fromform->value_type[$i];

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
            $resolution->type = $fromform->res_type[$i];

            if ($resolution->type == 'hint') {
                $resolution->settingorcontent = $fromform->res_hint[$i];
            } else { // 'action'
                $resolution->actiontype = $fromform->res_actiontype[$i];
                $resolution->settingorcontent = $fromform->res_settingorcontent[$i];
                $resolution->value = $fromform->res_value[$i];
            }
            $DB->insert_record('block_course_audit_resolution', $resolution);
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

        $i = 0;
        foreach ($checks as $check) {
            $toform->scope[$i] = $check->scope;
            $toform->not[$i] = $check->not_check;
            $toform->source[$i] = $check->source;
            $toform->check_type[$i] = $check->check_type;
            $toform->target[$i] = $check->target;
            $toform->comp[$i] = $check->comp;
            $toform->value[$i] = $check->value;
            $toform->value_type[$i] = $check->value_type;
            if (isset($check->next_logic)) {
                $toform->next_logic[$i] = $check->next_logic;
            }
            $i++;
        }

        $i = 0;
        foreach ($resolutions as $resolution) {
            $toform->res_scope[$i] = $resolution->scope;
            $toform->res_type[$i] = $resolution->type;
            if ($resolution->type == 'hint') {
                $toform->res_hint[$i] = $resolution->settingorcontent;
            } else {
                $toform->res_actiontype[$i] = $resolution->actiontype;
                $toform->res_settingorcontent[$i] = $resolution->settingorcontent;
                $toform->res_value[$i] = $resolution->value;
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