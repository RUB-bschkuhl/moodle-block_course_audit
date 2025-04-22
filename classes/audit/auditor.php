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
        require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_manager.php');

        $tour_steps = [];
        $raw_results = [];
        $courseformat = course_get_format($course);
        $sections = $courseformat->get_sections();
        $index = 0;

        $rulemanager = new \block_course_audit\rules\rule_manager();

        foreach ($sections as $sectionnum => $sectionobj) {
            $section_results = $this->audit_section($sectionobj->id);
            $raw_results = array_merge($raw_results, $section_results);

            $section_template_data = [
                'section_id' => $sectionobj->id,
                'section_name' => get_section_name($course, $sectionobj),
                'section_number' => $sectionobj->section,
                'course_id' => $course->id,
                'course_shortname' => $course->shortname,
                'rules' => [
                    'results' => $section_results, 
                    'stats' => $rulemanager->get_summary($section_results),
                ]
            ];

            $tour_steps[] = [
                'type' => 'section',
                'title' => get_string('structure_title', 'block_course_audit') . " - " . $section_template_data['section_name'],
                'number' => $sectionobj->section,
                'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_results', $section_template_data)
            ];
            $index++;
        }

        return [
            'tour_steps' => $tour_steps,
            'raw_results' => $raw_results
        ];
    }

    /**
     * Analyse the current section and return raw rule results.
     *
     * @param int $sectionid The ID of the section to audit.
     * @return array Raw results from rule checks for this section.
     */
    public function audit_section($sectionid)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_manager.php');

        $section = $DB->get_record('course_sections', ['id' => $sectionid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $section->course], '*', MUST_EXIST);

        $modinfo = get_fast_modinfo($course);
        $section->modules = [];

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

        $rulemanager = new \block_course_audit\rules\rule_manager();
        $hintResults = $rulemanager->run_rules($section, $course, 'hint');
        $actionResults = $rulemanager->run_rules($section, $course, 'action');

        $allResults = array_merge($hintResults, $actionResults);

        return $allResults;
    }
}
