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
 * Content Adder class for executing content addition actions.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\actions;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

/**
 * Class for executing content addition actions.
 */
class content_adder {

    /**
     * Execute a content addition action.
     *
     * @param array $params Action parameters
     * @return array Result with success status and message
     */
    public function execute_action(array $params): array {
        global $DB;

        $target_type = $params['target_type'] ?? '';
        $target_id = $params['target_id'] ?? 0;
        $content_type = $params['content_type'] ?? '';

        if (empty($target_type) || empty($target_id) || empty($content_type)) {
            return [
                'success' => false,
                'message' => get_string('error_missing_parameters', 'block_course_audit')
            ];
        }

        try {
            switch ($target_type) {
                case 'course':
                    return $this->add_content_to_course($target_id, $content_type);
                    
                case 'section':
                    return $this->add_content_to_section($target_id, $content_type);
                    
                default:
                    return [
                        'success' => false,
                        'message' => get_string('error_unsupported_target', 'block_course_audit', $target_type)
                    ];
            }
        } catch (\Exception $e) {
            debugging('Error executing content addition: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => get_string('error_action_failed', 'block_course_audit')
            ];
        }
    }

    /**
     * Add content to a course.
     *
     * @param int $course_id Course ID
     * @param string $content_type Content type to add
     * @return array Result array
     */
    private function add_content_to_course(int $course_id, string $content_type): array {
        global $DB;

        $course = $DB->get_record('course', ['id' => $course_id], '*', MUST_EXIST);
        $context = \context_course::instance($course_id);

        // Check permission
        if (!has_capability('moodle/course:manageactivities', $context)) {
            return [
                'success' => false,
                'message' => get_string('error_no_permission', 'block_course_audit')
            ];
        }

        if ($content_type === 'section') {
            return $this->add_course_section($course);
        } else {
            // Add module to the first available section
            $sections = $DB->get_records('course_sections', ['course' => $course_id], 'section ASC');
            $target_section = reset($sections);
            
            if (!$target_section) {
                return [
                    'success' => false,
                    'message' => get_string('error_no_sections', 'block_course_audit')
                ];
            }

            return $this->add_module_to_section($target_section, $content_type, $course);
        }
    }

    /**
     * Add content to a specific section.
     *
     * @param int $section_id Section ID
     * @param string $content_type Content type to add
     * @return array Result array
     */
    private function add_content_to_section(int $section_id, string $content_type): array {
        global $DB;

        $section = $DB->get_record('course_sections', ['id' => $section_id], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $section->course], '*', MUST_EXIST);
        $context = \context_course::instance($section->course);

        // Check permission
        if (!has_capability('moodle/course:manageactivities', $context)) {
            return [
                'success' => false,
                'message' => get_string('error_no_permission', 'block_course_audit')
            ];
        }

        return $this->add_module_to_section($section, $content_type, $course);
    }

    /**
     * Add a new section to a course.
     *
     * @param object $course Course object
     * @return array Result array
     */
    private function add_course_section(object $course): array {
        global $DB;

        try {
            // Get the last section number
            $max_section = $DB->get_field_sql(
                'SELECT MAX(section) FROM {course_sections} WHERE course = ?',
                [$course->id]
            );
            $new_section_num = $max_section + 1;

            // Create new section
            $section = new \stdClass();
            $section->course = $course->id;
            $section->section = $new_section_num;
            $section->name = get_string('topic') . ' ' . $new_section_num;
            $section->summary = '';
            $section->summaryformat = FORMAT_HTML;
            $section->sequence = '';
            $section->visible = 1;
            $section->availability = null;
            $section->timemodified = time();

            $section_id = $DB->insert_record('course_sections', $section);

            if ($section_id) {
                // Update course numsections
                $DB->set_field('course', 'numsections', $new_section_num, ['id' => $course->id]);

                // Rebuild course cache
                rebuild_course_cache($course->id, true);

                return [
                    'success' => true,
                    'message' => get_string('section_added_successfully', 'block_course_audit', $new_section_num)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => get_string('error_database_insert', 'block_course_audit')
                ];
            }
        } catch (\Exception $e) {
            debugging('Error adding section: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => get_string('error_section_creation', 'block_course_audit')
            ];
        }
    }

    /**
     * Add a module to a section.
     *
     * @param object $section Section object
     * @param string $content_type Module type to add
     * @param object $course Course object
     * @return array Result array
     */
    private function add_module_to_section(object $section, string $content_type, object $course): array {
        global $DB, $CFG;

        // Check if module type is available
        if (!$this->is_module_available($content_type)) {
            return [
                'success' => false,
                'message' => get_string('error_module_not_available', 'block_course_audit', $content_type)
            ];
        }

        try {
            // Create module data based on type
            $moduleinfo = $this->create_module_data($content_type, $course, $section);
            
            if (!$moduleinfo) {
                return [
                    'success' => false,
                    'message' => get_string('error_module_data_creation', 'block_course_audit')
                ];
            }

            // Add the module
            $module = add_moduleinfo($moduleinfo, $course, null);

            if ($module) {
                return [
                    'success' => true,
                    'message' => get_string('module_added_successfully', 'block_course_audit', [
                        'type' => $content_type,
                        'name' => $moduleinfo->name
                    ])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => get_string('error_module_creation', 'block_course_audit')
                ];
            }
        } catch (\Exception $e) {
            debugging('Error adding module: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => get_string('error_module_creation', 'block_course_audit')
            ];
        }
    }

    /**
     * Check if a module type is available.
     *
     * @param string $modname Module name
     * @return bool True if available
     */
    private function is_module_available(string $modname): bool {
        global $DB;

        // Check if module is installed and enabled
        $module = $DB->get_record('modules', ['name' => $modname, 'visible' => 1]);
        return $module !== false;
    }

    /**
     * Create module data object for adding a module.
     *
     * @param string $modname Module name
     * @param object $course Course object
     * @param object $section Section object
     * @return object|null Module data object or null if failed
     */
    private function create_module_data(string $modname, object $course, object $section): ?object {
        global $DB;

        $module = $DB->get_record('modules', ['name' => $modname], '*', MUST_EXIST);

        $moduleinfo = new \stdClass();
        $moduleinfo->modulename = $modname;
        $moduleinfo->module = $module->id;
        $moduleinfo->course = $course->id;
        $moduleinfo->section = $section->section;
        $moduleinfo->visible = 1;
        $moduleinfo->groupmode = 0;
        $moduleinfo->groupingid = 0;
        $moduleinfo->completion = COMPLETION_TRACKING_NONE;
        $moduleinfo->completionview = 0;
        $moduleinfo->completionexpected = 0;
        $moduleinfo->showdescription = 0;

        // Set module-specific defaults
        switch ($modname) {
            case 'label':
                $moduleinfo->name = get_string('defaultlabelname', 'block_course_audit');
                $moduleinfo->intro = get_string('defaultlabelcontent', 'block_course_audit');
                $moduleinfo->introformat = FORMAT_HTML;
                break;

            case 'url':
                $moduleinfo->name = get_string('defaulturlname', 'block_course_audit');
                $moduleinfo->intro = '';
                $moduleinfo->introformat = FORMAT_HTML;
                $moduleinfo->externalurl = 'https://moodle.org';
                $moduleinfo->display = RESOURCELIB_DISPLAY_AUTO;
                break;

            case 'page':
                $moduleinfo->name = get_string('defaultpagename', 'block_course_audit');
                $moduleinfo->intro = '';
                $moduleinfo->introformat = FORMAT_HTML;
                $moduleinfo->content = get_string('defaultpagecontent', 'block_course_audit');
                $moduleinfo->contentformat = FORMAT_HTML;
                $moduleinfo->display = RESOURCELIB_DISPLAY_OPEN;
                break;

            case 'forum':
                $moduleinfo->name = get_string('defaultforumname', 'block_course_audit');
                $moduleinfo->intro = get_string('defaultforumintro', 'block_course_audit');
                $moduleinfo->introformat = FORMAT_HTML;
                $moduleinfo->type = 'general';
                $moduleinfo->assessed = 0;
                $moduleinfo->scale = 0;
                break;

            case 'assign':
                $moduleinfo->name = get_string('defaultassignname', 'block_course_audit');
                $moduleinfo->intro = get_string('defaultassignintro', 'block_course_audit');
                $moduleinfo->introformat = FORMAT_HTML;
                $moduleinfo->alwaysshowdescription = 1;
                $moduleinfo->submissiondrafts = 0;
                $moduleinfo->sendnotifications = 1;
                $moduleinfo->sendlatenotifications = 1;
                $moduleinfo->duedate = time() + (7 * 24 * 60 * 60); // One week from now
                $moduleinfo->allowsubmissionsfromdate = time();
                $moduleinfo->grade = 100;
                $moduleinfo->teamsubmission = 0;
                $moduleinfo->requireallteammemberssubmit = 0;
                $moduleinfo->teamsubmissiongroupingid = 0;
                $moduleinfo->blindmarking = 0;
                $moduleinfo->revealidentities = 0;
                $moduleinfo->attemptreopenmethod = 'none';
                $moduleinfo->maxattempts = -1;
                break;

            case 'quiz':
                $moduleinfo->name = get_string('defaultquizname', 'block_course_audit');
                $moduleinfo->intro = get_string('defaultquizintro', 'block_course_audit');
                $moduleinfo->introformat = FORMAT_HTML;
                $moduleinfo->timeopen = 0;
                $moduleinfo->timeclose = 0;
                $moduleinfo->timelimit = 0;
                $moduleinfo->overduehandling = 'autosubmit';
                $moduleinfo->graceperiod = 0;
                $moduleinfo->preferredbehaviour = 'deferredfeedback';
                $moduleinfo->canredoquestions = 0;
                $moduleinfo->attempts = 0;
                $moduleinfo->attemptonlast = 0;
                $moduleinfo->grademethod = 1;
                $moduleinfo->decimalpoints = 2;
                $moduleinfo->questiondecimalpoints = -1;
                $moduleinfo->reviewattempt = 0x11110;
                $moduleinfo->reviewcorrectness = 0x11110;
                $moduleinfo->reviewmarks = 0x11110;
                $moduleinfo->reviewspecificfeedback = 0x11110;
                $moduleinfo->reviewgeneralfeedback = 0x11110;
                $moduleinfo->reviewrightanswer = 0x11110;
                $moduleinfo->reviewoverallfeedback = 0x11110;
                $moduleinfo->questionsperpage = 1;
                $moduleinfo->navmethod = 'free';
                $moduleinfo->shuffleanswers = 1;
                $moduleinfo->sumgrades = 0;
                $moduleinfo->grade = 10;
                break;

            default:
                $moduleinfo->name = get_string('defaultmodulename', 'block_course_audit', $modname);
                $moduleinfo->intro = '';
                $moduleinfo->introformat = FORMAT_HTML;
                break;
        }

        return $moduleinfo;
    }

    /**
     * Get supported content types for addition.
     *
     * @return array Array of supported content types
     */
    public function get_supported_content_types(): array {
        return [
            'section',
            'label',
            'url',
            'page',
            'forum',
            'assign',
            'quiz',
            'resource'
        ];
    }

    /**
     * Validate if content addition is allowed for the target.
     *
     * @param string $target_type Target type
     * @param string $content_type Content type to add
     * @return bool True if allowed
     */
    public function is_content_addition_allowed(string $target_type, string $content_type): bool {
        $supported_types = $this->get_supported_content_types();
        
        if (!in_array($content_type, $supported_types)) {
            return false;
        }

        // Sections can only be added to courses
        if ($content_type === 'section' && $target_type !== 'course') {
            return false;
        }

        // Modules can be added to courses or sections
        if ($content_type !== 'section' && !in_array($target_type, ['course', 'section'])) {
            return false;
        }

        return true;
    }
} 