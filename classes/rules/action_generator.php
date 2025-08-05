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
 * Action Button Generator class.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for generating action buttons for resolution actions.
 */
class action_generator {

    /**
     * Generate action button for a setting change resolution.
     *
     * @param object $resolution Dynamic resolution object
     * @param object $target Target object (course, section, module, etc.)
     * @return array Action button details
     */
    public function generate_change_setting_action(object $resolution, object $target): array {
        $target_info = $this->determine_target_info($target);
        $mapkey = $this->generate_mapkey($target_info, 'change_setting', $resolution->actionsetting);

        return [
            'mapkey' => $mapkey,
            'actiontype' => 'change_setting',
            'target_type' => $target_info['type'],
            'target_id' => $target_info['id'],
            'setting' => $resolution->actionsetting,
            'value' => $resolution->actionvalue,
            'button_text' => $this->get_setting_change_button_text($resolution->actionsetting, $resolution->actionvalue),
            'confirmation_text' => $this->get_setting_change_confirmation_text($resolution->actionsetting, $resolution->actionvalue),
            'icon' => $this->get_setting_change_icon($resolution->actionsetting, $resolution->actionvalue),
            'params' => [
                'target_type' => $target_info['type'],
                'target_id' => $target_info['id'],
                'setting' => $resolution->actionsetting,
                'value' => $resolution->actionvalue,
                'resolution_id' => $resolution->id,
                'action' => 'change_setting'
            ]
        ];
    }

    /**
     * Generate action button for adding content resolution.
     *
     * @param object $resolution Dynamic resolution object
     * @param object $target Target object
     * @return array Action button details
     */
    public function generate_add_content_action(object $resolution, object $target): array {
        $target_info = $this->determine_target_info($target);
        $mapkey = $this->generate_mapkey($target_info, 'add_content', $resolution->content_type);

        return [
            'mapkey' => $mapkey,
            'actiontype' => 'add_content',
            'target_type' => $target_info['type'],
            'target_id' => $target_info['id'],
            'content_type' => $resolution->content_type,
            'button_text' => $this->get_add_content_button_text($resolution->content_type),
            'confirmation_text' => $this->get_add_content_confirmation_text($resolution->content_type),
            'icon' => $this->get_content_type_icon($resolution->content_type),
            'params' => [
                'target_type' => $target_info['type'],
                'target_id' => $target_info['id'],
                'content_type' => $resolution->content_type,
                'resolution_id' => $resolution->id,
                'action' => 'add_content'
            ]
        ];
    }





    /**
     * Generate action button for creating a backup.
     *
     * @param object $resolution Dynamic resolution object
     * @param object $target Target object
     * @return array Action button details
     */
    public function generate_create_backup_action(object $resolution, object $target): array {
        $target_info = $this->determine_target_info($target);
        $mapkey = $this->generate_mapkey($target_info, 'create_backup', 'backup');

        return [
            'mapkey' => $mapkey,
            'actiontype' => 'create_backup',
            'target_type' => $target_info['type'],
            'target_id' => $target_info['id'],
            'button_text' => get_string('createbackup', 'block_course_audit'),
            'confirmation_text' => get_string('confirmbackup', 'block_course_audit'),
            'icon' => 'i/backup',
            'params' => [
                'target_type' => $target_info['type'],
                'target_id' => $target_info['id'],
                'resolution_id' => $resolution->id,
                'action' => 'create_backup'
            ]
        ];
    }

    /**
     * Generate a unique map key for an action.
     *
     * @param array $target_info Target information
     * @param string $action_type Action type
     * @param string $action_detail Additional action detail
     * @return string Unique map key
     */
    public function generate_mapkey(array $target_info, string $action_type, string $action_detail = ''): string {
        $components = [
            'action',
            $action_type,
            $target_info['type'],
            $target_info['id']
        ];
        
        if (!empty($action_detail)) {
            $components[] = preg_replace('/[^a-zA-Z0-9_]/', '_', $action_detail);
        }
        
        $components[] = time(); // Add timestamp for uniqueness
        
        return implode('_', $components);
    }

    /**
     * Determine target information from target object.
     *
     * @param object $target Target object
     * @return array Array with 'type' and 'id' keys
     */
    private function determine_target_info(object $target): array {
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
     * Get button text for setting change actions.
     *
     * @param string $setting Setting name
     * @param string $value New value
     * @return string Button text
     */
    private function get_setting_change_button_text(string $setting, string $value): string {
        // Special case for visibility - more user-friendly text
        if ($setting === 'visible' && $value === '1') {
            return get_string('makevisible', 'block_course_audit');
        }
        
        // Special cases for feature enabling - more user-friendly text
        if ($value === '1') {
            $enable_features = [
                'enablecompletion' => 'completion',
                'groupmode' => 'groups',
                'showgrades' => 'grades',
                'showreports' => 'reports',
                'showactivitydates' => 'activity dates',
                'showcompletionconditions' => 'completion conditions'
            ];
            
            if (isset($enable_features[$setting])) {
                return get_string('enablefeature', 'block_course_audit', $enable_features[$setting]);
            }
        }
        
        // Try to get specific string first
        $string_key = 'action_change_' . $setting;
        if (get_string_manager()->string_exists($string_key, 'block_course_audit')) {
            return get_string($string_key, 'block_course_audit', $value);
        }
        
        // Fallback to generic string
        return get_string('changesetting', 'block_course_audit', ['setting' => $setting, 'value' => $value]);
    }

    /**
     * Get confirmation text for setting change actions.
     *
     * @param string $setting Setting name
     * @param string $value New value
     * @return string Confirmation text
     */
    private function get_setting_change_confirmation_text(string $setting, string $value): string {
        // Special case for visibility - more user-friendly text
        if ($setting === 'visible' && $value === '1') {
            return get_string('confirmvisible', 'block_course_audit');
        }
        
        // Special cases for feature enabling
        if ($value === '1') {
            $enable_features = [
                'enablecompletion' => 'completion',
                'groupmode' => 'groups', 
                'showgrades' => 'grades',
                'showreports' => 'reports',
                'showactivitydates' => 'activity dates',
                'showcompletionconditions' => 'completion conditions'
            ];
            
            if (isset($enable_features[$setting])) {
                return get_string('confirmenablefeature', 'block_course_audit', $enable_features[$setting]);
            }
        }
        
        return get_string('confirmchangesetting', 'block_course_audit', ['setting' => $setting, 'value' => $value]);
    }

    /**
     * Get appropriate icon for setting change actions.
     *
     * @param string $setting Setting name
     * @param string $value New value
     * @return string Icon identifier
     */
    private function get_setting_change_icon(string $setting, string $value): string {
        // Special case for visibility
        if ($setting === 'visible' && $value === '1') {
            return 'i/show';
        }
        
        // Setting-specific icons
        $setting_icons = [
            'enablecompletion' => 'i/completion',
            'groupmode' => 'i/group',
            'showgrades' => 'i/grades',
            'showreports' => 'i/report',
            'showactivitydates' => 'i/calendar',
            'showcompletionconditions' => 'i/completion',
            'visible' => 'i/hide', // For hiding (visible=0)
        ];
        
        // Use checkmark icon for enabling features
        if ($value === '1' && in_array($setting, array_keys($setting_icons))) {
            return 'i/checkmarkgreen';
        }
        
        return $setting_icons[$setting] ?? 'i/settings';
    }

    /**
     * Get button text for add content actions.
     *
     * @param string $content_type Content type
     * @return string Button text
     */
    private function get_add_content_button_text(string $content_type): string {
        $string_key = 'action_add_' . $content_type;
        if (get_string_manager()->string_exists($string_key, 'block_course_audit')) {
            return get_string($string_key, 'block_course_audit');
        }
        
        return get_string('addcontent', 'block_course_audit', $content_type);
    }

    /**
     * Get confirmation text for add content actions.
     *
     * @param string $content_type Content type
     * @return string Confirmation text
     */
    private function get_add_content_confirmation_text(string $content_type): string {
        return get_string('confirmaddcontent', 'block_course_audit', $content_type);
    }



    /**
     * Get appropriate icon for content type.
     *
     * @param string $content_type Content type
     * @return string Icon identifier
     */
    private function get_content_type_icon(string $content_type): string {
        $icons = [
            'quiz' => 'i/quiz',
            'assign' => 'i/assignment',
            'forum' => 'i/forum',
            'resource' => 'i/files',
            'url' => 'i/link',
            'page' => 'i/document',
            'book' => 'i/book',
            'lesson' => 'i/lesson',
            'scorm' => 'i/scorm',
            'label' => 'i/label',
            'section' => 'i/section'
        ];
        
        return $icons[$content_type] ?? 'i/item';
    }

    /**
     * Validate if an action is allowed for the given target.
     *
     * @param string $action_type Action type
     * @param object $target Target object
     * @param object $user User object (optional, defaults to current user)
     * @return bool True if action is allowed
     */
    public function is_action_allowed(string $action_type, object $target, ?object $user = null): bool {
        global $USER;
        
        if ($user === null) {
            $user = $USER;
        }

        $target_info = $this->determine_target_info($target);
        
        switch ($target_info['type']) {
            case 'course':
                return has_capability('moodle/course:update', \context_course::instance($target->id), $user);
                
            case 'section':
                return has_capability('moodle/course:update', \context_course::instance($target->course), $user);
                
            case 'mod':
                $context = \context_module::instance($target->id);
                return has_capability('moodle/course:manageactivities', $context, $user);
                
            default:
                return false;
        }
    }

    /**
     * Get all supported action types.
     *
     * @return array Array of supported action types
     */
    public function get_supported_action_types(): array {
        return [
            'change_setting',
            'add_content',
            'create_backup'
        ];
    }
} 