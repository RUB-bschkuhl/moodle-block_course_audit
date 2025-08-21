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
 * Dynamic Rule class.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\dynamic;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/dynamic_check.php');
require_once(__DIR__ . '/dynamic_resolution.php');

/**
 * Class representing a complete dynamic rule with checks and resolutions.
 */
class dynamic_rule
{

    /** @var int Rule ID */
    public $id;

    /** @var string Rule name */
    public $name;

    /** @var string Rule description */
    public $description;

    /** @var int Creation timestamp */
    public $timecreated;

    /** @var int Modification timestamp */
    public $timemodified;

    /** @var int Created by user ID */
    public $createdby;

    /** @var dynamic_check[] Array of check objects */
    public $checks = [];

    /** @var dynamic_resolution[] Array of resolution objects */
    public $resolutions = [];

    /** @var array Array of precondition rule IDs */
    public $preconditions = [];

    /** @var array Array of evaluated check results with instances */
    public $evaluated_check_results = [];

    /**
     * Constructor.
     *
     * @param object $rule_data Rule data from the database loader
     */
    public function __construct(object $rule_data)
    {
        $this->id = $rule_data->id;
        $this->name = $rule_data->name;
        $this->description = $rule_data->description ?? '';
        $this->timecreated = $rule_data->timecreated ?? time();
        $this->timemodified = $rule_data->timemodified ?? time();
        $this->createdby = $rule_data->createdby ?? 0;

        // Create check objects
        if (isset($rule_data->checks) && is_array($rule_data->checks)) {
            foreach ($rule_data->checks as $check_data) {
                $this->checks[] = new dynamic_check($check_data);
            }
        }

        // Create resolution objects
        if (isset($rule_data->resolutions) && is_array($rule_data->resolutions)) {
            foreach ($rule_data->resolutions as $resolution_data) {
                $this->resolutions[] = new dynamic_resolution($resolution_data);
            }
        }

        // Store preconditions
        if (isset($rule_data->preconditions) && is_array($rule_data->preconditions)) {
            foreach ($rule_data->preconditions as $precondition) {
                $this->preconditions[] = $precondition->precondition_rule_id;
            }
        }
    }

    /**
     * Execute all checks for this rule and return the result.
     *
     * @param \stdClass $course The course object for context
     * @return array Array of result objects with status, messages, and applicable resolutions
     */
    public function execute(\stdClass $course): array
    {
        if (empty($this->checks)) {
            return $this->create_result(true, 'No checks defined for this rule');
        }

        // Execute checks with logic operators and store results
        $overall_result = $this->evaluate_checks_with_logic($course);

        $messages = [];
        $failed_checks = [];

        // Use the stored check results to avoid redundant evaluation
        foreach ($this->evaluated_check_results as $index => $check_result) {
            if (!$check_result->passed) {
                $failed_checks[] = $this->checks[$index];
                if (!empty($check_result->message)) {
                    $messages[] = $check_result->message;
                }
            }
        }

        $message = implode('; ', $messages);

        return $this->create_result($overall_result, $message);
    }

    /**
     * Evaluate all checks with AND/OR logic operators.
     *
     * @param \stdClass $course The course object
     * @return bool True if the overall check chain passes
     */
    private function evaluate_checks_with_logic(\stdClass $course): bool
    {
        if (empty($this->checks)) {
            return true;
        }

        // Clear previous evaluated results
        $this->evaluated_check_results = [];
        $overall_result = true;

        for ($i = 0; $i < count($this->checks); $i++) {
            $current_check = $this->checks[$i];

            // Validate required fields for current check, TODO add more required fields
            if (empty($current_check->source) || empty($current_check->check_type)) {
                continue;
            }

            // Evaluate the check and store the complete result including evaluated instance
            $check_result_obj = $current_check->evaluate($course);

            // Apply NOT operator if needed
            if ($current_check->not_check) {
                $check_result_obj->passed = !$check_result_obj->passed;
            }

            $this->evaluated_check_results[$i] = $check_result_obj;

            if ($i > 0) {
                $previous_check = $this->checks[$i - 1];
                // Apply the logic operator from the previous check
                $logic_operator = $previous_check->next_logic ?? 'AND';

                if ($logic_operator === 'OR') {
                    $overall_result = $overall_result || $check_result_obj->passed;
                } else { // Default to AND
                    $overall_result = $overall_result && $check_result_obj->passed;
                }
            } else {
                // For the first check, just set the overall result
                $overall_result = $check_result_obj->passed;
            }
        }

        return $overall_result;
    }

    /**
     * Get applicable resolutions for failed checks.
     *
     * @param array $failed_checks Array of failed check objects
     * @return dynamic_resolution[] Array of applicable resolutions
     */
    public function get_resolutions(array $failed_checks = []): array
    {
        // For now, return all resolutions
        // In the future, we could filter based on specific failed checks
        return $this->resolutions;
    }

    /**
     * Create a standardized result object.
     *
     * @param bool $passed Whether the rule passed
     * @param string $message Result message
     * @param object $course The target course
     * @param array $failed_checks Array of failed checks
     * @return array Standardized result object array
     */
    private function create_result(bool $passed, string $message): array
    {
        $results = [];

        $failed_checks = array_filter($this->evaluated_check_results, function ($result) {
            return !$result->passed;
        });

        foreach ($failed_checks as $failed_check) {
            $target = $failed_check->evaluated_instance;

            $target_info = $this->determine_target_info($target);

            // Get applicable resolutions if rule failed
            $applicable_resolutions = $passed ? [] : $this->get_resolutions($failed_checks);

            // Determine rule category based on resolutions
            $rule_category = $this->determine_rule_category($applicable_resolutions);

            // Generate action button details for action-type resolutions
            $action_button_details = [];
            if (!$passed && $rule_category === 'action') {
                foreach ($applicable_resolutions as $resolution) {
                    if ($resolution->type === 'action') {
                        $button_details = $resolution->generate_action_button($target);
                        if ($button_details) {
                            $action_button_details[] = $button_details;
                        }
                    }
                }
            }


            $result = (object)[
                'rule_name' => $this->name,
                'rule_category' => $rule_category,
                'status' => $passed,
                'messages' => $message,
                'rule_target' => $target_info['type'],
                'rule_target_id' => $target_info['id'],
                'action_button_details' => $action_button_details,
                'resolutions' => $applicable_resolutions,
                'rule_id' => $this->id
            ];

            $results[] = $result;
        }

        return $results;
    }

    /**
     * Determine target information from target object.
     *
     * @param object $target The target object
     * @return array Array with 'type' and 'id' keys
     */
    private function determine_target_info(object $target): array
    {
        if ($target instanceof \course_info) {
            return ['type' => 'course', 'id' => $target->id];
        } else if ($target instanceof \section_info) {
            return ['type' => 'section', 'id' => $target->id];
        } else  if (isset($target->cm) && $target->cm instanceof \cm_info) {
            return ['type' => 'mod', 'id' => $target->cm->id];
        } else {
            return ['type' => 'unknown', 'id' => $target->id ?? 0];
        }
    }

    /**
     * Determine rule category based on resolutions.
     *
     * @param array $resolutions Array of resolution objects
     * @return string Rule category ('hint', 'action', 'show')
     */
    private function determine_rule_category(array $resolutions): string
    {
        if (empty($resolutions)) {
            return 'hint'; // Default to hint if no resolutions
        }

        // Check if we have any action resolutions
        foreach ($resolutions as $resolution) {
            if ($resolution->type === 'action') {
                return 'action';
            }
        }

        // Check if we have any show resolutions
        foreach ($resolutions as $resolution) {
            if ($resolution->type === 'show') {
                return 'show';
            }
        }

        // Default to hint
        return 'hint';
    }

    /**
     * Get a human-readable description of this rule.
     *
     * @return string Rule description
     */
    public function get_description(): string
    {
        return $this->description ?: $this->name;
    }

    /**
     * Check if this rule has any preconditions.
     *
     * @return bool True if rule has preconditions
     */
    public function has_preconditions(): bool
    {
        return !empty($this->preconditions);
    }

    /**
     * Get the precondition rule IDs.
     *
     * @return array Array of rule IDs that must pass before this rule
     */
    public function get_preconditions(): array
    {
        return $this->preconditions;
    }

    /**
     * Get the evaluated check results with instances.
     *
     * @return array Array of check result objects with evaluated instances
     */
    public function get_evaluated_check_results(): array
    {
        return $this->evaluated_check_results;
    }

    /**
     * Get the evaluated instances from the check results.
     *
     * @return array Array of evaluated instance objects
     */
    public function get_evaluated_instances(): array
    {
        $instances = [];
        foreach ($this->evaluated_check_results as $check_result) {
            if (isset($check_result->evaluated_instance) && $check_result->evaluated_instance !== null) {
                $instances[] = $check_result->evaluated_instance;
            }
        }
        return $instances;
    }
}
