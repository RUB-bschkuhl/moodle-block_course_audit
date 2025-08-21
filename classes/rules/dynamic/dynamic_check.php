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
 * Dynamic Check class.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\dynamic;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing an individual check condition.
 */
class dynamic_check {

    /** @var int Check ID */
    public $id;

    /** @var int Associated rule ID */
    public $rule_id;

    /** @var string Source type (course, section, mod, quiz, assign, etc.) */
    public $source;

    /** @var string Check type (setting, content_comp, content_count) */
    public $check_type;

    /** @var string Target field or setting to check */
    public $target;

    /** @var string Comparison operator (eq, neq, gt, gte, lt, lte, contains, not_contains) */
    public $comp;

    /** @var string Expected value to compare against */
    public $value;

    /** @var int Whether to negate this check (NOT operator) */
    public $not_check;

    /** @var string Content type for content checks */
    public $content_type;

    /** @var int Content comparison value for content_comp checks */
    public $content_comp;

    /** @var int Content count value for content_count checks */
    public $content_count;

    /** @var string Other source for additional context */
    public $other_source;

    /** @var string Source instance selection (first, last) */
    public $source_instance_first;

    /** @var string Source instance selection (first, last) */
    public $source_instance_last;

    /** @var string Logic operator for the next check (AND, OR) */
    public $next_logic;

    /** @var int Sort order */
    public $sortorder;

    /**
     * Constructor.
     *
     * @param object $check_data Check data from database
     */
    public function __construct(object $check_data) {
        $this->id = $check_data->id;
        $this->rule_id = $check_data->rule_id;
        $this->source = $check_data->source;
        $this->check_type = $check_data->check_type;
        $this->target = $check_data->target;
        $this->comp = $check_data->comp;
        $this->value = $check_data->value;
        $this->not_check = $check_data->not_check ?? 0;
        $this->content_type = $check_data->content_type ?? null;
        $this->content_comp = $check_data->content_comp ?? null;
        $this->content_count = $check_data->content_count ?? null;
        $this->other_source = $check_data->other_source ?? null;
        $this->source_instance_first = $check_data->source_instance_first ?? null;
        $this->source_instance_last = $check_data->source_instance_last ?? null;
        $this->next_logic = $check_data->next_logic ?? 'AND';
        $this->sortorder = $check_data->sortorder ?? 0;
    }

    /**
     * Evaluate this check against the target object.
     *
     * @param \stdClass $course The course context
     * @return object Result object with 'passed', 'message', 'evaluated_instance', and 'check_id' properties
     */
    public function evaluate(\stdClass $course): object {
        try {
            // Get the source data based on source type
            $source_data = $this->get_source_data($course);
            
            if ($source_data === null) {
                return $this->create_result(false, "Could not retrieve source data for {$this->source}", null);
            }

            // Perform the actual check based on check type
            switch ($this->check_type) {
                case 'setting':
                    return $this->evaluate_setting_check($source_data);
                case 'content_comp':
                    return $this->evaluate_content_comparison_check($source_data, $course);
                case 'content_count':
                    return $this->evaluate_content_count_check($source_data, $course);
                default:
                    return $this->create_result(false, "Unknown check type: {$this->check_type}", $source_data);
            }
        } catch (\Exception $e) {
            debugging('Error evaluating check: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return $this->create_result(false, "Error evaluating check: " . $e->getMessage(), null);
        }
    }

    /**
     * Get source data based on source type.
     *
     * @param \stdClass $course The course context
     * @return object|null Source data object or null if not found
     */
    private function get_source_data(\stdClass $course): ?object {
        // Handle "other source" functionality - get same type as target but other instances
        if ($this->other_source) {
            //TODO debug
            return $this->get_other_source_instances($this->source, $course);
        }

        switch ($this->source) {
            case 'course':
                return $course;
            case 'section':
                // Get sections from course
                $sections = get_fast_modinfo($course)->get_section_info_all();
                //TODO only gets 1 section even if multiple exist (reset(objects) is used), should test all sections instead
                return $this->apply_instance_selection($sections);
            default:
                // Handle specific module types (quiz, assign, etc.)
                if (in_array($this->source, ['quiz', 'assign', 'forum', 'lesson', 'scorm', 'url', 'resource'])) {
                    return $this->get_module_data($this->source, $course);
                }
                return null;
        }
    }

    /**
     * Get other instances of the same type as source for "other source" functionality.
     *
     * @param string $source_type The source type string
     * @param \stdClass $course The course context
     * @return object|null Other source instance or null if not found
     */
    private function get_other_source_instances(string $source_type, \stdClass $course): ?object {
        global $DB;

        // Get instances based on source type
        switch ($source_type) {
            case 'course':
                // Course context - no other courses available
                return null;
                
            case 'section':
                // Get all sections
                $sections = get_fast_modinfo($course)->get_section_info_all();
                return $this->apply_instance_selection($sections);
                
            default:
                // Handle specific module types (quiz, assign, etc.)
                if (in_array($source_type, ['quiz', 'assign', 'forum', 'lesson', 'scorm', 'url', 'resource'])) {
                    $modinfo = get_fast_modinfo($course);
                    $modules = [];
                    
                    foreach ($modinfo->get_cms() as $cm) {
                        if ($cm->modname === $source_type) {
                            // Get the actual module instance
                            $instance = $DB->get_record($source_type, ['id' => $cm->instance]);
                            if ($instance) {
                                $instance->cm = $cm; // Add course module info
                                $modules[] = $instance;
                            }
                        }
                    }
                    
                    return $this->apply_instance_selection($modules);
                }
                break;
        }
        
        return null;
    }

    /**
     * Get specific module data.
     *
     * @param string $modname Module name
     * @param \stdClass $course Course object
     * @return object|null Module data or null if not found
     */
    private function get_module_data(string $modname, \stdClass $course): ?object {
        global $DB;

        $modinfo = get_fast_modinfo($course);
        $modules = [];
        
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->modname === $modname) {
                // Get the actual module instance
                $instance = $DB->get_record($modname, ['id' => $cm->instance]);
                if ($instance) {
                    $instance->cm = $cm; // Add course module info
                    $modules[] = $instance;
                }
            }
        }

        return $this->apply_instance_selection($modules);
    }

    /**
     * Apply instance selection (first/last) to an array of objects.
     *
     * @param array $objects Array of objects to select from
     * @return object|null Selected object or null if array is empty
     */
    private function apply_instance_selection(array $objects): ?object {
        if (empty($objects)) {
            return null;
        }

        // Apply first instance selection (check for boolean true, not string)
        if ($this->source_instance_first == 1) {
            return reset($objects);
        }

        // Apply last instance selection (check for boolean true, not string)
        if ($this->source_instance_last == 1) {
            return end($objects);
        }

        // Default to first instance
        return reset($objects);
    }

    /**
     * Evaluate a setting check.
     *
     * @param object $source_data Source object to check
     * @return object Check result
     */
    private function evaluate_setting_check(object $source_data): object {
        // Get the target field value
        $actual_value = $this->get_field_value($source_data, $this->target);
        
        if ($actual_value === null) {
            return $this->create_result(false, "Field '{$this->target}' not found in source data", $source_data);
        }

        // Perform comparison
        $passed = $this->compare_values($actual_value, $this->value, $this->comp);
        
        $message = $passed ? 
            "Setting check passed: {$this->target} {$this->comp} {$this->value}" :
            "Setting check failed: {$this->target} is '{$actual_value}', expected {$this->comp} '{$this->value}'";

        return $this->create_result($passed, $message, $source_data);
    }

    /**
     * Evaluate a content comparison check.
     *
     * @param object $source_data Source object
     * @param \stdClass $course Course context
     * @return object Check result
     */
    private function evaluate_content_comparison_check(object $source_data, \stdClass $course): object {
        $actual_count = $this->count_content($source_data, $this->content_type, $course);
        $expected_count = (int)$this->content_comp;

        $passed = $this->compare_values($actual_count, $expected_count, $this->comp);

        $message = $passed ?
            "Content comparison passed: {$this->content_type} count {$this->comp} {$expected_count}" :
            "Content comparison failed: Found {$actual_count} {$this->content_type}, expected {$this->comp} {$expected_count}";

        return $this->create_result($passed, $message, $source_data);
    }

    /**
     * Evaluate a content count check.
     *
     * @param object $source_data Source object
     * @param \stdClass $course Course context
     * @return object Check result
     */
    private function evaluate_content_count_check(object $source_data, \stdClass $course): object {
        $actual_count = $this->count_content($source_data, $this->content_type, $course);
        $expected_count = (int)$this->content_count;

        $passed = $this->compare_values($actual_count, $expected_count, $this->comp);

        $message = $passed ?
            "Content count passed: {$this->content_type} count {$this->comp} {$expected_count}" :
            "Content count failed: Found {$actual_count} {$this->content_type}, expected {$this->comp} {$expected_count}";

        return $this->create_result($passed, $message, $source_data);
    }

    /**
     * Count content items of specified type.
     *
     * @param object $source_data Source object
     * @param string $content_type Type of content to count
     * @param \stdClass $course Course context
     * @return int Content count
     */
    private function count_content(object $source_data, string $content_type, \stdClass $course): int {
        require_once(__DIR__ . '/content_counters.php');
        $content_counters = new content_counters();

        // Determine the source type and delegate to appropriate counting method
        if (isset($source_data->category) && isset($source_data->fullname)) {
            // Course-level content counting
            return $content_counters->count_course_content($content_type, $course);
        } else if (isset($source_data->section) && isset($source_data->course)) {
            // Section-level content counting
            return $content_counters->count_section_content($content_type, $source_data, $course);
        } else if (isset($source_data->cm) && $source_data->cm->modname === 'quiz') {
            // Quiz-specific content counting
            return $content_counters->count_quiz_content($content_type, $source_data);
        } else if (isset($source_data->cm) && $source_data->cm->modname === 'assign') {
            // Assignment-specific content counting
            return $content_counters->count_assign_content($content_type, $source_data);
        } else {
            // Default fallback for unknown source types
            return 0;
        }
    }

    /**
     * Get field value from object, supporting nested properties.
     *
     * @param object $object Object to get value from
     * @param string $field Field name (supports dot notation)
     * @return mixed Field value or null if not found
     */
    private function get_field_value(object $object, string $field) {
        // Handle simple property access
        if (property_exists($object, $field)) {
            return $object->$field;
        }

        // Handle nested property access (e.g., 'cm.visible')
        if (strpos($field, '.') !== false) {
            $parts = explode('.', $field, 2);
            $first_part = $parts[0];
            $remaining = $parts[1];

            if (property_exists($object, $first_part) && is_object($object->$first_part)) {
                return $this->get_field_value($object->$first_part, $remaining);
            }
        }

        return null;
    }

    /**
     * Compare two values using the specified operator.
     *
     * @param mixed $actual Actual value
     * @param mixed $expected Expected value
     * @param string $operator Comparison operator
     * @return bool True if comparison passes
     */
    private function compare_values($actual, $expected, string $operator): bool {
        switch ($operator) {
            case 'eq':
                return $actual == $expected;
            case 'neq':
                return $actual != $expected;
            case 'gt':
                return $actual > $expected;
            case 'gte':
                return $actual >= $expected;
            case 'lt':
                return $actual < $expected;
            case 'lte':
                return $actual <= $expected;
            case 'contains':
                return stripos((string)$actual, (string)$expected) !== false;
            case 'not_contains':
                return stripos((string)$actual, (string)$expected) === false;
            default:
                return false;
        }
    }

    /**
     * Create a standardized result object.
     *
     * @param bool $passed Whether the check passed
     * @param string $message Result message
     * @param object|null $evaluated_instance The evaluated item instance
     * @return object Result object
     */
    private function create_result(bool $passed, string $message, ?object $evaluated_instance = null): object {
        return (object)[
            'passed' => $passed,
            'message' => $message,
            'evaluated_instance' => $evaluated_instance,
            'check_id' => $this->id
        ];
    }
} 