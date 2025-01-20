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
 * Template for activity type rules.
 * This is not an actual rule but a template to create new activity type rules.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\activity_type;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_base.php');

use block_course_audit\rules\rule_base;

/**
 * Template class for creating activity type rules
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_type_template extends rule_base {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'Rule name', // Replace with get_string() call
            'Rule description', // Replace with get_string() call
            'activity_type'
        );
    }
    
    /**
     * Check if a section meets the activity type requirements
     *
     * @param object $section The section to check
     * @param object $course The course the section belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_section($section, $course) {
        global $DB;
        
        // If no modules in section, handle empty section case
        if (empty($section->modules)) {
            return $this->create_result(false, ['No activities found in this section']);
        }
        
        // Arrays to track activities meeting or not meeting the rule criteria
        $compliantactivities = [];
        $noncompliantactivities = [];
        
        foreach ($section->modules as $module) {
            // Implement your rule-specific logic here to check each module
            // For example:
            
            // 1. Check module type (modname)
            if ($module->modname === 'desired_type') {
                // Add to compliant activities
                $compliantactivities[] = $module->name;
            } else {
                // Add to non-compliant activities
                $noncompliantactivities[] = [
                    'name' => $module->name,
                    'type' => $module->modname
                ];
            }
            
            // 2. For resource-type modules, you might need to check file types:
            if ($module->modname === 'resource') {
                // Get the course module
                $cm = get_coursemodule_from_id('resource', $module->id, $course->id);
                if (!$cm) {
                    continue;
                }
                
                // Get the file
                $context = \context_module::instance($cm->id);
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);
                
                $file = reset($files);
                if (!$file) {
                    continue;
                }
                
                // Check file properties (mimetype, extension, etc.)
                if ($file->get_mimetype() === 'desired_mimetype') {
                    $compliantactivities[] = $module->name;
                } else {
                    $noncompliantactivities[] = [
                        'name' => $module->name,
                        'type' => $file->get_mimetype()
                    ];
                }
            }
        }
        
        // Determine result based on compliant and non-compliant activities
        if (!empty($noncompliantactivities)) {
            $messages = ['Some activities do not meet the requirements:'];
            
            foreach ($noncompliantactivities as $activity) {
                $messages[] = "Activity '{$activity['name']}' is of type '{$activity['type']}'";
            }
            
            return $this->create_result(false, $messages);
        }
        
        // If all activities are compliant
        return $this->create_result(true, [
            'All ' . count($compliantactivities) . ' activities meet the requirements.'
        ]);
    }
} 