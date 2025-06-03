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
 * Rule Manager for the Course Audit block.
 *
 * This class is responsible for loading, interpreting, and evaluating
 * dynamic rules stored in the database.
 *
 * @package    block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_course_audit_rule_manager {

    protected $courseid;
    protected $context;

    /**
     * Constructor.
     *
     * @param int $courseid The ID of the course context for rule evaluation, if applicable.
     */
    public function __construct(int $courseid = 0) {
        global $DB, $COURSE;

        if ($courseid) {
            $this->courseid = $courseid;
            $this->context = context_course::instance($courseid);
        } else if (isset($COURSE->id)) {
            $this->courseid = $COURSE->id;
            $this->context = context_course::instance($COURSE->id);
        } else {
            $this->courseid = 0;
            $this->context = null;
        }
    }

    /**
     * Evaluates all rules within a specific rule set for the given course.
     *
     * @param int $rulesetid The ID of the rule set to evaluate.
     * @param int $targetcourseid The ID of the course to evaluate against.
     * @return array An array of evaluation results, detailing passes/fails and messages.
     */
    public function evaluate_rule_set(int $rulesetid, int $targetcourseid): array {
        global $DB;
        $results = [];

        $rules = $DB->get_records('block_course_audit_rules', ['rulesetid' => $rulesetid]);

        foreach ($rules as $rule) {
            $results[$rule->id] = $this->evaluate_rule($rule->id, $targetcourseid);
        }
        return $results;
    }

    /**
     * Evaluates a single rule for the given course.
     *
     * @param int $ruleid The ID of the rule to evaluate.
     * @param int $targetcourseid The ID of the course to evaluate against.
     * @return stdClass An object detailing the rule evaluation (pass/fail, messages, applicable actions).
     */
    public function evaluate_rule(int $ruleid, int $targetcourseid): stdClass {
        global $DB;

        $ruledefinition = $this->load_rule_definition($ruleid);
        if (!$ruledefinition) {
            $result = new stdClass();
            $result->passed = false; // Or perhaps a specific error state.
            $result->message = get_string('error_rule_not_found', 'block_course_audit', $ruleid);
            return $result;
        }

        // TODO: Core evaluation logic will go here.
        // This will involve iterating through condition chains and segments.

        $overallchainresult = true; // Placeholder.

        foreach ($ruledefinition->chains as $chainid => $chain) {
            $currentchainresult = $this->evaluate_condition_chain($chain, $targetcourseid);

            if ($chain->logical_operator_to_next === 'AND') {
                $overallchainresult = $overallchainresult && $currentchainresult;
            } else if ($chain->logical_operator_to_next === 'OR') {
                $overallchainresult = $overallchainresult || $currentchainresult;
                 // For OR, if already true, might short-circuit depending on exact logic desired.
            } else { // Last chain or only chain.
                $overallchainresult = $overallchainresult && $currentchainresult;
            }
            // If an AND chain fails, the whole rule might fail immediately.
            if ($chain->logical_operator_to_next === 'AND' && !$overallchainresult) {
                break;
            }
        }

        $result = new stdClass();
        $result->ruleid = $ruleid;
        $result->name = $ruledefinition->name;
        $result->passed = $overallchainresult; // This needs to be the actual evaluation result.
        $result->messages = [];
        $result->actions = [];

        if (!$result->passed) {
            if (!empty($ruledefinition->failure_hint_text)) {
                $result->messages[] = $ruledefinition->failure_hint_text;
            }
            $result->actions = $ruledefinition->actions;
        } else {
            // Optional: success message?
        }

        return $result;
    }

    /**
     * Loads the complete definition of a rule from the database.
     *
     * @param int $ruleid The ID of the rule.
     * @return stdClass|null The rule definition object or null if not found.
     */
    protected function load_rule_definition(int $ruleid): ?stdClass {
        global $DB;

        $rule = $DB->get_record('block_course_audit_rules', ['id' => $ruleid]);
        if (!$rule) {
            return null;
        }

        $rule->chains = $DB->get_records('block_course_audit_condition_chains', ['ruleid' => $ruleid], 'chain_order ASC');
        foreach ($rule->chains as $chainid => $chain) {
            $rule->chains[$chainid]->segments = $DB->get_records('block_course_audit_condition_segments', ['conditionchainid' => $chainid], 'segment_order ASC');
        }
        $rule->actions = $DB->get_records('block_course_audit_actions', ['ruleid' => $ruleid], 'action_order ASC');

        return $rule;
    }

    /**
     * Evaluates a single condition chain.
     *
     * @param stdClass $chain The condition chain object (with its segments).
     * @param int $targetcourseid The ID of the course to evaluate against.
     * @return bool True if the chain conditions are met, false otherwise.
     */
    protected function evaluate_condition_chain(stdClass $chain, int $targetcourseid): bool {
        // Placeholder for context tracking throughout the chain.
        // Current context will change as we go from Course -> Section -> Module -> Sub-element.
        $currenttargetcontext = (object)['type' => 'COURSE', 'id' => $targetcourseid, 'instance' => null];
        $allsegmentsmet = true;

        foreach ($chain->segments as $segment) {
            list($segmentpassed, $nexttargetcontext) = $this->evaluate_condition_segment($segment, $currenttargetcontext);
            if (!$segmentpassed) {
                $allsegmentsmet = false;
                break; // If one segment in the chain fails, the chain fails.
            }
            // Update context for the next segment.
            if ($nexttargetcontext) {
                $currenttargetcontext = $nexttargetcontext;
            }
        }
        return $allsegmentsmet;
    }

    /**
     * Evaluates a single condition segment against the current context.
     *
     * @param stdClass $segment The condition segment object.
     * @param stdClass $currenttargetcontext An object representing the current Moodle entity being evaluated
     *        (e.g., {type: 'COURSE', id: 123} or {type: 'MODULE', id: 456, instance: $cm}).
     * @return array [bool $passed, ?stdClass $nexttargetcontext]
     *         $passed: True if the segment condition is met.
     *         $nexttargetcontext: The context for the next segment if this one passed and identified a child.
     */
    protected function evaluate_condition_segment(stdClass $segment, stdClass $currenttargetcontext): array {
        // TODO: This is the core logic where individual checks happen.
        // Needs to handle $segment->target_type, $segment->check_type, etc.
        // Will involve fetching Moodle data (course settings, section details, module instances, module settings).

        $passed = true; // Placeholder.
        $nexttargetcontext = null; // Placeholder for the child context if HAS_CONTENT finds something.

        // Example structure for HAS_SETTING for a course fullname.
        // if ($currenttargetcontext->type === 'COURSE' && $segment->target_type === 'COURSE') {
        //     if ($segment->check_type === 'HAS_SETTING' && $segment->setting_name === 'fullname') {
        //         $course = $DB->get_record('course', ['id' => $currenttargetcontext->id]);
        //         if ($course) {
        //             // Implement comparison logic based on $segment->setting_operator and $segment->setting_expected_value
        //             // $passed = ($course->fullname === $segment->setting_expected_value);
        //         } else {
        //             $passed = false;
        //         }
        //     }
        // }

        // Example structure for HAS_CONTENT to find a section in a course.
        // if ($currenttargetcontext->type === 'COURSE' && $segment->target_type === 'COURSE') {
        //     if ($segment->check_type === 'HAS_CONTENT' && $segment->content_child_type === 'SECTION') {
        //         // Logic to check if the course has ANY section.
        //         // If found, $nexttargetcontext would be set for that section.
        //         // For now, let's assume it finds a generic section.
        //         // $sections = $DB->get_records('course_sections', ['course' => $currenttargetcontext->id]);
        //         // if (!empty($sections)) {
        //         //     $passed = true;
        //         //     // For simplicity, pick the first one. Real logic would need to handle "any" or specific sections.
        //         //     $firstsection = reset($sections);
        //         //     $nexttargetcontext = (object)['type' => 'SECTION', 'id' => $firstsection->id, 'courseid' => $currenttargetcontext->id];
        //         // } else {
        //         //     $passed = false;
        //         // }
        //     }
        // }

        return [$passed, $nexttargetcontext];
    }

    // TODO: Add helper methods for specific checks:
    // - check_course_setting($courseid, $settingname, $operator, $expectedvalue)
    // - check_section_setting($sectionid, $settingname, $operator, $expectedvalue)
    // - check_module_setting($cmid, $settingname, $operator, $expectedvalue)
    // - check_course_has_content_section(...)
    // - check_section_has_content_module($sectionid, $moduletype, ...)
    // - etc.
} 