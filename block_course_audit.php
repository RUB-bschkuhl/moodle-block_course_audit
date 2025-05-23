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

use block_course_audit\audit\auditor;

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
     * Gets the ID of the latest audit run for the given course.
     *
     * @param int $courseid The ID of the course.
     * @return int|false The auditrunid if an audit run is found, false otherwise.
     */
    private function get_latest_audit_run_id($courseid)
    {
        global $DB;

        $sql = "SELECT ca.id AS auditrunid
                  FROM {block_course_audit_tours} ca
                 WHERE ca.courseid = :courseid
              ORDER BY ca.timemodified DESC";

        $latest_audit_run = $DB->get_record_sql($sql, ['courseid' => $courseid], IGNORE_MULTIPLE);

        if ($latest_audit_run) {
            return (int)$latest_audit_run->auditrunid;
        }
        return false;
    }

    /**
     * Prepares the summary data to be rendered in the block.
     * This is similar to parts of classes/external/get_summary.php
     *
     * @param int $auditrunid The ID of the audit run from block_course_audit_tours.
     * @param int $courseid The ID of the course (to get course name, etc.).
     * @return string HTML content for the summary.
     */
    private function get_summary_block_content($auditrunid, $courseid)
    {
        global $DB, $OUTPUT;

        $results = $DB->get_records('block_course_audit_results', ['auditid' => $auditrunid], 'id ASC'); // Ordered by ID or timecreated
        $audit_run_details = $DB->get_record('block_course_audit_tours', ['id' => $auditrunid]);
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        if (empty($results) || !$audit_run_details) {
            // This case (audit run exists but no results) should ideally not happen if an audit was run.
            return $OUTPUT->notification(get_string('noauditresults', 'block_course_audit'));
        }

        $processed_results = [];
        $passed_count = 0;
        $failed_count = 0;
        $total_count = count($results);

        foreach ($results as $result) {
            $rulename_key = 'rule_' . $result->rulekey . '_name';
            $rulename_display = get_string_manager()->string_exists($rulename_key, 'block_course_audit')
                ? get_string($rulename_key, 'block_course_audit')
                : $result->rulekey;

            $messages = [];
            if (!empty($result->messages)) {
                $decoded_messages = json_decode($result->messages);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $messages = is_array($decoded_messages) ? $decoded_messages : [$decoded_messages];
                } else {
                    $messages = [$result->messages];
                }
            }

            $processed_results[] = [
                'rulekey' => $result->rulekey,
                'ruleNameDisplay' => $rulename_display,
                'status' => $result->status,
                'isTodo' => ($result->status == '0'),
                'isDone' => ($result->status == '1'),
                'messages' => $messages,
                'rule_category' => $result->rulecategory,
                'targettype' => $result->targettype,
                'targetid' => $result->targetid,
                // TODO Add action button details if needed for the summary template, similar to auditor.php                
                // 'action_button_details' => $this->get_action_details_for_rule_result($result) // Hypothetical method
            ];

            if ($result->status == '1') {
                $passed_count++;
            } else {
                $failed_count++;
            }
        }

        $template_data = [
            'results' => $processed_results,
            'hasResults' => $total_count > 0,
            'timecreated' => userdate($audit_run_details->timecreated, get_string('strftimedaydatetime', 'langconfig')),
            'rulecount' => $total_count,
            'passedcount' => $passed_count,
            'failedcount' => $failed_count,
            'coursename' => $course->fullname,
            'fromblock' => true, // Flag to indicate this is the block's direct summary view
            'canmanagecourse' => has_capability('moodle/course:manageactivities', $this->page->context) // For conditional buttons
        ];

        // TODO main template muss erweitert werden, entsprechend auch die template data. Wenn diese da ist soll summary im Block angezeigt werden, der button sonst
        return $template_data;
    }


    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content()
    {
        global $OUTPUT, $USER, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $courseid = $this->page->course->id;

        $latest_auditrunid = $this->get_latest_audit_run_id($courseid);

        if ($latest_auditrunid !== false) {
            $template_data = $this->get_summary_block_content($latest_auditrunid, $courseid);

            $data = [
                'pre' => ['start_hint' => get_string('start_hint', 'block_course_audit')],
                'wrap_data' => [
                    [
                        'type' => 'disclaimer',
                        'title' => get_string('disclaimer_title', 'block_course_audit'),
                        'content' => $OUTPUT->render_from_template('block_course_audit/block/disclaimer', [
                            'wiki_url' => new moodle_url('/blocks/course_audit/wiki.php')
                        ]),
                        'button_done' => get_string('disclaimer_button', 'block_course_audit')
                    ],
                ],
                'summary_data' => $template_data
            ];
            $this->content->text = $OUTPUT->render_from_template('block_course_audit/main', $data);
        } else {
            $data = [
                'pre' => ['start_hint' => get_string('start_hint', 'block_course_audit')],
                'wrap_data' => [
                    [
                        'type' => 'disclaimer',
                        'title' => get_string('disclaimer_title', 'block_course_audit'),
                        'content' => $OUTPUT->render_from_template('block_course_audit/block/disclaimer', [
                            'wiki_url' => new moodle_url('/blocks/course_audit/wiki.php')
                        ]),
                        'button_done' => get_string('disclaimer_button', 'block_course_audit')
                    ],
                ]
            ];
            $this->content->text = $OUTPUT->render_from_template('block_course_audit/main', $data);
        }

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

        $courseid = $this->page->course->id;
        if ($courseid != SITEID) {
            $PAGE->requires->js_call_amd('block_course_audit/tour_creator', 'init', [$courseid]);
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

    /**
     * Returns true if this block has a settings page.
     *
     * @return bool
     */
    public function has_config()
    {
        return true;
    }
}
