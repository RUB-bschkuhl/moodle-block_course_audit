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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External functions and service definitions for the course audit block.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'block_course_audit_create_tour' => array(
        'classname'     => 'block_course_audit\external\create_tour',
        'methodname'    => 'execute',
        'description'   => 'Create a course audit tour',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'block/course_audit:view'
    ),
    'block_course_audit_get_summary' => array(
        'classname'     => 'block_course_audit\external\get_summary',
        'methodname'    => 'execute',
        'description'   => 'Get all audit results for a specified tour',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'block/course_audit:view'
    ),
    'block_course_audit_manage_labels' => array(
        'classname'     => 'block_course_audit\external\manage_labels',
        'methodname'    => 'execute',
        'description'   => 'Adds a new label module to a specific course section.',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'block/course_audit:view'
    ),
    'block_course_audit_manage_quiz' => array(
        'classname'     => 'block_course_audit\external\manage_quiz',
        'methodname'    => 'execute',
        'description'   => 'Manage a quiz in a course',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'block/course_audit:view'
    ),
    'block_course_audit_enable_repeatable' => array(
        'classname'     => 'block_course_audit\external\enable_repeatable',
        'methodname'    => 'execute',
        'description'   => 'Sets the attempts for a quiz to 0 (unlimited).',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'moodle/course:manageactivities' // Or a more specific capability like 'block/course_audit:managequizsettings'
    )
);

$services = array(
    'Course Audit Services' => array(
        'functions' => ['block_course_audit_create_tour', 'block_course_audit_get_summary', 'block_course_audit_manage_labels', 'block_course_audit_manage_quiz', 'block_course_audit_enable_repeatable'],
        'restrictedusers' => 0,
        'enabled' => 1
    )
); 