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
 * Dynamic Rule Executor class.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\dynamic;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/dynamic_rule_loader.php');
require_once(__DIR__ . '/dynamic_rule.php');
require_once(__DIR__ . '/content_counters.php');

/**
 * Central rule execution engine for dynamic rules.
 */
class dynamic_rule_executor
{

    /** @var dynamic_rule_loader Rule loader instance */
    private $rule_loader;

    /** @var content_counters Content counting utility */
    private $content_counters;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->rule_loader = new dynamic_rule_loader();
        $this->content_counters = new content_counters();
    }

    /**
     * Execute all dynamic rules for a course and return results.
     *
     * @param \stdClass $course Course object
     * @return array Array of rule execution results
     */
    public function execute_all_rules(\stdClass $course): array
    {
        $results = [];

        try {
            // Load all rules organized by target type
            //TODO Load course rules based on settings in block instance
            $all_rule_data = $this->rule_loader->load_all_rules();

            // Execute course-level rules
            foreach ($all_rule_data as $rule_data) {
                $rule = new dynamic_rule($rule_data);

                // Check preconditions
                if ($this->check_preconditions($rule, $results)) {
                    $result = $rule->execute($course);
                    $results[] = $result;
                }
            }
        } catch (\Exception $e) {
            debugging('Error executing dynamic rules: ' . $e->getMessage(), DEBUG_DEVELOPER);

            // Return error result
            $results[] = (object)[
                'rule_name' => 'Dynamic Rules Error',
                'rule_category' => 'hint',
                'status' => false,
                'messages' => 'Error executing dynamic rules: ' . $e->getMessage(),
                'rule_target' => 'course',
                'rule_target_id' => $course->id,
                'action_button_details' => [],
                'resolutions' => [],
                'rule_id' => 0
            ];
        }

        return $results;
    }

    /**
     * Check if rule preconditions are met.
     *
     * @param dynamic_rule $rule Rule to check
     * @param array $executed_results Already executed rule results
     * @return bool True if preconditions are met
     */
    private function check_preconditions(dynamic_rule $rule, array $executed_results): bool
    {
        if (!$rule->has_preconditions()) {
            return true;
        }

        $precondition_ids = $rule->get_preconditions();
        $passed_rule_ids = [];

        // Collect IDs of rules that have passed
        foreach ($executed_results as $result) {
            if ($result->status && isset($result->rule_id)) {
                $passed_rule_ids[] = $result->rule_id;
            }
        }

        // Check if all precondition rules have passed
        foreach ($precondition_ids as $required_rule_id) {
            if (!in_array($required_rule_id, $passed_rule_ids)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all sections for a course.
     *
     * @param \stdClass $course Course object
     * @return array Array of section objects
     */
    private function get_course_sections(\stdClass $course): array
    {
        try {
            $modinfo = get_fast_modinfo($course);
            $sections = $modinfo->get_section_info_all();

            // Filter out section 0 (general section) for most rule processing
            return array_filter($sections, function ($section) {
                return $section->section > 0;
            });
        } catch (\Exception $e) {
            debugging('Error getting course sections: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    /**
     * Get all course modules for a course.
     *
     * @param \stdClass $course Course object
     * @return array Array of course module objects
     */
    private function get_course_modules(\stdClass $course): array
    {
        try {
            $modinfo = get_fast_modinfo($course);
            return array_values($modinfo->get_cms());
        } catch (\Exception $e) {
            debugging('Error getting course modules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    /**
     * Get modules of a specific type for a course.
     *
     * @param \stdClass $course Course object
     * @param string $module_type Module type (quiz, assign, etc.)
     * @return array Array of module instances with course module info
     */
    private function get_modules_by_type(\stdClass $course, string $module_type): array
    {
        global $DB;

        try {
            $modinfo = get_fast_modinfo($course);
            $modules = [];

            foreach ($modinfo->get_cms() as $cm) {
                if ($cm->modname === $module_type) {
                    // Get the actual module instance
                    $instance = $DB->get_record($module_type, ['id' => $cm->instance]);
                    if ($instance) {
                        $instance->cm = $cm; // Add course module info
                        $modules[] = $instance;
                    }
                }
            }

            return $modules;
        } catch (\Exception $e) {
            debugging("Error getting {$module_type} modules: " . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    /**
     * Get summary statistics for rule execution results.
     *
     * @param array $results Array of rule execution results
     * @return object Summary statistics
     */
    public function get_execution_summary(array $results): object
    {
        $total_rules = count($results);
        $passed_rules = 0;
        $failed_rules = 0;
        $action_rules = 0;
        $hint_rules = 0;
        $show_rules = 0;

        foreach ($results as $result) {
            if ($result->status) {
                $passed_rules++;
            } else {
                $failed_rules++;
            }

            switch ($result->rule_category) {
                case 'action':
                    $action_rules++;
                    break;
                case 'show':
                    $show_rules++;
                    break;
                case 'hint':
                default:
                    $hint_rules++;
                    break;
            }
        }

        return (object)[
            'total_rules' => $total_rules,
            'passed_rules' => $passed_rules,
            'failed_rules' => $failed_rules,
            'pass_rate' => $total_rules > 0 ? round(($passed_rules / $total_rules) * 100, 1) : 0,
            'action_rules' => $action_rules,
            'hint_rules' => $hint_rules,
            'show_rules' => $show_rules
        ];
    }

    /**
     * Filter results by category.
     *
     * @param array $results Array of rule execution results
     * @param string $category Category to filter by (hint, action, show)
     * @return array Filtered results
     */
    public function filter_results_by_category(array $results, string $category): array
    {
        return array_filter($results, function ($result) use ($category) {
            return $result->rule_category === $category;
        });
    }

    /**
     * Filter results by status.
     *
     * @param array $results Array of rule execution results
     * @param bool $status Status to filter by (true for passed, false for failed)
     * @return array Filtered results
     */
    public function filter_results_by_status(array $results, bool $status): array
    {
        return array_filter($results, function ($result) use ($status) {
            return $result->status === $status;
        });
    }
}
