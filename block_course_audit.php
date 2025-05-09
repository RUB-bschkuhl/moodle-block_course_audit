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
 * Block definition class for the block_course_audit plugin.
 *
 * @package block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_course_audit\audit\auditor as auditor;

class block_course_audit extends block_base
{

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('pluginname', 'block_course_audit');
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     * @param string $visiblename localised
     *
     */
    public function get_content()
    {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $course = $this->page->course;

        // Structure for the template
        $data = [
            'pre' => ['start_hint' => get_string('start_hint', 'block_course_audit')],
            'tour_data' => [],
            'wrap_data' => [
                [
                    'type' => 'disclaimer',
                    'title' => get_string('disclaimer_title', 'block_course_audit'),
                    'content' => $OUTPUT->render_from_template('block_course_audit/block/disclaimer', [
                        'wiki_url' => new moodle_url('/blocks/course_audit/wiki.php')
                    ]),
                    'button_done' => get_string('disclaimer_button', 'block_course_audit'),
                    'button_id' => 'audit-start'
                ],
            ]
        ];

        // $auditor = new auditor();
        // $auditData = $auditor->get_audit_results($course); // Assume returns ['tour_steps' => [], 'raw_results' => []]
        // $data['tour_data'] = $auditData['tour_steps'];

        $this->content->text = $OUTPUT->render_from_template('block_course_audit/main', $data);

        // Return empty content if user cannot update the course, although button might be hidden by JS/template
        if (!has_capability('moodle/course:update', $this->context)) {
             $this->content->text = ''; 
        }

        return $this->content;
    }

    /**
     * Add required JavaScript.
     *
     * @return void
     */
    public function get_required_javascript()
    {
        global $PAGE;

        parent::get_required_javascript();

        if ($this->page->course->id !== SITEID) {
            $PAGE->requires->js_call_amd('block_course_audit/tour_creator', 'init', [$this->page->course->id]);
        }
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array 
     */
    public function applicable_formats()
    {
        return [
            'course-view' => true,
        ];
    }
}
