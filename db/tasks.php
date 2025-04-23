<?php
// moodle-405/blocks/course_audit/db/tasks.php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'block_course_audit\task\audit_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0', // Runs daily at midnight
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 0, // Enable the task by default
    ],
]; 