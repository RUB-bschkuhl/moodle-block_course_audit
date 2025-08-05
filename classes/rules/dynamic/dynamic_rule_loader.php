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
 * Dynamic Rule Loader class.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\dynamic;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for loading dynamic rules from the database.
 */
class dynamic_rule_loader {

    /**
     * Load all active rules from the database.
     *
     * @return array Array of rule objects with their checks and resolutions
     */
    public function load_all_rules(): array {
        global $DB;

        $rules = [];
        
        // Get all rules ordered by ID
        $rule_records = $DB->get_records('block_course_audit_rule', null, 'id ASC');
        
        foreach ($rule_records as $rule_record) {
            $rule = $this->load_rule_by_id($rule_record->id);
            if ($rule) {
                $rules[] = $rule;
            }
        }
        
        return $rules;
    }

    /**
     * Load rules for a specific target type.
     *
     * @param string $target_type The target type (course, section, mod, etc.)
     * @return array Array of rule objects applicable to the target type
     */
    public function load_rules_for_target(string $target_type): array {
        global $DB;

        $rules = [];
        
        // Get rules that have checks targeting the specified type
        $sql = "SELECT DISTINCT r.* 
                FROM {block_course_audit_rule} r
                JOIN {block_course_audit_check} c ON c.rule_id = r.id
                WHERE c.source = :target_type
                ORDER BY r.id ASC";
        
        $rule_records = $DB->get_records_sql($sql, ['target_type' => $target_type]);
        
        foreach ($rule_records as $rule_record) {
            $rule = $this->load_rule_by_id($rule_record->id);
            if ($rule) {
                $rules[] = $rule;
            }
        }
        
        return $rules;
    }

    /**
     * Load a specific rule by ID with all its checks and resolutions.
     *
     * @param int $rule_id The rule ID
     * @return object|null Rule object with checks and resolutions, or null if not found
     */
    public function load_rule_by_id(int $rule_id): ?object {
        global $DB;

        // Get the rule record
        $rule = $DB->get_record('block_course_audit_rule', ['id' => $rule_id]);
        if (!$rule) {
            return null;
        }

        // Load checks for this rule
        $checks = $DB->get_records('block_course_audit_check', 
            ['rule_id' => $rule_id], 
            'sort_order ASC, id ASC'
        );

        // Load resolutions for this rule
        $resolutions = $DB->get_records('block_course_audit_resolution', 
            ['rule_id' => $rule_id], 
            'id ASC'
        );

        // Load preconditions for this rule
        $preconditions = $DB->get_records('block_course_audit_precond', 
            ['rule_id' => $rule_id], 
            'id ASC'
        );

        // Build the complete rule object
        $complete_rule = (object)[
            'id' => $rule->id,
            'name' => $rule->rule_name,
            'description' => $rule->rule_description,
            'timecreated' => $rule->timecreated,
            'timemodified' => $rule->timemodified,
            'createdby' => $rule->createdby,
            'checks' => array_values($checks), // Convert to indexed array
            'resolutions' => array_values($resolutions), // Convert to indexed array
            'preconditions' => array_values($preconditions) // Convert to indexed array
        ];

        return $complete_rule;
    }

    /**
     * Check if rule preconditions are satisfied.
     *
     * @param int $rule_id The rule ID to check preconditions for
     * @param array $passed_rules Array of rule IDs that have already passed
     * @return bool True if all preconditions are satisfied
     */
    public function check_rule_preconditions(int $rule_id, array $passed_rules): bool {
        global $DB;

        // Get all preconditions for this rule
        $preconditions = $DB->get_records('block_course_audit_precond', 
            ['rule_id' => $rule_id]
        );

        // If no preconditions, rule can run
        if (empty($preconditions)) {
            return true;
        }

        // Check if all required precondition rules have passed
        foreach ($preconditions as $precondition) {
            if (!in_array($precondition->precondition_rule_id, $passed_rules)) {
                return false; // Required precondition not satisfied
            }
        }

        return true; // All preconditions satisfied
    }

    /**
     * Get rules organized by target type for efficient processing.
     *
     * @return array Associative array with target types as keys and rule arrays as values
     */
    public function get_rules_by_target_type(): array {
        global $DB;

        $organized_rules = [
            'course' => [],
            'section' => [],
            'quiz' => [],
            'assign' => [],
            'quiz_question' => []
        ];

        // Get all rules with their primary source types
        $sql = "SELECT DISTINCT r.*, c.source as primary_source
                FROM {block_course_audit_rule} r
                JOIN {block_course_audit_check} c ON c.rule_id = r.id
                WHERE c.sort_order = 0 OR c.id = (
                    SELECT MIN(c2.id) 
                    FROM {block_course_audit_check} c2 
                    WHERE c2.rule_id = r.id
                )
                ORDER BY r.id ASC";

        $rule_records = $DB->get_records_sql($sql);

        foreach ($rule_records as $rule_record) {
            $complete_rule = $this->load_rule_by_id($rule_record->id);
            if ($complete_rule) {
                $target_type = $rule_record->primary_source;
                if (isset($organized_rules[$target_type])) {
                    $organized_rules[$target_type][] = $complete_rule;
                }
            }
        }

        return $organized_rules;
    }
} 