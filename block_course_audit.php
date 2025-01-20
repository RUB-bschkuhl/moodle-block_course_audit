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
 * Block definition class for the block_course_audit plugin.
 *
 * @package   block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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


        // Pages definieren
        $data = [
            'has_one' => false,
            'has_multiple' => false,
            'page_count' => 0,
            'pages' => [
                [
                    'type' => 'disclaimer',
                    'title' => get_string('disclaimer_title', 'block_course_audit'),
                    'content' => $OUTPUT->render_from_template('block_course_audit/disclaimer', [
                        'wiki_url' => new moodle_url('/blocks/course_audit/wiki.php')
                    ])
                ],
            ]
        ];

        // Get section pages and add them to the pages array
        $sectionPages = $this->get_section_pages($course);
        if (is_array($sectionPages)) {
            foreach ($sectionPages as $page) {
                $data['pages'][] = $page;
            }
        }

        // Add summary page
        $data['pages'][] = [
            'type' => 'summary',
            'title' => get_string('summary_title', 'block_course_audit'),
            'content' => $OUTPUT->render_from_template('block_course_audit/summary', ['wiki_url' => 'url'])
        ];

        $data['page_count'] = count($data['pages']);
        $data['has_one'] = count($data['pages']) > 0;
        $data['has_multiple'] = count($data['pages']) > 1;

        foreach ($data['pages'] as $index => &$page) {
            $page['page_number'] = $index + 1;
            $page['first'] = $page['page_number'] == 1;
        }
        unset($page);

        $this->content->text = $OUTPUT->render_from_template('block_course_audit/main', $data);

        if (!has_capability('moodle/course:update', $this->context)) {
            return $this->content;
        }
    }

    /**
     * Gets js.
     *
     * @return void
     */
    public function get_required_javascript()
    {
        global $PAGE;

        parent::get_required_javascript();
        $PAGE->requires->js_call_amd('block_course_audit/audit', 'init');
    }


    /**
     * Return section data as pages
     *
     * @param object $course The course object
     * @return array Array of section pages
     */
    private function get_section_pages($course)
    {
        global $CFG, $DB, $OUTPUT;
        require_once($CFG->dirroot . '/course/lib.php');

        // Initialize pages array
        $pages = [];

        // Log for debugging
        error_log('Getting section pages for course: ' . $course->id);

        $modinfo = get_fast_modinfo($course);
        $courseformat = course_get_format($course);
        $sections = $courseformat->get_sections();

        $index = 0;
        foreach ($sections as $sectionnum => $sectionid) {
 

            $rulecheckresults = $this->analyse_section($sectionid->id);

          
            $pages[] = [
                'type' => 'section',
                'title' => get_string('structure_title', 'block_course_audit'),
                'number' => $sectionnum,
                'content' => $OUTPUT->render_from_template('block_course_audit/rule_results', $rulecheckresults)
            ];
            $index++;
        }

        return $pages;
    }

   
    /**
     * Analyse the current section.
     *
     * @return string The block HTML.
     * @param string $visiblename localised
     * 
     */
    public function analyse_section($sectionid)
    {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_manager.php');

        // Get section info
        $section = $DB->get_record('course_sections', ['id' => $sectionid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $section->course], '*', MUST_EXIST);

        // Get modinfo for this course
        $modinfo = get_fast_modinfo($course);
        $section->modules = [];

        // Populate section with module data
        if (isset($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                if (!$cm->uservisible) continue;

                $module = new stdClass();
                $module->id = $cm->id;
                $module->name = $cm->get_formatted_name();
                $module->modname = $cm->modname;
                $module->availability = $cm->availability;

                $section->modules[] = $module;
            }
        }

        // Create rule manager and run rules
        $rulemanager = new \block_course_audit\rules\rule_manager();

        // Run activity type rules
        $activityTypeResults = $rulemanager->run_rules($section, $course, 'activity_type');
        $activityTypeStats = $rulemanager->get_summary($activityTypeResults);

        // Run activity flow rules
        $activityFlowResults = $rulemanager->run_rules($section, $course, 'activity_flow');
        $activityFlowStats = $rulemanager->get_summary($activityFlowResults);

        // Combine results
        $allResults = array_merge($activityTypeResults, $activityFlowResults);
        $overallStats = $rulemanager->get_summary($allResults);

        // Prepare data for output
        $data = [
            'section_id' => $sectionid,
            'section_name' => get_section_name($course, $section),
            'section_number' => $section->section,
            'course_id' => $course->id,
            'course_shortname' => $course->shortname,
            'activity_type_rules' => [
                'results' => $activityTypeResults,
                'stats' => $activityTypeStats,
                'title' => get_string('rules_activity_type_category', 'block_course_audit')
            ],
            'activity_flow_rules' => [
                'results' => $activityFlowResults,
                'stats' => $activityFlowStats,
                'title' => get_string('rules_activity_flow_category', 'block_course_audit')
            ],
            'overall_stats' => $overallStats
        ];

        return $data;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats()
    {
        return [
            'course-view' => true,
        ];
    }
}
