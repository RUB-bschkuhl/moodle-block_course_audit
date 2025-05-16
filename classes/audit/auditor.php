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

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_manager.php');
require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_interface.php'); // Include interface for type hinting

use block_course_audit\rules\rule_manager;
use block_course_audit\rules\rule_interface;
use stdClass;

class auditor
{
    /**
     * Return section data as pages for the tour, raw rule results, and action details map.
     *
     * @param stdClass $course The course object
     * @return array An array containing three keys: 'tour_steps', 'raw_results', and 'action_details_map'.
     *               'tour_steps': Array formatted for creating tour steps.
     *               'raw_results': Array of all individual rule check results.
     *               'action_details_map': Map of identifiers to action button details.
     */
    public function get_audit_results(stdClass $course): array
    {
        global $OUTPUT;

        $tour_steps = [];
        $raw_results = [];
        $action_details_map = []; // Initialize the map

        $courseformat = course_get_format($course);
        $sections = $courseformat->get_sections();

        //TODO run audit_course first, audit course should only run rule with target_type course
        $course_results = $this->audit_course($course->id);
        foreach ($course_results as $result) {
            //TODO switch for section mod course
            $raw_results[] = $result; // Store raw result regardless of status

            if ($result->rule_category == "action" && $result->action_button_details && $result->status == false) {
                $map_key = 'section_' . $sectionobj->id . '_' . $result->rule_key;
                $action_details_map[$map_key] = $result->action_button_details;
            }

            // Only create tour steps for failed checks, as before
            if (!$result->status) {
                $section_template_data = [
                    'section_id' => $sectionobj->id,
                    'section_name' => get_section_name($course, $sectionobj),
                    'section_number' => $sectionobj->section,
                    'course_id' => $course->id,
                    'course_shortname' => $course->shortname,
                    'rule_result' => $result,
                ];

                $tour_steps[] = [
                    'type' => 'course',
                    'title' => $result->rule_name . ': ' . $result->rule_category,
                    'number' => $sectionobj->section,
                    'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $section_template_data)
                ];
            }
        }

        foreach ($sections as $sectionnum => $sectionobj) {
            // Get all raw results first
            $section_results = $this->audit_section($sectionobj->id); // Assume this returns raw results

            foreach ($section_results as $result) {
                $raw_results[] = $result; // Store raw result regardless of status

                if ($result->rule_category == "action" && $result->action_button_details && !$result->status) {
                    foreach ($result->action_button_details as $detail) {
                        $action_details_map[$detail->mapkey] = $detail;
                    }
                }

                // Only create tour steps for failed checks, as before
                if (!$result->status) {
                    switch ($result->rule_target) {
                        case "section":
                            $section_template_data = [
                                'section_id' => $sectionobj->id,
                                'section_name' => get_section_name($course, $sectionobj),
                                'section_number' => $sectionobj->section,
                                'course_id' => $course->id,
                                'course_shortname' => $course->shortname,
                                'rule_result' => $result,
                            ];

                            $tour_steps[] = [
                                'type' => 'section',
                                'title' => $result->rule_name . ': ' . $result->rule_category,
                                'number' => $sectionobj->section,
                                'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $section_template_data)
                            ];
                            break;
                        case "mod":
                            //TODO
                            $section_template_data = [
                                'section_id' => $sectionobj->id,
                                'section_name' => get_section_name($course, $sectionobj),
                                'section_number' => $sectionobj->section,
                                'course_id' => $course->id,
                                'course_shortname' => $course->shortname,
                                'rule_result' => $result,
                            ];

                            $tour_steps[] = [
                                'type' => 'section',
                                'title' => $result->rule_name . ': ' . $result->rule_category,
                                'number' => $sectionobj->section,
                                'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $section_template_data)
                            ];
                            break;
                            break;
                        case "course":
                            //TODO
                            $section_template_data = [
                                'section_id' => $sectionobj->id,
                                'section_name' => get_section_name($course, $sectionobj),
                                'section_number' => $sectionobj->section,
                                'course_id' => $course->id,
                                'course_shortname' => $course->shortname,
                                'rule_result' => $result,
                            ];

                            $tour_steps[] = [
                                'type' => 'section',
                                'title' => $result->rule_name . ': ' . $result->rule_category,
                                'number' => $sectionobj->section,
                                'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $section_template_data)
                            ];
                            break;
                            break;
                    }
                }
            }
        }

        return [
            'tour_steps' => $tour_steps,
            'raw_results' => $raw_results,
            'action_details_map' => $action_details_map // Return the map
        ];
    }

    /**
     * Analyse the current course and return raw rule results.
     * 
     */
    public function audit_course(int $courseid): array
    {
        global $CFG, $DB;

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        return [];
    }

    /**
     * Analyse the current section and return raw rule results.
     *
     * @param int $sectionid The ID of the section to audit.
     * @return array Raw results from rule checks for this section.
     */
    public function audit_section(int $sectionid): array
    {
        global $CFG, $DB;

        $section = $DB->get_record('course_sections', ['id' => $sectionid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $section->course], '*', MUST_EXIST);

        $modinfo = get_fast_modinfo($course);
        $section_modules = []; // Use a local variable

        if (isset($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                //  if (!$cm->uservisible) continue;
                $section_modules[] = $cm;
            }
        }
        // Attach modules to the section object for the rules
        $section->modules = $section_modules;

        $rulemanager = new rule_manager();
        $hintResults_section = $rulemanager->run_rules($section, $course, 'hint', 'section');
        $actionResults_section = $rulemanager->run_rules($section, $course, 'action', 'section');
        $hintResults_mod = [];
        $actionResults_mod = [];

        foreach ($section->modules as $module) {
            $hintResults_mod = array_merge($hintResults_mod, $rulemanager->run_rules($module, $course, 'hint', 'mod'));
            $actionResults_mod = array_merge($actionResults_mod, $rulemanager->run_rules($module, $course, 'action', 'mod'));
        }

        $allResults = array_merge($hintResults_section, $actionResults_section, $hintResults_mod, $actionResults_mod);

        return $allResults;
    }
}
