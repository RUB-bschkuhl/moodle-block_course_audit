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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Rule that checks if activities in a section have any connections.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\action;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_base.php');

use block_course_audit\rules\rule_base;

/**
 * Rule that checks if activities in a section have any connections.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class has_connections extends rule_base {

    const rule_key = 'has_connections';
    const target_type = 'section';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_has_connections_name', 'block_course_audit'),
            get_string('rule_has_connections_description', 'block_course_audit'),
            'action'
        );
    }
    
    /**
     * Check if activities in a section have any connections (completion conditions)
     *
     * @param object $section The section to check
     * @param object $course The course the section belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_section($section, $course) {
        // If no modules in section, return false
        if (empty($section->modules)) {
            return $this->create_result(false, [
                get_string('rule_has_connections_empty_section', 'block_course_audit')
            ], $section->id);
        }
        
        // If only one module, return false (can't have connections with only one module)
        if (count($section->modules) < 2) {
            return $this->create_result(false, [
                get_string('rule_has_connections_single_module', 'block_course_audit', 
                    ['name' => $section->modules[0]->name])
            ], $section->id);
        }
        
        $modulesWithConditions = [];
        $modulesWithoutConditions = [];
        
        foreach ($section->modules as $module) {
            // Skip if no availability data
            if (empty($module->availability)) {
                $modulesWithoutConditions[] = $module->name;
                continue;
            }
            
            $hasCompletionCondition = false;
            
            // Parse the availability conditions
            $availability = json_decode($module->availability);
            if (isset($availability->c) && is_array($availability->c)) {
                foreach ($availability->c as $condition) {
                    // Check for completion conditions
                    if (isset($condition->type) && $condition->type === 'completion') {
                        $hasCompletionCondition = true;
                        break;
                    }
                }
            }
            
            if ($hasCompletionCondition) {
                $modulesWithConditions[] = $module->name;
            } else {
                $modulesWithoutConditions[] = $module->name;
            }
        }
        
        // If no modules have completion conditions
        if (empty($modulesWithConditions)) {
            return $this->create_result(false, [
                get_string('rule_has_connections_no_conditions', 'block_course_audit')
            ], $section->id);
        }
        
        // Prepare messages about which modules have conditions
        $messages = [
            get_string('rule_has_connections_success', 'block_course_audit',
                ['count' => count($modulesWithConditions)])
        ];
        
        // Add details about modules with and without conditions
        foreach ($modulesWithConditions as $moduleName) {
            $messages[] = get_string('rule_has_connections_module_with_condition', 'block_course_audit',
                ['name' => $moduleName]);
        }
        
        if (!empty($modulesWithoutConditions)) {
            $messages[] = get_string('rule_has_connections_some_without_conditions', 'block_course_audit',
                ['count' => count($modulesWithoutConditions)]);
                
            foreach ($modulesWithoutConditions as $moduleName) {
                $messages[] = get_string('rule_has_connections_module_without_condition', 'block_course_audit',
                    ['name' => $moduleName]);
            }
        }
        
        return $this->create_result(true, $messages, $section->id);
    }
} 