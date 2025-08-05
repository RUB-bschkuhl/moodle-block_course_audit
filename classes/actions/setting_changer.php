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
 * Setting Changer class for executing setting change actions.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\actions;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for executing setting change actions.
 */
class setting_changer {

    /**
     * Execute a setting change action.
     *
     * @param array $params Action parameters
     * @return array Result with success status and message
     */
    public function execute_action(array $params): array {
        global $DB;

        $target_type = $params['target_type'] ?? '';
        $target_id = $params['target_id'] ?? 0;
        $setting = $params['setting'] ?? '';
        $value = $params['value'] ?? '';

        if (empty($target_type) || empty($target_id) || empty($setting)) {
            return [
                'success' => false,
                'message' => get_string('error_missing_parameters', 'block_course_audit')
            ];
        }

        try {
            switch ($target_type) {
                case 'course':
                    return $this->change_course_setting($target_id, $setting, $value);
                    
                case 'section':
                    return $this->change_section_setting($target_id, $setting, $value);
                    
                case 'mod':
                    return $this->change_module_setting($target_id, $setting, $value);
                    
                default:
                    return [
                        'success' => false,
                        'message' => get_string('error_unsupported_target', 'block_course_audit', $target_type)
                    ];
            }
        } catch (\Exception $e) {
            debugging('Error executing setting change: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => get_string('error_action_failed', 'block_course_audit')
            ];
        }
    }

    /**
     * Change a course setting.
     *
     * @param int $course_id Course ID
     * @param string $setting Setting name
     * @param string $value New value
     * @return array Result array
     */
    private function change_course_setting(int $course_id, string $setting, string $value): array {
        global $DB;

        $course = $DB->get_record('course', ['id' => $course_id], '*', MUST_EXIST);
        $context = \context_course::instance($course_id);

        // Check permission
        if (!has_capability('moodle/course:update', $context)) {
            return [
                'success' => false,
                'message' => get_string('error_no_permission', 'block_course_audit')
            ];
        }

        $allowed_settings = $this->get_allowed_course_settings();
        if (!in_array($setting, $allowed_settings)) {
            return [
                'success' => false,
                'message' => get_string('error_setting_not_allowed', 'block_course_audit', $setting)
            ];
        }

        // Validate and convert value
        $converted_value = $this->convert_value($setting, $value);
        if ($converted_value === false) {
            return [
                'success' => false,
                'message' => get_string('error_invalid_value', 'block_course_audit', $value)
            ];
        }

        // Update the setting
        $update_data = [
            'id' => $course_id,
            $setting => $converted_value,
            'timemodified' => time()
        ];

        $success = $DB->update_record('course', $update_data);

        if ($success) {
            // Trigger course updated event
            $event = \core\event\course_updated::create([
                'objectid' => $course_id,
                'context' => $context,
                'other' => [
                    'updatedfields' => [$setting]
                ]
            ]);
            $event->trigger();

            return [
                'success' => true,
                'message' => get_string('setting_changed_successfully', 'block_course_audit', [
                    'setting' => $setting,
                    'value' => $value
                ])
            ];
        } else {
            return [
                'success' => false,
                'message' => get_string('error_database_update', 'block_course_audit')
            ];
        }
    }

    /**
     * Change a section setting.
     *
     * @param int $section_id Section ID
     * @param string $setting Setting name
     * @param string $value New value
     * @return array Result array
     */
    private function change_section_setting(int $section_id, string $setting, string $value): array {
        global $DB;

        $section = $DB->get_record('course_sections', ['id' => $section_id], '*', MUST_EXIST);
        $context = \context_course::instance($section->course);

        // Check permission
        if (!has_capability('moodle/course:update', $context)) {
            return [
                'success' => false,
                'message' => get_string('error_no_permission', 'block_course_audit')
            ];
        }

        $allowed_settings = $this->get_allowed_section_settings();
        if (!in_array($setting, $allowed_settings)) {
            return [
                'success' => false,
                'message' => get_string('error_setting_not_allowed', 'block_course_audit', $setting)
            ];
        }

        // Validate and convert value
        $converted_value = $this->convert_value($setting, $value);
        if ($converted_value === false) {
            return [
                'success' => false,
                'message' => get_string('error_invalid_value', 'block_course_audit', $value)
            ];
        }

        // Update the setting
        $update_data = [
            'id' => $section_id,
            $setting => $converted_value,
            'timemodified' => time()
        ];

        $success = $DB->update_record('course_sections', $update_data);

        if ($success) {
            // Rebuild course cache
            rebuild_course_cache($section->course, true);

            return [
                'success' => true,
                'message' => get_string('setting_changed_successfully', 'block_course_audit', [
                    'setting' => $setting,
                    'value' => $value
                ])
            ];
        } else {
            return [
                'success' => false,
                'message' => get_string('error_database_update', 'block_course_audit')
            ];
        }
    }

    /**
     * Change a module setting.
     *
     * @param int $cm_id Course module ID
     * @param string $setting Setting name
     * @param string $value New value
     * @return array Result array
     */
    private function change_module_setting(int $cm_id, string $setting, string $value): array {
        global $DB;

        $cm = $DB->get_record('course_modules', ['id' => $cm_id], '*', MUST_EXIST);
        $context = \context_module::instance($cm_id);

        // Check permission
        if (!has_capability('moodle/course:manageactivities', $context)) {
            return [
                'success' => false,
                'message' => get_string('error_no_permission', 'block_course_audit')
            ];
        }

        $allowed_settings = $this->get_allowed_module_settings();
        if (!in_array($setting, $allowed_settings)) {
            return [
                'success' => false,
                'message' => get_string('error_setting_not_allowed', 'block_course_audit', $setting)
            ];
        }

        // Validate and convert value
        $converted_value = $this->convert_value($setting, $value);
        if ($converted_value === false) {
            return [
                'success' => false,
                'message' => get_string('error_invalid_value', 'block_course_audit', $value)
            ];
        }

        // Update the setting
        $update_data = [
            'id' => $cm_id,
            $setting => $converted_value
        ];

        $success = $DB->update_record('course_modules', $update_data);

        if ($success) {
            // Rebuild course cache
            rebuild_course_cache($cm->course, true);

            // Trigger module updated event
            $event = \core\event\course_module_updated::create([
                'objectid' => $cm_id,
                'context' => $context,
                'other' => [
                    'modulename' => $cm->modname,
                    'instanceid' => $cm->instance,
                    'name' => get_coursemodule_from_id($cm->modname, $cm_id)->name
                ]
            ]);
            $event->trigger();

            return [
                'success' => true,
                'message' => get_string('setting_changed_successfully', 'block_course_audit', [
                    'setting' => $setting,
                    'value' => $value
                ])
            ];
        } else {
            return [
                'success' => false,
                'message' => get_string('error_database_update', 'block_course_audit')
            ];
        }
    }

    /**
     * Get allowed course settings that can be changed.
     *
     * @return array Array of allowed setting names
     */
    private function get_allowed_course_settings(): array {
        return [
            'visible',
            'enablecompletion',
            'completionnotify',
            'showgrades',
            'showreports',
            'maxbytes',
            'groupmode',
            'groupmodeforce',
            'newsitems',
            'showactivitydates',
            'showcompletionconditions'
        ];
    }

    /**
     * Get allowed section settings that can be changed.
     *
     * @return array Array of allowed setting names
     */
    private function get_allowed_section_settings(): array {
        return [
            'visible',
            'availability'
        ];
    }

    /**
     * Get allowed module settings that can be changed.
     *
     * @return array Array of allowed setting names
     */
    private function get_allowed_module_settings(): array {
        return [
            'visible',
            'availability',
            'indent',
            'groupmode',
            'groupingid',
            'completion',
            'completionview',
            'completionexpected',
            'showdescription'
        ];
    }

    /**
     * Convert string value to appropriate type for database storage.
     *
     * @param string $setting Setting name
     * @param string $value String value to convert
     * @return mixed Converted value or false if invalid
     */
    private function convert_value(string $setting, string $value) {
        // Boolean settings
        $boolean_settings = [
            'visible', 'enablecompletion', 'completionnotify', 'showgrades',
            'showreports', 'groupmodeforce', 'showactivitydates',
            'showcompletionconditions', 'completion', 'completionview', 'showdescription'
        ];

        if (in_array($setting, $boolean_settings)) {
            if (in_array(strtolower($value), ['1', 'true', 'yes', 'on'])) {
                return 1;
            } else if (in_array(strtolower($value), ['0', 'false', 'no', 'off'])) {
                return 0;
            } else {
                return false; // Invalid boolean value
            }
        }

        // Integer settings
        $integer_settings = [
            'maxbytes', 'groupmode', 'newsitems', 'indent', 'groupingid', 'completionexpected'
        ];

        if (in_array($setting, $integer_settings)) {
            if (is_numeric($value)) {
                return (int)$value;
            } else {
                return false; // Invalid integer value
            }
        }

        // String settings (like availability JSON)
        return $value;
    }

    /**
     * Validate if a setting change is safe and allowed.
     *
     * @param string $target_type Target type
     * @param string $setting Setting name
     * @param string $value New value
     * @return bool True if safe
     */
    public function is_setting_change_safe(string $target_type, string $setting, string $value): bool {
        // Check if setting is in allowed list
        switch ($target_type) {
            case 'course':
                $allowed = $this->get_allowed_course_settings();
                break;
            case 'section':
                $allowed = $this->get_allowed_section_settings();
                break;
            case 'mod':
                $allowed = $this->get_allowed_module_settings();
                break;
            default:
                return false;
        }

        if (!in_array($setting, $allowed)) {
            return false;
        }

        // Check if value conversion is valid
        return $this->convert_value($setting, $value) !== false;
    }
} 