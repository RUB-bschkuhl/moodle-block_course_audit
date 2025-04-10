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
 * Audit manager class.
 *
 * @package block_course_audit
 * @copyright 2024 Your Name <your.email@example.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_course_audit\audit;
defined('MOODLE_INTERNAL') || die();
class auditor
{
    /**
     * Return section data as pages for the tour and raw rule results for DB storage.
     *
     * @param object $course The course object
     * @return array An array containing two keys: 'tour_steps' and 'raw_results'.
     *               'tour_steps': Array formatted for creating tour steps.
     *               'raw_results': Array of all individual rule check results.
     */
    public function get_audit_results($course)
    {
        global $CFG, $DB, $OUTPUT;
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_manager.php'); // Ensure rule manager is available for summary

        // Initialize arrays
        $tour_steps = [];
        $raw_results = [];
        $courseformat = course_get_format($course);
        $sections = $courseformat->get_sections();
        $index = 0;

        // Create rule manager instance needed for summary generation
        $rulemanager = new \block_course_audit\rules\rule_manager();

        foreach ($sections as $sectionnum => $sectionobj) {
            // Get raw results for this section
            $section_results = $this->audit_section($sectionobj->id);
            $raw_results = array_merge($raw_results, $section_results);

            // Prepare data for template rendering (similar to previous logic)
            $section_template_data = [
                'section_id' => $sectionobj->id,
                'section_name' => get_section_name($course, $sectionobj),
                'section_number' => $sectionobj->section,
                'course_id' => $course->id,
                'course_shortname' => $course->shortname,
                // We need to categorize the results again for the template summary
                'activity_type_rules' => [
                    'results' => array_filter($section_results, function($res) { return $res['category'] === 'activity_type'; }), // Assuming results have a 'category' key
                    'stats' => $rulemanager->get_summary(array_filter($section_results, function($res) { return $res['category'] === 'activity_type'; })),
                    'title' => get_string('rules_activity_type_category', 'block_course_audit')
                ],
                'activity_flow_rules' => [
                    'results' => array_filter($section_results, function($res) { return $res['category'] === 'activity_flow'; }), // Assuming results have a 'category' key
                    'stats' => $rulemanager->get_summary(array_filter($section_results, function($res) { return $res['category'] === 'activity_flow'; })),
                    'title' => get_string('rules_activity_flow_category', 'block_course_audit')
                ],
                'overall_stats' => $rulemanager->get_summary($section_results)
            ];

            // Create the tour step entry
            $tour_steps[] = [
                'type' => 'section', // Identifies the target type for the step
                'title' => get_string('structure_title', 'block_course_audit') . " - " . $section_template_data['section_name'], // More specific title
                'number' => $sectionobj->section, // Corresponds to #section-<number> ID
                'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_results', $section_template_data)
            ];
            $index++;
        }

        // Return both the steps and the raw results
        return [
            'tour_steps' => $tour_steps,
            'raw_results' => $raw_results
        ];
    }

    /**
     * Analyse the current section and return raw rule results.
     *
     * @return string The block HTML.
     * @param string $visiblename localised
     *
     */
    public function audit_section($sectionid)
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
                $module = new \stdClass();
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
        // Run activity flow rules
        $activityFlowResults = $rulemanager->run_rules($section, $course, 'activity_flow');
        // Combine results
        $allResults = array_merge($activityTypeResults, $activityFlowResults);

        // Return the raw results directly
        // The caller (get_audit_results) will handle formatting for display and DB storage.
        return $allResults;

        /* --- Previous code for template data preparation (removed) ---
        $activityTypeStats = $rulemanager->get_summary($activityTypeResults);
        $activityFlowStats = $rulemanager->get_summary($activityFlowResults);
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
        */
    }
}
