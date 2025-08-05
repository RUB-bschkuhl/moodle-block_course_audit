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
     * @return object Result object with status, messages, and applicable resolutions
     */
    public function execute(\stdClass $course): object
    {
        if (empty($this->checks)) {
            return $this->create_result(true, 'No checks defined for this rule', $course);
        }

        // Execute checks with logic operators
        $overall_result = $this->evaluate_checks_with_logic($course);

        $messages = [];
        $failed_checks = [];

        // Collect messages and failed checks
        foreach ($this->checks as $index => $check) {
            //TODO redundant checks
            $check_result = $check->evaluate($course);
            if (!$check_result->passed) {
                $failed_checks[] = $check;
                if (!empty($check_result->message)) {
                    $messages[] = $check_result->message;
                }
            }
        }

        $message = implode('; ', $messages);

        return $this->create_result($overall_result, $message, $target, $failed_checks);
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
        $overall_result = true;

        for ($i = 0; $i < count($this->checks); $i++) {
            $current_check = $this->checks[$i];

            // ToDo current_check check required fields

            //TODO current_check evaluate should return passed and message and he evaluated item instance and save it to the rule object
            $check_result = $current_check->evaluate($course)->passed;

            // Apply NOT operator if needed
            if ($current_check->not_check) {
                $check_result = !$check_result;
            }

            if ($i > 0) {
                $previous_check = $this->checks[$i - 1];
                // Apply the logic operator from the previous check
                $logic_operator = $previous_check->next_logic ?? 'AND';

                if ($logic_operator === 'OR') {
                    $overall_result = $overall_result || $check_result;
                } else { // Default to AND
                    $overall_result = $overall_result && $check_result;
                }
            } else {
                // For the first check, just set the overall result
                $overall_result = $check_result;
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
     * @param object $target The target object
     * @param array $failed_checks Array of failed checks
     * @return object Standardized result object
     */
    private function create_result(bool $passed, string $message, object $target, array $failed_checks = []): object
    {
        // Determine target type and ID based on target object
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

        return (object)[
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
    }

    /**
     * Determine target information from target object.
     *
     * @param object $target The target object
     * @return array Array with 'type' and 'id' keys
     */
    private function determine_target_info(object $target): array
    {
        // Check if it's a course object
        if (isset($target->category) && isset($target->fullname)) {
            return ['type' => 'course', 'id' => $target->id];
        }

        // Check if it's a section object
        if (isset($target->section) && isset($target->course)) {
            return ['type' => 'section', 'id' => $target->id];
        }

        // Check if it's a module object (course module)
        if (isset($target->module) && isset($target->course) && isset($target->modname)) {
            return ['type' => 'mod', 'id' => $target->id];
        }

        // Default fallback
        return ['type' => 'unknown', 'id' => $target->id ?? 0];
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
}
