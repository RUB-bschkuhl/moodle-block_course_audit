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
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\audit;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/static/rule_manager.php');
require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/static/rule_interface.php'); // Include interface for type hinting
require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/dynamic/dynamic_rule_executor.php');

use block_course_audit\rules\static\rule_manager;
use block_course_audit\rules\dynamic\dynamic_rule_executor;
use stdClass;

class auditor
{
    /** @var dynamic_rule_executor Dynamic rule executor instance */
    private $dynamic_executor;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->dynamic_executor = new dynamic_rule_executor();
    }

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

        // Execute all dynamic rules for the course
        $dynamic_results = $this->audit_dynamic_rules($course);
        
        // Process all dynamic rule results
        foreach ($dynamic_results as $result) {
            $raw_results[] = $result;

            // Handle action button details for dynamic rules
            if ($result->rule_category == "action" && !empty($result->action_button_details) && $result->status == false) {
                $action_buttons = is_array($result->action_button_details) && !isset($result->action_button_details['mapkey']) 
                    ? $result->action_button_details : [$result->action_button_details];
                
                foreach ($action_buttons as $button_detail) {
                    if (is_array($button_detail) && !empty($button_detail['mapkey'])) {
                        $action_details_map[$button_detail['mapkey']] = $button_detail;
                    }
                }
            }

            // Create tour steps for failed dynamic rules
            if (!$result->status) {
                $this->add_tour_step_for_result($result, $course, $sections, $tour_steps, $OUTPUT);
            }
        }

        // Execute static course-level rules for backward compatibility
        // $static_course_results = $this->audit_course($course->id);
        // foreach ($static_course_results as $result) {
        //     $raw_results[] = $result;

        //     if ($result->rule_category == "action" && !empty($result->action_button_details) && isset($result->action_button_details['mapkey']) && $result->status == false) {
        //         $action_details_map[$result->action_button_details['mapkey']] = $result->action_button_details;
        //     }

        //     if (!$result->status) {
        //         $this->add_tour_step_for_result($result, $course, $sections, $tour_steps, $OUTPUT);
        //     }
        // }

        // foreach ($sections as $sectionnum => $sectionobj) {
        //     // Get all raw results first
        //     $section_results = $this->audit_section($sectionobj->id); // Assume this returns raw results

        //     foreach ($section_results as $result) {
        //         $raw_results[] = $result; // Store raw result regardless of status

        //         if ($result->rule_category == "action" && !empty($result->action_button_details) && $result->status == false) {
        //             $action_buttons = is_array(reset($result->action_button_details)) && is_string(key(reset($result->action_button_details))) ? $result->action_button_details : [$result->action_button_details];
        //             foreach ($action_buttons as $button_detail) {
        //                 if (is_array($button_detail) && !empty($button_detail['mapkey'])) {
        //                     $action_details_map[$button_detail['mapkey']] = $button_detail;
        //                 }
        //             }
        //         }

        //         // Only create tour steps for failed checks, as before
        //         if (!$result->status) {
        //             switch ($result->rule_target) {
        //                 case "section":
        //                     $section_template_data = [
        //                         'section_id' => $sectionobj->id,
        //                         'section_name' => get_section_name($course, $sectionobj),
        //                         'section_number' => $sectionobj->section,
        //                         'course_id' => $course->id,
        //                         'course_shortname' => $course->shortname,
        //                         'rule_result' => $result,
        //                     ];

        //                     $tour_steps[] = [
        //                         'type' => 'section',
        //                         'title' => $result->rule_name,
        //                         'number' => $sectionobj->section,
        //                         'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $section_template_data)
        //                     ];
        //                     break;
        //                 case "mod":
        //                     //TODO
        //                     $section_template_data = [
        //                         'section_id' => $sectionobj->id,
        //                         'section_name' => get_section_name($course, $sectionobj),
        //                         'section_number' => $sectionobj->section,
        //                         'course_id' => $course->id,
        //                         'course_shortname' => $course->shortname,
        //                         'rule_result' => $result,
        //                     ];

        //                     $tour_steps[] = [
        //                         'type' => 'mod',
        //                         'title' => $result->rule_name,
        //                         'number' => $result->rule_target_id,
        //                         'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $section_template_data)
        //                     ];
        //                     break;
        //                 case "course":
        //                     //TODO
        //                     $section_template_data = [
        //                         'section_id' => $sectionobj->id,
        //                         'section_name' => get_section_name($course, $sectionobj),
        //                         'section_number' => $sectionobj->section,
        //                         'course_id' => $course->id,
        //                         'course_shortname' => $course->shortname,
        //                         'rule_result' => $result,
        //                     ];

        //                     $tour_steps[] = [
        //                         'type' => 'course',
        //                         'title' => $result->rule_name,
        //                         'number' => $sectionobj->section,
        //                         'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $section_template_data)
        //                     ];
        //                     break;
        //             }
        //         }
        //     }
        // }

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
        global $DB;

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

    /**
     * Execute all dynamic rules for a course.
     * This method runs all available dynamic rules regardless of target type.
     * The target distinction is made in the rule results themselves.
     *
     * @param stdClass $course The course object
     * @return array All dynamic rule execution results
     */
    public function audit_dynamic_rules(stdClass $course): array
    {
        try {
            return $this->dynamic_executor->execute_all_rules($course);
        } catch (\Exception $e) {
            debugging('Error executing dynamic rules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            
            // Return error result if something goes wrong
            return [(object)[
                'rule_name' => 'Dynamic Rules Error',
                'rule_category' => 'hint',
                'status' => false,
                'messages' => 'Error executing dynamic rules: ' . $e->getMessage(),
                'rule_target' => 'course',
                'rule_target_id' => $course->id,
                'action_button_details' => [],
                'resolutions' => [],
                'rule_id' => 0
                         ]];
         }
     }

    /**
     * Add a tour step for a failed rule result.
     *
     * @param object $result Rule result object
     * @param stdClass $course Course object
     * @param array $sections Course sections
     * @param array &$tour_steps Tour steps array to append to
     * @param object $OUTPUT Moodle output renderer
     */
    private function add_tour_step_for_result(object $result, stdClass $course, array $sections, array &$tour_steps, object $OUTPUT): void {
        switch ($result->rule_target) {
            case "course":
                $template_data = [
                    'section_id' => null,
                    'section_name' => get_string('courselevel', 'block_course_audit'),
                    'section_number' => null,
                    'course_id' => $course->id,
                    'course_shortname' => $course->shortname,
                    'rule_result' => $result,
                ];

                $tour_steps[] = [
                    'type' => 'course',
                    'title' => $result->rule_name . ': ' . $result->rule_category,
                    'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $template_data)
                ];
                break;

            case "section":
                // Find the section object
                $section_obj = null;
                foreach ($sections as $section) {
                    if ($section->id == $result->rule_target_id) {
                        $section_obj = $section;
                        break;
                    }
                }

                if ($section_obj) {
                    $template_data = [
                        'section_id' => $section_obj->id,
                        'section_name' => get_section_name($course, $section_obj),
                        'section_number' => $section_obj->section,
                        'course_id' => $course->id,
                        'course_shortname' => $course->shortname,
                        'rule_result' => $result,
                    ];

                    $tour_steps[] = [
                        'type' => 'section',
                        'title' => $result->rule_name,
                        'number' => $section_obj->section,
                        'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $template_data)
                    ];
                }
                break;

            case "mod":
                // For module rules, we need to find which section it belongs to
                $section_obj = $this->find_section_for_module($result->rule_target_id, $sections, $course);
                
                if ($section_obj) {
                    $template_data = [
                        'section_id' => $section_obj->id,
                        'section_name' => get_section_name($course, $section_obj),
                        'section_number' => $section_obj->section,
                        'course_id' => $course->id,
                        'course_shortname' => $course->shortname,
                        'rule_result' => $result,
                    ];

                    $tour_steps[] = [
                        'type' => 'mod',
                        'title' => $result->rule_name,
                        'number' => $result->rule_target_id,
                        'content' => $OUTPUT->render_from_template('block_course_audit/rules/rule_result', $template_data)
                    ];
                }
                break;
        }
    }

    /**
     * Find the section that contains a specific module.
     *
     * @param int $module_id Module ID to find
     * @param array $sections Course sections
     * @param stdClass $course Course object
     * @return object|null Section object or null if not found
     */
    private function find_section_for_module(int $module_id, array $sections, stdClass $course): ?object {
        $modinfo = get_fast_modinfo($course);
        
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->id == $module_id) {
                // Find the section this module belongs to
                foreach ($sections as $section) {
                    if ($section->section == $cm->sectionnum) {
                        return $section;
                    }
                }
            }
        }
        
        return null;
    }
}
