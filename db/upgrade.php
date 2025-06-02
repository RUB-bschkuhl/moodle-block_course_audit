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
 * Upgrade script for the Course Audit block.
 *
 * @package    block_course_audit
 * @copyright  2024 Your Name <your.email@example.com> // Please replace with actual details
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Perform the upgrade steps for the Course Audit block.
 *
 * @param int $oldversion The old version number of the plugin.
 * @return bool True on success, false on failure.
 */
function xmldb_block_course_audit_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager(); // Load database manager

    // Upgrade step: Create block_course_audit_tours table (Version 2025041401).
    if ($oldversion < 2025041401) {
        $table = new xmldb_table('block_course_audit_tours');
        // Define fields only if the table doesn't exist.
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('tourid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('tourid', XMLDB_KEY_FOREIGN, array('tourid'), 'tool_usertours_tours', array('id'));
            $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
            $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
            $dbman->create_table($table);
        }
        // Course_audit savepoint.
        upgrade_plugin_savepoint(true, 2025041401, 'block', 'course_audit');
    }

    // Upgrade step: Create block_course_audit_results table (Version 2025041402).
    if ($oldversion < 2025041402) {
        $table = new xmldb_table('block_course_audit_results');
        // Define fields only if the table doesn't exist.
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('auditid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('rulekey', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('rulecategory', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'general', 'rulekey');
            $table->add_field('status', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('messages', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('targettype', XMLDB_TYPE_CHAR, '50', null, null, null, null);
            $table->add_field('targetid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('auditid', XMLDB_KEY_FOREIGN, array('auditid'), 'block_course_audit_tours', array('id'));

            $dbman->create_table($table);
        }
        // Course_audit savepoint.
        upgrade_plugin_savepoint(true, 2025041402, 'block', 'course_audit');
    }

    // Add future upgrade steps here below this line, using similar if ($oldversion < X) conditions.

    // START: New DB schema for dynamic rules from version 2025052002.
    if ($oldversion < 2025052002) {

        // Table: block_course_audit_rule_sets.
        $table = new xmldb_table('block_course_audit_rule_sets');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Table: block_course_audit_rules.
        $table = new xmldb_table('block_course_audit_rules');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('rulesetid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('failure_hint_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_rulesetid', XMLDB_KEY_FOREIGN, array('rulesetid'), 'block_course_audit_rule_sets', array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Table: block_course_audit_condition_chains.
        $table = new xmldb_table('block_course_audit_condition_chains');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ruleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('chain_order', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('logical_operator_to_next', XMLDB_TYPE_CHAR, '3', null, null, null, null); // AND or OR
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_ruleid', XMLDB_KEY_FOREIGN, array('ruleid'), 'block_course_audit_rules', array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Table: block_course_audit_condition_segments.
        $table = new xmldb_table('block_course_audit_condition_segments');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('conditionchainid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('segment_order', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('target_type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null); // COURSE, SECTION, MODULE, SUB_ELEMENT
        $table->add_field('target_identifier', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('check_type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null); // HAS_CONTENT, NOT_HAS_CONTENT, HAS_SETTING, NOT_HAS_SETTING
        $table->add_field('content_child_type', XMLDB_TYPE_CHAR, '20', null, null, null, null); // SECTION, MODULE, SUB_ELEMENT
        $table->add_field('content_child_identifier', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('setting_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('setting_operator', XMLDB_TYPE_CHAR, '20', null, null, null, null); // EQUALS, CONTAINS, REGEX, GT, LT, IS_TRUE, etc.
        $table->add_field('setting_expected_value', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('setting_value_data_type', XMLDB_TYPE_CHAR, '10', null, null, null, null); // STRING, INTEGER, BOOLEAN, FLOAT
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_conditionchainid', XMLDB_KEY_FOREIGN, array('conditionchainid'), 'block_course_audit_condition_chains', array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Table: block_course_audit_actions.
        $table = new xmldb_table('block_course_audit_actions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ruleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('action_order', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('button_label', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('action_type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null); // CHANGE_SETTING, ADD_CONTENT
        $table->add_field('action_target_chain_index', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('action_target_segment_index', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('change_setting_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('change_setting_new_value', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('change_setting_value_data_type', XMLDB_TYPE_CHAR, '10', null, null, null, null); // STRING, INTEGER, BOOLEAN
        $table->add_field('add_content_parent_chain_index', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('add_content_parent_segment_index', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('add_content_child_type', XMLDB_TYPE_CHAR, '20', null, null, null, null); // SECTION, MODULE, SUB_ELEMENT
        $table->add_field('add_content_child_identifier', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('add_content_initial_settings', XMLDB_TYPE_TEXT, null, null, null, null, null); // JSON string
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_action_ruleid', XMLDB_KEY_FOREIGN, array('ruleid'), 'block_course_audit_rules', array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Seed initial data for the rules.
        // Rule: Quiz should be repeatable.
        $ruleset = new stdClass();
        $ruleset->name = 'Quiz Best Practices';
        $ruleset->description = 'Ensures quizzes are configured according to best practices for student engagement and assessment.';
        $ruleset->timecreated = time();
        $ruleset->timemodified = time();
        $rulesetid = $DB->insert_record('block_course_audit_rule_sets', $ruleset);

        $rule = new stdClass();
        $rule->rulesetid = $rulesetid;
        $rule->name = 'Quiz: Allow Multiple Attempts';
        $rule->failure_hint_text = 'This quiz currently allows only one attempt. Consider allowing multiple attempts for practice or review.';
        $rule->timecreated = time();
        $rule->timemodified = time();
        $ruleid = $DB->insert_record('block_course_audit_rules', $rule);

        $conditionchain = new stdClass();
        $conditionchain->ruleid = $ruleid;
        $conditionchain->chain_order = 0;
        $conditionchain->logical_operator_to_next = null;
        $conditionchainid = $DB->insert_record('block_course_audit_condition_chains', $conditionchain);

        $segment1 = new stdClass();
        $segment1->conditionchainid = $conditionchainid;
        $segment1->segment_order = 0;
        $segment1->target_type = 'COURSE';
        $segment1->check_type = 'HAS_CONTENT';
        $segment1->content_child_type = 'SECTION';
        $DB->insert_record('block_course_audit_condition_segments', $segment1);

        $segment2 = new stdClass();
        $segment2->conditionchainid = $conditionchainid;
        $segment2->segment_order = 1;
        $segment2->target_type = 'SECTION';
        $segment2->check_type = 'HAS_CONTENT';
        $segment2->content_child_type = 'MODULE';
        $segment2->content_child_identifier = 'quiz';
        $DB->insert_record('block_course_audit_condition_segments', $segment2);

        $segment3 = new stdClass();
        $segment3->conditionchainid = $conditionchainid;
        $segment3->segment_order = 2;
        $segment3->target_type = 'MODULE';
        $segment3->target_identifier = 'quiz';
        $segment3->check_type = 'HAS_SETTING';
        $segment3->setting_name = 'attempts';
        $segment3->setting_operator = 'NOT_EQUALS';
        $segment3->setting_expected_value = '1';
        $segment3->setting_value_data_type = 'INTEGER';
        $DB->insert_record('block_course_audit_condition_segments', $segment3);

        $action = new stdClass();
        $action->ruleid = $ruleid;
        $action->action_order = 0;
        $action->button_label = 'Make Repeatable (Unlimited Attempts)';
        $action->action_type = 'CHANGE_SETTING';
        $action->action_target_chain_index = 0;
        $action->action_target_segment_index = 2; // Points to the quiz segment.
        $action->change_setting_name = 'attempts';
        $action->change_setting_new_value = '0'; // 0 for unlimited.
        $action->change_setting_value_data_type = 'INTEGER';
        $DB->insert_record('block_course_audit_actions', $action);

        // Upgrade savepoint reached.
        upgrade_block_savepoint(true, 2025052002, 'course_audit');
    }
    // END: New DB schema for dynamic rules.

    return true;
}
