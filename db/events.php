<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => \tool_usertours\event\tour_ended::class,
        'callback' => 'block_course_audit\observer::tour_ended',
    ],
]; 