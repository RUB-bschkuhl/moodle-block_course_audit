<?php
// moodle-405/blocks/course_audit/db/tasks.php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'block_course_audit\task\cleanup_audit_tours',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '1', // Runs daily at 1:00 AM
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 0, // Enable the task by default
    ],
]; 