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
 * Rule manager for the course_audit block.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules;

defined('MOODLE_INTERNAL') || die();

/**
 * Rule manager for handling course rule sets
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_manager {
    /** @var array List of registered rules, indexed by category */
    private $rules = [
        'activity_type' => [],
        'activity_flow' => []
    ];
    
    /**
     * Constructor - automatically registers all available rules
     */
    public function __construct() {
        $this->register_default_rules();
    }
    
    /**
     * Register all default rules from the rules directory
     */
    private function register_default_rules() {
        global $CFG;
        
        // Auto-discover and register rules in the activity_type directory
        $basedir = $CFG->dirroot . '/blocks/course_audit/classes/rules/activity_type';
        $this->register_rules_from_directory($basedir, 'activity_type');
        
        // Auto-discover and register rules in the activity_flow directory  
        $basedir = $CFG->dirroot . '/blocks/course_audit/classes/rules/activity_flow';
        $this->register_rules_from_directory($basedir, 'activity_flow');
    }
    
    /**
     * Scan a directory for rule classes and register them
     * 
     * @param string $directory Directory to scan
     * @param string $category Rule category
     */
    private function register_rules_from_directory($directory, $category) {
        $files = scandir($directory);
        
        foreach ($files as $file) {
            // Skip directories and non-PHP files
            if (is_dir($directory . '/' . $file) || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }
            
            // Skip template files
            if (strpos($file, 'template') !== false) {
                continue;
            }
            
            // Include the file and instantiate the rule
            require_once($directory . '/' . $file);
            
            // Determine the class name
            $classname = '\\block_course_audit\\rules\\' . $category . '\\' . pathinfo($file, PATHINFO_FILENAME);
            
            if (class_exists($classname)) {
                $rule = new $classname();
                $this->register_rule($rule);
            }
        }
    }
    
    /**
     * Register a rule
     *
     * @param rule_interface $rule Rule to register
     * @return bool Whether registration was successful
     */
    public function register_rule(rule_interface $rule) {
        $category = $rule->get_category();
        
        // Validate category
        if (!isset($this->rules[$category])) {
            return false;
        }
        
        // Add rule to the appropriate category
        $this->rules[$category][] = $rule;
        return true;
    }
    
    /**
     * Get all registered rules
     *
     * @param string $category Optional category filter
     * @return array Array of rule objects
     */
    public function get_rules($category = null) {
        if ($category !== null) {
            return isset($this->rules[$category]) ? $this->rules[$category] : [];
        }
        
        // Return all rules in a flat array
        $allrules = [];
        foreach ($this->rules as $categoryrules) {
            $allrules = array_merge($allrules, $categoryrules);
        }
        
        return $allrules;
    }
    
    /**
     * Run all rules or rules of a specific category on a section
     *
     * @param object $section Section to check
     * @param object $course Course the section belongs to
     * @param string $category Optional category to filter rules
     * @return array Results from all rules
     */
    public function run_rules($section, $course, $category = null) {
        $results = [];
        $rules = $this->get_rules($category);
        
        foreach ($rules as $rule) {
            $results[] = $rule->check_section($section, $course);
        }
        
        return $results;
    }
    
    /**
     * Get summary of rule results
     *
     * @param array $results Rule results
     * @return object Summary with counts of passed and failed rules
     */
    public function get_summary($results) {
        $passed = 0;
        $failed = 0;
        
        foreach ($results as $result) {
            if ($result->status) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        return (object) [
            'passed' => $passed,
            'failed' => $failed,
            'total' => count($results),
            'success_rate' => count($results) > 0 ? ($passed / count($results) * 100) : 0
        ];
    }
} 