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

    return true;
}
