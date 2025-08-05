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
 * Dynamic Resolution class.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\dynamic;

use block_course_audit\rules\action_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing resolution actions and hints.
 */
class dynamic_resolution {

    /** @var int Resolution ID */
    public $id;

    /** @var int Associated rule ID */
    public $rule_id;

    /** @var string Resolution type (hint, action, show) */
    public $type;

    /** @var string Resolution scope (course, section, mod, etc.) */
    public $scope;

    /** @var string Hint message for hint-type resolutions */
    public $hint_message;

    /** @var string Show message for show-type resolutions */
    public $show_message;

    /** @var string Action type for action-type resolutions */
    public $actiontype;

    /** @var string Action setting to change */
    public $actionsetting;

    /** @var string Action value to set */
    public $actionvalue;

    /** @var string Additional content type for actions */
    public $content_type;

    /** @var string Other target for actions */
    public $other_target;

    /** @var int Sort order */
    public $sortorder;

    /**
     * Constructor.
     *
     * @param object $resolution_data Resolution data from database
     */
    public function __construct(object $resolution_data) {
        $this->id = $resolution_data->id;
        $this->rule_id = $resolution_data->rule_id;
        $this->type = $resolution_data->type;
        $this->scope = $resolution_data->scope ?? '';
        $this->hint_message = $resolution_data->hint_message ?? '';
        $this->show_message = $resolution_data->show_message ?? '';
        $this->actiontype = $resolution_data->actiontype ?? '';
        $this->actionsetting = $resolution_data->actionsetting ?? '';
        $this->actionvalue = $resolution_data->actionvalue ?? '';
        $this->content_type = $resolution_data->content_type ?? '';
        $this->other_target = $resolution_data->other_target ?? '';
        $this->sortorder = $resolution_data->sortorder ?? 0;
    }

    /**
     * Generate output for this resolution.
     *
     * @return string Resolution output text
     */
    public function generate_output(): string {
        switch ($this->type) {
            case 'hint':
                return $this->hint_message;
                
                //TODO
            case 'show':
                return $this->show_message;
                
            case 'action':
                return $this->generate_action_description();
                
            default:
                return '';
        }
    }

    /**
     * Generate action button details for action-type resolutions.
     *
     * @param object $target The target object for the action
     * @return array|null Action button details array or null if not applicable
     */
    public function generate_action_button(object $target): ?array {
        if ($this->type !== 'action') {
            return null;
        }

        // Handle "other target" functionality - get other instances of same type
        $resolved_targets = $this->resolve_targets($target);
        if (empty($resolved_targets)) {
            return null;
        }

        // Use the action generator for creating action buttons
        $action_generator = new action_generator();
        $all_action_details = [];

        // Generate action details for each resolved target
        foreach ($resolved_targets as $resolved_target) {
            switch ($this->actiontype) {
                case 'change_setting':
                    $action_details = $action_generator->generate_change_setting_action($this, $resolved_target);
                    break;
                    
                case 'add_content':
                    $action_details = $action_generator->generate_add_content_action($this, $resolved_target);
                    break;
                    
                case 'create_backup':
                    $action_details = $action_generator->generate_create_backup_action($this, $resolved_target);
                    break;
                    
                default:
                    debugging("Unknown action type: {$this->actiontype}", DEBUG_DEVELOPER);
                    continue 2;
            }

            if ($action_details) {
                $all_action_details[] = $action_details;
            }
        }

        // If other_target is enabled, return all action details; otherwise return the first one
        if ($this->other_target && count($all_action_details) > 1) {
            // For multiple targets, we could combine them or return the first
            // For now, return the first action details but note it affects multiple targets
            $first_action = $all_action_details[0];
            $first_action['affects_multiple'] = count($all_action_details);
            return $first_action;
        }

        return !empty($all_action_details) ? $all_action_details[0] : null;
    }

    /**
     * Generate action description text.
     *
     * @return string Action description
     */
    private function generate_action_description(): string {
        switch ($this->actiontype) {
            case 'change_setting':
                // Smart description for common cases
                if ($this->actionsetting === 'visible' && $this->actionvalue === '1') {
                    return "Make visible";
                }
                
                // Feature enabling cases
                $enable_features = [
                    'enablecompletion' => 'completion',
                    'groupmode' => 'groups',
                    'showgrades' => 'grades',
                    'showreports' => 'reports',
                    'showactivitydates' => 'activity dates',
                    'showcompletionconditions' => 'completion conditions'
                ];
                
                if ($this->actionvalue === '1' && isset($enable_features[$this->actionsetting])) {
                    return "Enable {$enable_features[$this->actionsetting]}";
                }
                
                return "Change {$this->actionsetting} to '{$this->actionvalue}'";
                
            case 'add_content':
                return "Add {$this->content_type}";
                
            case 'create_backup':
                return "Create backup";
                
            default:
                return "Perform action: {$this->actiontype}";
        }
    }

    /**
     * Determine target information from target object.
     *
     * @param object $target The target object
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
     * Generate a unique map key for this action.
     *
     * @param array $target_info Target information
     * @return string Unique map key
     */
    private function generate_mapkey(array $target_info): string {
        $components = [
            'resolution',
            $this->id,
            $this->actiontype,
            $target_info['type'],
            $target_info['id']
        ];
        
        if (!empty($this->actionsetting)) {
            $components[] = $this->actionsetting;
        }
        
        if (!empty($this->content_type)) {
            $components[] = $this->content_type;
        }
        
        return implode('_', $components);
    }

    /**
     * Check if this resolution is applicable to the given target.
     *
     * @param object $target The target object
     * @return bool True if resolution applies to this target
     */
    public function applies_to_target(object $target): bool {
        $resolved_targets = $this->resolve_targets($target);
        
        if (empty($resolved_targets)) {
            return false;
        }
        
        // Check if at least one resolved target matches the scope
        foreach ($resolved_targets as $resolved_target) {
            $target_info = $this->determine_target_info($resolved_target);
            
            // If scope is specified, check if it matches the target type
            if (!empty($this->scope) && $this->scope !== 'any') {
                if ($target_info['type'] === $this->scope) {
                    return true;
                }
            } else {
                // If no specific scope, resolution applies to any target
                return true;
            }
        }
        
        return false;
    }

    /**
     * Resolve targets for this resolution based on other_target settings.
     *
     * @param object $target The original target object
     * @return array Array of resolved target objects
     */
    private function resolve_targets(object $target): array {
        global $DB;
        
        // If other_target is enabled, get other instances of same type
        if ($this->other_target) {
            return $this->get_other_target_instances($target);
        }
        
        // Otherwise, use the original target
        return [$target];
    }

    /**
     * Get other instances of the same type as target for "other target" functionality.
     *
     * @param object $target The original target object
     * @return array Array of other target instances
     */
    private function get_other_target_instances(object $target): array {
        global $DB;
        
        // Determine target type and get other instances
        if (isset($target->category) && isset($target->fullname)) {
            // Target is a course - no other courses in this context
            return [];
            
        } else if (isset($target->section) && isset($target->course)) {
            // Target is a section - get other sections
            $course = $DB->get_record('course', ['id' => $target->course]);
            if (!$course) {
                return [];
            }
            
            $sections = get_fast_modinfo($course)->get_section_info_all();
            $other_sections = [];
            
            foreach ($sections as $section) {
                if ($section->id != $target->id) {
                    $other_sections[] = $section;
                }
            }
            
            return $other_sections;
            
        } else if (isset($target->modname)) {
            // Target is a module - get other modules of same type
            $course = $DB->get_record('course', ['id' => $target->course]);
            if (!$course) {
                return [];
            }
            
            $modinfo = get_fast_modinfo($course);
            $other_modules = [];
            
            foreach ($modinfo->get_cms() as $cm) {
                if ($cm->id != $target->id && $cm->modname === $target->modname) {
                    $other_modules[] = $cm;
                }
            }
            
            return $other_modules;
        }
        
        return [];
    }

    /**
     * Get the resolution description for display.
     *
     * @return string Resolution description
     */
    public function get_description(): string {
        switch ($this->type) {
            case 'hint':
                return substr($this->hint_message, 0, 100) . (strlen($this->hint_message) > 100 ? '...' : '');
                
            case 'show':
                return substr($this->show_message, 0, 100) . (strlen($this->show_message) > 100 ? '...' : '');
                
            case 'action':
                return $this->generate_action_description();
                
            default:
                return "Resolution #{$this->id}";
        }
    }

    /**
     * Check if this resolution requires user action.
     *
     * @return bool True if resolution requires action
     */
    public function requires_action(): bool {
        return $this->type === 'action';
    }

    /**
     * Get the priority of this resolution type.
     *
     * @return int Priority (lower numbers = higher priority)
     */
    public function get_priority(): int {
        switch ($this->type) {
            case 'action':
                return 1; // Highest priority
            case 'show':
                return 2; // Medium priority
            case 'hint':
                return 3; // Lowest priority
            default:
                return 4; // Unknown types get lowest priority
        }
    }
} 