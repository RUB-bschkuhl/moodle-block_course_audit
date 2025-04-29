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
 * Rule that checks if a section contains only PDF resources.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

        namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_base.php');

use block_course_audit\rules\rule_base;

/**
 * Rule that checks if a section contains only PDF resources.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_only extends rule_base {

    const rule_key = 'course_has_section';
    const target_type = 'course';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_pdf_only_name', 'block_course_audit'),
            get_string('rule_pdf_only_description', 'block_course_audit'),
            'hint'
            //get_string('rule_category_hint', 'block_course_audit')
        );
    }
    
    /**
     * Check if a section contains only PDF resources
     *
     * @param object $section The section to check
     * @param object $course The course the section belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_section($section, $course) {
        // If no modules in section, return false
        if (empty($section->modules)) {
            return $this->create_result(false, [
                get_string('rule_pdf_only_empty_section', 'block_course_audit')
            ], $section->id);
        }
        
        $nonpdfresources = [];
        $pdfs = [];
        
        foreach ($section->modules as $module) {
            // Check if it's a resource
            if ($module->modname === 'resource') {
                // Get the resource file
                $cm = get_coursemodule_from_id('resource', $module->id, $course->id);
                if (!$cm) {
                    continue;
                }
                
                $context = \context_module::instance($cm->id);
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);
                
                $file = reset($files);
                if (!$file) {
                    continue;
                }
                
                // Check if it's a PDF
                if ($file->get_mimetype() === 'application/pdf') {
                    $pdfs[] = $module->name;
                } else {
                    $nonpdfresources[] = [
                        'name' => $module->name,
                        'type' => $file->get_mimetype()
                    ];
                }
            } else {
                // Not a resource at all
                $nonpdfresources[] = [
                    'name' => $module->name,
                    'type' => $module->modname
                ];
            }
        }
        
        // If there are non-PDF resources
        if (!empty($nonpdfresources)) {
            $messages = [get_string('rule_pdf_only_non_pdf_resources', 'block_course_audit')];
            
            foreach ($nonpdfresources as $resource) {
                $messages[] = get_string('rule_pdf_only_non_pdf_resource_item', 'block_course_audit', 
                    ['name' => $resource['name'], 'type' => $resource['type']]);
            }
            
            return $this->create_result(true, $messages, $section->id);
        }
        
        // If we made it here, all resources are PDFs
        return $this->create_result(false, [
            get_string('rule_pdf_only_success', 'block_course_audit', 
                ['count' => count($pdfs)])
        ], $section->id);
    }
} 