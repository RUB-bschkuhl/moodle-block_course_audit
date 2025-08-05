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
 * Action Validator class for validating action permissions.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\actions;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for validating action permissions and safety.
 */
class action_validator {

    /**
     * Validate if an action can be executed.
     *
     * @param array $params Action parameters
     * @param object $user User object (optional, defaults to current user)
     * @return array Validation result with success status and message
     */
    public function validate_action(array $params, ?object $user = null): array {
        global $USER;

        if ($user === null) {
            $user = $USER;
        }

        $action_type = $params['action'] ?? '';
        $target_type = $params['target_type'] ?? '';
        $target_id = $params['target_id'] ?? 0;

        if (empty($action_type) || empty($target_type) || empty($target_id)) {
            return [
                'success' => false,
                'message' => get_string('error_missing_parameters', 'block_course_audit')
            ];
        }

        // Check if action type is supported
        if (!$this->is_action_type_supported($action_type)) {
            return [
                'success' => false,
                'message' => get_string('error_unsupported_action', 'block_course_audit', $action_type)
            ];
        }

        // Validate target existence and permissions
        $target_validation = $this->validate_target($target_type, $target_id, $user);
        if (!$target_validation['success']) {
            return $target_validation;
        }

        // Validate specific action requirements
        return $this->validate_specific_action($action_type, $params, $user);
    }

    /**
     * Check if an action type is supported.
     *
     * @param string $action_type Action type
     * @return bool True if supported
     */
    private function is_action_type_supported(string $action_type): bool {
        $supported_actions = [
            'change_setting',
            'add_content',
            'create_backup'
        ];

        return in_array($action_type, $supported_actions);
    }

    /**
     * Validate target existence and basic permissions.
     *
     * @param string $target_type Target type
     * @param int $target_id Target ID
     * @param object $user User object
     * @return array Validation result
     */
    private function validate_target(string $target_type, int $target_id, object $user): array {
        global $DB;

        try {
            switch ($target_type) {
                case 'course':
                    $target = $DB->get_record('course', ['id' => $target_id], '*', MUST_EXIST);
                    $context = \context_course::instance($target_id);
                    
                    if (!has_capability('moodle/course:update', $context, $user)) {
                        return [
                            'success' => false,
                            'message' => get_string('error_no_permission_course', 'block_course_audit')
                        ];
                    }
                    break;

                case 'section':
                    $target = $DB->get_record('course_sections', ['id' => $target_id], '*', MUST_EXIST);
                    $context = \context_course::instance($target->course);
                    
                    if (!has_capability('moodle/course:update', $context, $user)) {
                        return [
                            'success' => false,
                            'message' => get_string('error_no_permission_section', 'block_course_audit')
                        ];
                    }
                    break;

                case 'mod':
                    $target = $DB->get_record('course_modules', ['id' => $target_id], '*', MUST_EXIST);
                    $context = \context_module::instance($target_id);
                    
                    if (!has_capability('moodle/course:manageactivities', $context, $user)) {
                        return [
                            'success' => false,
                            'message' => get_string('error_no_permission_module', 'block_course_audit')
                        ];
                    }
                    break;

                default:
                    return [
                        'success' => false,
                        'message' => get_string('error_unsupported_target', 'block_course_audit', $target_type)
                    ];
            }

            return [
                'success' => true,
                'target' => $target
            ];

        } catch (\dml_missing_record_exception $e) {
            return [
                'success' => false,
                'message' => get_string('error_target_not_found', 'block_course_audit', $target_type)
            ];
        } catch (\Exception $e) {
            debugging('Error validating target: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => get_string('error_validation_failed', 'block_course_audit')
            ];
        }
    }

    /**
     * Validate specific action requirements.
     *
     * @param string $action_type Action type
     * @param array $params Action parameters
     * @param object $user User object
     * @return array Validation result
     */
    private function validate_specific_action(string $action_type, array $params, object $user): array {
        switch ($action_type) {
            case 'change_setting':
                return $this->validate_setting_change($params, $user);
                
            case 'add_content':
                return $this->validate_content_addition($params, $user);
                
            case 'create_backup':
                return $this->validate_backup_creation($params, $user);
                
            default:
                return [
                    'success' => false,
                    'message' => get_string('error_unknown_action', 'block_course_audit', $action_type)
                ];
        }
    }

    /**
     * Validate setting change action.
     *
     * @param array $params Action parameters
     * @param object $user User object
     * @return array Validation result
     */
    private function validate_setting_change(array $params, object $user): array {
        $target_type = $params['target_type'] ?? '';
        $setting = $params['setting'] ?? '';
        $value = $params['value'] ?? '';

        if (empty($setting)) {
            return [
                'success' => false,
                'message' => get_string('error_missing_setting', 'block_course_audit')
            ];
        }

        // Use setting_changer to validate if the change is safe
        $setting_changer = new setting_changer();
        if (!$setting_changer->is_setting_change_safe($target_type, $setting, $value)) {
            return [
                'success' => false,
                'message' => get_string('error_unsafe_setting_change', 'block_course_audit', $setting)
            ];
        }

        return ['success' => true];
    }

    /**
     * Validate content addition action.
     *
     * @param array $params Action parameters
     * @param object $user User object
     * @return array Validation result
     */
    private function validate_content_addition(array $params, object $user): array {
        $target_type = $params['target_type'] ?? '';
        $content_type = $params['content_type'] ?? '';

        if (empty($content_type)) {
            return [
                'success' => false,
                'message' => get_string('error_missing_content_type', 'block_course_audit')
            ];
        }

        // Use content_adder to validate if the addition is allowed
        $content_adder = new content_adder();
        if (!$content_adder->is_content_addition_allowed($target_type, $content_type)) {
            return [
                'success' => false,
                'message' => get_string('error_content_addition_not_allowed', 'block_course_audit', $content_type)
            ];
        }

        return ['success' => true];
    }





    /**
     * Validate backup creation action.
     *
     * @param array $params Action parameters
     * @param object $user User object
     * @return array Validation result
     */
    private function validate_backup_creation(array $params, object $user): array {
        global $CFG;

        // Check if backup functionality is enabled
        if (empty($CFG->backup_auto_active)) {
            return [
                'success' => false,
                'message' => get_string('error_backup_disabled', 'block_course_audit')
            ];
        }

        $target_type = $params['target_type'] ?? '';
        $target_id = $params['target_id'] ?? 0;

        // Only allow course backups for now
        if ($target_type !== 'course') {
            return [
                'success' => false,
                'message' => get_string('error_backup_only_courses', 'block_course_audit')
            ];
        }

        // Check backup capability
        $context = \context_course::instance($target_id);
        if (!has_capability('moodle/backup:backupcourse', $context, $user)) {
            return [
                'success' => false,
                'message' => get_string('error_no_backup_permission', 'block_course_audit')
            ];
        }

        return ['success' => true];
    }



    /**
     * Check if user can execute any actions in the given context.
     *
     * @param string $target_type Target type
     * @param int $target_id Target ID
     * @param object $user User object (optional)
     * @return bool True if user can execute actions
     */
    public function can_execute_actions(string $target_type, int $target_id, ?object $user = null): bool {
        global $USER;

        if ($user === null) {
            $user = $USER;
        }

        $validation = $this->validate_target($target_type, $target_id, $user);
        return $validation['success'];
    }

    /**
     * Get list of actions available for a target.
     *
     * @param string $target_type Target type
     * @param int $target_id Target ID
     * @param object $user User object (optional)
     * @return array Array of available action types
     */
    public function get_available_actions(string $target_type, int $target_id, ?object $user = null): array {
        if (!$this->can_execute_actions($target_type, $target_id, $user)) {
            return [];
        }

        $all_actions = [
            'change_setting',
            'add_content',
            'create_backup'
        ];

        $available_actions = [];

        foreach ($all_actions as $action) {
            $test_params = [
                'action' => $action,
                'target_type' => $target_type,
                'target_id' => $target_id
            ];

            // Add minimal required parameters for validation
            switch ($action) {
                case 'change_setting':
                    $test_params['setting'] = 'visible';
                    $test_params['value'] = '1';
                    break;
                case 'add_content':
                    $test_params['content_type'] = 'label';
                    break;
            }

            $validation = $this->validate_action($test_params, $user);
            if ($validation['success']) {
                $available_actions[] = $action;
            }
        }

        return $available_actions;
    }
} 