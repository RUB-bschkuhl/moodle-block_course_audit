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
 * Database update script for the course_audit block.
 *
 * @package block_course_audit
 * @copyright 2024 Your Name <your.email@example.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * xmldb_block_course_audit_upgrade is the function that upgrades
 * the block_course_audit database when it is necessary.
 *
 * @param int $oldversion The version we are upgrading from
 * @return bool result
 */
function xmldb_block_course_audit_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    $table = new xmldb_table('block_course_audit_tours');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('tourid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_key('tourid', XMLDB_KEY_FOREIGN, array('tourid'), 'tool_usertours_tours', array('id'));
    $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
    $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    upgrade_block_savepoint(true, 2025040801, 'course_audit');

    return true;
}
