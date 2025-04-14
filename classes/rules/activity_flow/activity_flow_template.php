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
 * Template for activity flow rules.
 * This is not an actual rule but a template to create new activity flow rules.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\activity_flow;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_base.php');

use block_course_audit\rules\rule_base;

/**
 * Template class for creating activity flow rules
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_flow_template extends rule_base {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'Rule name', // Replace with get_string() call
            'Rule description', // Replace with get_string() call
            'activity_flow'
        );
    }
    
    /**
     * Check if activities in a section meet the flow requirements
     *
     * @param object $section The section to check
     * @param object $course The course the section belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_section($section, $course) {
        // If no modules in section, return false
        if (empty($section->modules)) {
            return $this->create_result(false, ['No activities found in this section'], $section->id);
        }
        
        // If only one module, you might need to handle this case specifically
        if (count($section->modules) < 2) {
            return $this->create_result(false, [
                'Section has only one activity (' . $section->modules[0]->name . '), need at least two for flow analysis'
            ], $section->id);
        }
        
        // Arrays to track flow-related information
        $modulesMeetingFlowCriteria = [];
        $modulesNotMeetingFlowCriteria = [];
        
        // Example flow analysis variables
        $flowGraph = []; // For storing activity connectivity
        $moduleIdMap = []; // For mapping module IDs to array indices
        
        // Build mapping of module IDs
        foreach ($section->modules as $index => $module) {
            $moduleIdMap[$module->id] = $index;
            $flowGraph[$index] = [];
        }
        
        // Analyze each module's conditions to build the flow graph
        foreach ($section->modules as $index => $module) {
            // Skip if no availability data
            if (empty($module->availability)) {
                $modulesNotMeetingFlowCriteria[] = $module->name;
                continue;
            }
            
            // Parse the availability conditions
            $availability = json_decode($module->availability);
            if (isset($availability->c) && is_array($availability->c)) {
                foreach ($availability->c as $condition) {
                    // Check for completion conditions
                    if (isset($condition->type) && $condition->type === 'completion') {
                        // Add a connection in the flow graph
                        if (isset($moduleIdMap[$condition->cm])) {
                            $sourceIndex = $moduleIdMap[$condition->cm];
                            $flowGraph[$sourceIndex][] = $index;
                            
                            // Add this module to the list of modules meeting flow criteria
                            $modulesMeetingFlowCriteria[] = $module->name;
                        }
                    }
                }
            }
        }
        
        // Perform your specific flow analysis here
        // Example: Check if there are any connections at all
        $hasAnyConnections = false;
        foreach ($flowGraph as $connections) {
            if (!empty($connections)) {
                $hasAnyConnections = true;
                break;
            }
        }
        
        // Example: Check if there's a complete path from the first to the last activity
        $hasCompletePath = $this->check_path_exists($flowGraph, 0, count($section->modules) - 1);
        
        // Example: Check if there are any isolated activities
        $isolatedActivities = [];
        foreach ($section->modules as $index => $module) {
            $hasIncoming = false;
            $hasOutgoing = !empty($flowGraph[$index]);
            
            // Check if any other module points to this one
            foreach ($flowGraph as $sourceIdx => $targets) {
                if (in_array($index, $targets)) {
                    $hasIncoming = true;
                    break;
                }
            }
            
            if (!$hasIncoming && !$hasOutgoing && $index > 0) { // Skip first activity for incoming check
                $isolatedActivities[] = $module->name;
            }
        }
        
        // Determine the result based on your flow criteria
        $result = $hasAnyConnections && $hasCompletePath && empty($isolatedActivities);
        $messages = [];
        
        if ($result) {
            $messages[] = 'The activities in this section have a good flow structure.';
        } else {
            if (!$hasAnyConnections) {
                $messages[] = 'There are no connections between activities in this section.';
            }
            
            if (!$hasCompletePath) {
                $messages[] = 'There is no complete path from the first to the last activity.';
            }
            
            if (!empty($isolatedActivities)) {
                $messages[] = 'The following activities are isolated (no incoming or outgoing connections):';
                foreach ($isolatedActivities as $name) {
                    $messages[] = '- ' . $name;
                }
            }
        }
        
        return $this->create_result($result, $messages, $section->id);
    }
    
    /**
     * Helper function to check if a path exists between two nodes in the flow graph
     *
     * @param array $graph The flow graph as an adjacency list
     * @param int $start Start node index
     * @param int $end End node index
     * @return bool Whether a path exists
     */
    private function check_path_exists($graph, $start, $end) {
        // Skip implementation for template
        // In a real rule, you would implement a path-finding algorithm here
        // (e.g., breadth-first search or depth-first search)
        return false;
    }
} 