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
 * Page for managing (creating/editing) audit rules for the Course Audit block.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/course_audit/classes/forms/edit_rule_form.php');

$courseid = required_param('courseid', PARAM_INT); // Rules are typically course-specific contextually.
$id = optional_param('id', 0, PARAM_INT); // Rule ID, if editing an existing one.

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course, false);
require_capability('block/course_audit:managerules', $context);

$PAGE->set_url('/blocks/course_audit/manage_rules.php', array('courseid' => $courseid, 'id' => $id));
$PAGE->set_pagelayout('standard'); // Or incourse, admin etc.
$PAGE->set_context($context);

$title = $id ? get_string('editrule', 'block_course_audit') : get_string('createnewrule', 'block_course_audit');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js_call_amd('block_course_audit/rule_form', 'init');

echo $OUTPUT->header();

$customformdata = ['courseid' => $courseid];
$ruleform = new block_course_audit_edit_rule_form(null, $customformdata);

if ($ruleform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
} else if ($fromform = $ruleform->get_data()) {
    // --- Process the submitted data ---
    // This is where the complex logic to save rule sets, rules, conditions, and actions will go.
    // It will involve parsing the $fromform data (which will have many dynamic fields due to JS)
    // and saving it to the new database tables.

    // Example: Basic structure of what you might do (highly simplified)
    /*
    $transaction = $DB->start_delegated_transaction();
    try {
        $rulesetid = $fromform->rulesetid;
        if ($rulesetid == 0) { // Create new rule set
            $newset = new stdClass();
            $newset->name = $fromform->newrulesetname;
            $newset->description = $fromform->newrulesetdescription;
            $newset->timecreated = time();
            $newset->timemodified = $newset->timecreated;
            $rulesetid = $DB->insert_record('block_course_audit_rule_sets', $newset);
        }

        $rule = new stdClass();
        $rule->rulesetid = $rulesetid;
        $rule->name = $fromform->rulename;
        $rule->failure_hint_text = $fromform->failurehinttext;
        // ... and so on for other rule fields, then condition chains, segments, actions.

        // For repeatable elements, you'd loop through data submitted by JS.
        // e.g., for ($i = 0; isset($fromform->{"chain_{$i}_segment_0_target_type"}); $i++) { ... }

        $rule->id = $DB->insert_record('block_course_audit_rules', $rule); // or update_record if $id

        $transaction->allow_commit();
        redirect(new moodle_url('/blocks/course_audit/manage_rules.php', ['courseid' => $courseid, 'id' => $rule->id, 'success' => 1]), get_string('changessaved'), 2);
    } catch (Exception $e) {
        $transaction->rollback($e);
        // Handle error, display message.
        echo $OUTPUT->notification(get_string('errorsaving') . $e->getMessage());
    }
    */
    echo $OUTPUT->notification(get_string('datasubmitted', 'block_course_audit') . '<br><pre>' . s(print_r($fromform, true)) . '</pre>', 'notifysuccess');
    // For now, just display submitted data and the form again.

} else {
    // Set initial data if editing.
    if ($id) {
        // TODO: Load rule data from DB and set form defaults.
        // $ruledata = ... load from DB ...
        // $ruleform->set_data($ruledata);
    }
}

$ruleform->display();

echo $OUTPUT->footer(); 