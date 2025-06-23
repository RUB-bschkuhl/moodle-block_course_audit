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

    if ($oldversion < 2025062002) {
        // Define fields to be added to block_course_audit_check.
        $table = new xmldb_table('block_course_audit_check');
        
        $field1 = new xmldb_field('content_comp', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'value_type');
        $field2 = new xmldb_field('content_count', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'content_comp');

        // Conditionally launch add field content_comp.
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        // Conditionally launch add field content_count.
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Course audit savepoint reached.
        upgrade_block_savepoint(true, 2025062002, 'course_audit');
    }

    if ($oldversion < 2025062003) {
        // Define field to be added to block_course_audit_check.
        $table = new xmldb_table('block_course_audit_check');
        
        $field = new xmldb_field('other_source', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'source');

        // Conditionally launch add field other_source.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Course audit savepoint reached.
        upgrade_block_savepoint(true, 2025062003, 'course_audit');
    }

    if ($oldversion < 2025062004) {
        // Define fields to be added to block_course_audit_check.
        $table = new xmldb_table('block_course_audit_check');
        
        // Remove old source_instance field if it exists (from previous version)
        $old_field = new xmldb_field('source_instance');
        if ($dbman->field_exists($table, $old_field)) {
            $dbman->drop_field($table, $old_field);
        }
        
        // Add new boolean fields for first and last instance selection
        $field1 = new xmldb_field('source_instance_first', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'source');
        $field2 = new xmldb_field('source_instance_last', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'source_instance_first');

        // Conditionally launch add field source_instance_first.
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        // Conditionally launch add field source_instance_last.
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Course audit savepoint reached.
        upgrade_block_savepoint(true, 2025062004, 'course_audit');
    }

    if ($oldversion < 2025062005) {
        // Define table block_course_audit_precond to be created.
        $table = new xmldb_table('block_course_audit_precond');

        // Adding fields to table block_course_audit_precond.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('rule_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('precondition_rule_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_course_audit_precond.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fk_rule_id', XMLDB_KEY_FOREIGN, ['rule_id'], 'block_course_audit_rule', ['id']);
        $table->add_key('fk_precondition_rule_id', XMLDB_KEY_FOREIGN, ['precondition_rule_id'], 'block_course_audit_rule', ['id']);

        // Adding indexes to table block_course_audit_precond.
        $table->add_index('rule_precond_unique', XMLDB_INDEX_UNIQUE, ['rule_id', 'precondition_rule_id']);

        // Conditionally launch create table for block_course_audit_precond.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Course audit savepoint reached.
        upgrade_block_savepoint(true, 2025062005, 'course_audit');
    }

    if ($oldversion < 2025062006) {
        // Define field to be added to block_course_audit_resolution.
        $table = new xmldb_table('block_course_audit_resolution');
        
        $field = new xmldb_field('other_target', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'scope');

        // Conditionally launch add field other_target.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Course audit savepoint reached.
        upgrade_block_savepoint(true, 2025062006, 'course_audit');
    }

    if ($oldversion < 2025062007) {
        // Add new fields for show resolution type and structured content handling.
        $table = new xmldb_table('block_course_audit_resolution');
        
        // Add hint_message field
        $field1 = new xmldb_field('hint_message', XMLDB_TYPE_TEXT, null, null, null, null, null, 'type');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        // Add show_message field
        $field2 = new xmldb_field('show_message', XMLDB_TYPE_TEXT, null, null, null, null, null, 'hint_message');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Add content_type field for structured addcontent actions
        $field3 = new xmldb_field('content_type', XMLDB_TYPE_TEXT, null, null, null, null, null, 'settingorcontent');
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }

        // Course audit savepoint reached.
        upgrade_block_savepoint(true, 2025062007, 'course_audit');
    }

    if ($oldversion < 2025062008) {
        // Remove scope field from block_course_audit_check table if it exists (from earlier versions).
        $table = new xmldb_table('block_course_audit_check');
        
        $field = new xmldb_field('scope');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Course audit savepoint reached.
        upgrade_block_savepoint(true, 2025062008, 'course_audit');
    }

    return true;
}
