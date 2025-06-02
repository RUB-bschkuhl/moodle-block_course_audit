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

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'block_course_audit_settings_heading',
        get_string('settings_heading', 'block_course_audit'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'block_course_audit/example_setting',
        get_string('example_setting_name', 'block_course_audit'),
        get_string('example_setting_desc', 'block_course_audit'),
        '',
        PARAM_TEXT
    ));

    // Link to manage rules page, only if user has the capability.
    // The $this->page->course->id is not directly available here in this script.
    // We need to ensure we are in a course context for this link to make sense.
    // Typically, block settings are edited at course or site level.
    // If this settings page is for the block instance in a course:
    if (!empty($this->instance) && $this->instance->pagetypepattern == 'course-view-*') {
        $courseid = $this->instance->pageid;
        $coursecontext = context_course::instance($courseid);
        if (has_capability('block/course_audit:managerules', $coursecontext)) {
            $url = new moodle_url('/blocks/course_audit/manage_rules.php', array('courseid' => $courseid));
            $settings->add(new admin_setting_heading(
                'block_course_audit_managerules_link_heading',
                get_string('managerules', 'block_course_audit'),
                format_text(
                    html_writer::link($url, get_string('managerules', 'block_course_audit')),
                    FORMAT_HTML,
                    array('trusted' => true)
                )
            ));
        }
    }
} 