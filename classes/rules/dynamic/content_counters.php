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
 * Content Counters utility class.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\dynamic;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility class for counting content items of various types.
 */
class content_counters {

    /**
     * Count content items for a course.
     *
     * @param string $content_type Type of content to count
     * @param \stdClass $course Course object
     * @return int Content count
     */
    public function count_course_content(string $content_type, \stdClass $course): int {
        switch ($content_type) {
            case 'modules':
                return $this->count_course_modules($course);
            case 'sections':
                return $this->count_course_sections($course);
            case 'visible_modules':
                return $this->count_visible_modules($course);
            case 'hidden_modules':
                return $this->count_hidden_modules($course);
            case 'quiz':
                return $this->count_modules_by_type($course, 'quiz');
            case 'assign':
                return $this->count_modules_by_type($course, 'assign');
            case 'forum':
                return $this->count_modules_by_type($course, 'forum');
            case 'resource':
                return $this->count_modules_by_type($course, 'resource');
            case 'url':
                return $this->count_modules_by_type($course, 'url');
            case 'page':
                return $this->count_modules_by_type($course, 'page');
            case 'book':
                return $this->count_modules_by_type($course, 'book');
            case 'lesson':
                return $this->count_modules_by_type($course, 'lesson');
            case 'scorm':
                return $this->count_modules_by_type($course, 'scorm');
            case 'label':
                return $this->count_modules_by_type($course, 'label');
            default:
                return 0;
        }
    }

    /**
     * Count content items for a section.
     *
     * @param string $content_type Type of content to count
     * @param object $section Section object
     * @param \stdClass $course Course context
     * @return int Content count
     */
    public function count_section_content(string $content_type, object $section, \stdClass $course): int {
        switch ($content_type) {
            case 'modules':
                return $this->count_section_modules($section, $course);
            case 'visible_modules':
                return $this->count_section_visible_modules($section, $course);
            case 'hidden_modules':
                return $this->count_section_hidden_modules($section, $course);
            default:
                // For specific module types, count in this section
                if ($this->is_module_type($content_type)) {
                    return $this->count_section_modules_by_type($section, $course, $content_type);
                }
                return 0;
        }
    }

    /**
     * Count quiz-specific content.
     *
     * @param string $content_type Type of content to count
     * @param object $quiz Quiz object (with cm property)
     * @return int Content count
     */
    public function count_quiz_content(string $content_type, object $quiz): int {
        global $DB;

        switch ($content_type) {
            case 'questions':
                return $this->count_quiz_questions($quiz);
            case 'attempts':
                return $this->count_quiz_attempts($quiz);
            case 'grades':
                return $this->count_quiz_grades($quiz);
            case 'multichoice':
                return $this->count_quiz_questions_by_type($quiz, 'multichoice');
            case 'truefalse':
                return $this->count_quiz_questions_by_type($quiz, 'truefalse');
            case 'shortanswer':
                return $this->count_quiz_questions_by_type($quiz, 'shortanswer');
            case 'numerical':
                return $this->count_quiz_questions_by_type($quiz, 'numerical');
            case 'essay':
                return $this->count_quiz_questions_by_type($quiz, 'essay');
            case 'matching':
                return $this->count_quiz_questions_by_type($quiz, 'matching');
            default:
                return 0;
        }
    }

    /**
     * Count assignment-specific content.
     *
     * @param string $content_type Type of content to count
     * @param object $assign Assignment object (with cm property)
     * @return int Content count
     */
    public function count_assign_content(string $content_type, object $assign): int {
        global $DB;

        switch ($content_type) {
            case 'submissions':
                return $this->count_assign_submissions($assign);
            case 'grades':
                return $this->count_assign_grades($assign);
            case 'feedback_files':
                return $this->count_assign_feedback_files($assign);
            default:
                return 0;
        }
    }

    /**
     * Count total course modules.
     *
     * @param \stdClass $course Course object
     * @return int Module count
     */
    private function count_course_modules(\stdClass $course): int {
        try {
            $modinfo = get_fast_modinfo($course);
            return count($modinfo->get_cms());
        } catch (\Exception $e) {
            debugging('Error counting course modules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count course sections (excluding section 0).
     *
     * @param \stdClass $course Course object
     * @return int Section count
     */
    private function count_course_sections(\stdClass $course): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $sections = $modinfo->get_section_info_all();
            // Exclude section 0 (general section)
            return count($sections) - 1;
        } catch (\Exception $e) {
            debugging('Error counting course sections: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count visible modules in course.
     *
     * @param \stdClass $course Course object
     * @return int Visible module count
     */
    private function count_visible_modules(\stdClass $course): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $visible_count = 0;
            
            foreach ($modinfo->get_cms() as $cm) {
                if ($cm->visible) {
                    $visible_count++;
                }
            }
            
            return $visible_count;
        } catch (\Exception $e) {
            debugging('Error counting visible modules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count hidden modules in course.
     *
     * @param \stdClass $course Course object
     * @return int Hidden module count
     */
    private function count_hidden_modules(\stdClass $course): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $hidden_count = 0;
            
            foreach ($modinfo->get_cms() as $cm) {
                if (!$cm->visible) {
                    $hidden_count++;
                }
            }
            
            return $hidden_count;
        } catch (\Exception $e) {
            debugging('Error counting hidden modules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count modules of a specific type.
     *
     * @param \stdClass $course Course object
     * @param string $module_type Module type (quiz, assign, etc.)
     * @return int Module count
     */
    private function count_modules_by_type(\stdClass $course, string $module_type): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $count = 0;
            
            foreach ($modinfo->get_cms() as $cm) {
                if ($cm->modname === $module_type) {
                    $count++;
                }
            }
            
            return $count;
        } catch (\Exception $e) {
            debugging("Error counting {$module_type} modules: " . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count modules in a specific section.
     *
     * @param object $section Section object
     * @param \stdClass $course Course context
     * @return int Module count
     */
    private function count_section_modules(object $section, \stdClass $course): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $section_info = $modinfo->get_section_info($section->section);
            
            if ($section_info && !empty($section_info->sequence)) {
                return count(explode(',', $section_info->sequence));
            }
            
            return 0;
        } catch (\Exception $e) {
            debugging('Error counting section modules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count visible modules in a specific section.
     *
     * @param object $section Section object
     * @param \stdClass $course Course context
     * @return int Visible module count
     */
    private function count_section_visible_modules(object $section, \stdClass $course): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $section_info = $modinfo->get_section_info($section->section);
            $visible_count = 0;
            
            if ($section_info && !empty($section_info->sequence)) {
                $cmids = explode(',', $section_info->sequence);
                foreach ($cmids as $cmid) {
                    $cm = $modinfo->get_cm($cmid);
                    if ($cm && $cm->visible) {
                        $visible_count++;
                    }
                }
            }
            
            return $visible_count;
        } catch (\Exception $e) {
            debugging('Error counting section visible modules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count hidden modules in a specific section.
     *
     * @param object $section Section object
     * @param \stdClass $course Course context
     * @return int Hidden module count
     */
    private function count_section_hidden_modules(object $section, \stdClass $course): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $section_info = $modinfo->get_section_info($section->section);
            $hidden_count = 0;
            
            if ($section_info && !empty($section_info->sequence)) {
                $cmids = explode(',', $section_info->sequence);
                foreach ($cmids as $cmid) {
                    $cm = $modinfo->get_cm($cmid);
                    if ($cm && !$cm->visible) {
                        $hidden_count++;
                    }
                }
            }
            
            return $hidden_count;
        } catch (\Exception $e) {
            debugging('Error counting section hidden modules: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count modules of a specific type in a section.
     *
     * @param object $section Section object
     * @param \stdClass $course Course context
     * @param string $module_type Module type
     * @return int Module count
     */
    private function count_section_modules_by_type(object $section, \stdClass $course, string $module_type): int {
        try {
            $modinfo = get_fast_modinfo($course);
            $section_info = $modinfo->get_section_info($section->section);
            $count = 0;
            
            if ($section_info && !empty($section_info->sequence)) {
                $cmids = explode(',', $section_info->sequence);
                foreach ($cmids as $cmid) {
                    $cm = $modinfo->get_cm($cmid);
                    if ($cm && $cm->modname === $module_type) {
                        $count++;
                    }
                }
            }
            
            return $count;
        } catch (\Exception $e) {
            debugging("Error counting section {$module_type} modules: " . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count questions in a quiz.
     *
     * @param object $quiz Quiz object
     * @return int Question count
     */
    private function count_quiz_questions(object $quiz): int {
        global $DB;

        try {
            $questions = $DB->get_records('quiz_slots', ['quizid' => $quiz->id]);
            return count($questions);
        } catch (\Exception $e) {
            debugging('Error counting quiz questions: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count quiz attempts.
     *
     * @param object $quiz Quiz object
     * @return int Attempt count
     */
    private function count_quiz_attempts(object $quiz): int {
        global $DB;

        try {
            $attempts = $DB->get_records('quiz_attempts', ['quiz' => $quiz->id]);
            return count($attempts);
        } catch (\Exception $e) {
            debugging('Error counting quiz attempts: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count quiz grades.
     *
     * @param object $quiz Quiz object
     * @return int Grade count
     */
    private function count_quiz_grades(object $quiz): int {
        global $DB;

        try {
            $grades = $DB->get_records('quiz_grades', ['quiz' => $quiz->id]);
            return count($grades);
        } catch (\Exception $e) {
            debugging('Error counting quiz grades: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count quiz questions by type.
     *
     * @param object $quiz Quiz object
     * @param string $question_type Question type
     * @return int Question count
     */
    private function count_quiz_questions_by_type(object $quiz, string $question_type): int {
        global $DB;

        try {
            $sql = "SELECT COUNT(q.id)
                    FROM {quiz_slots} qs
                    JOIN {question} q ON qs.questionid = q.id
                    WHERE qs.quizid = ? AND q.qtype = ?";
            
            return $DB->count_records_sql($sql, [$quiz->id, $question_type]);
        } catch (\Exception $e) {
            debugging("Error counting {$question_type} questions: " . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count assignment submissions.
     *
     * @param object $assign Assignment object
     * @return int Submission count
     */
    private function count_assign_submissions(object $assign): int {
        global $DB;

        try {
            $submissions = $DB->get_records('assign_submission', ['assignment' => $assign->id]);
            return count($submissions);
        } catch (\Exception $e) {
            debugging('Error counting assignment submissions: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count assignment grades.
     *
     * @param object $assign Assignment object
     * @return int Grade count
     */
    private function count_assign_grades(object $assign): int {
        global $DB;

        try {
            $grades = $DB->get_records('assign_grades', ['assignment' => $assign->id]);
            return count($grades);
        } catch (\Exception $e) {
            debugging('Error counting assignment grades: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Count assignment feedback files.
     *
     * @param object $assign Assignment object
     * @return int File count
     */
    private function count_assign_feedback_files(object $assign): int {
        global $DB;

        try {
            // Count files in assignment feedback file areas
            $context = \context_module::instance($assign->cm->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'assignfeedback_file', 'feedback_files');
            
            // Filter out directories
            $file_count = 0;
            foreach ($files as $file) {
                if (!$file->is_directory()) {
                    $file_count++;
                }
            }
            
            return $file_count;
        } catch (\Exception $e) {
            debugging('Error counting assignment feedback files: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Check if a content type is a module type.
     *
     * @param string $content_type Content type to check
     * @return bool True if it's a module type
     */
    private function is_module_type(string $content_type): bool {
        $module_types = [
            'quiz', 'assign', 'forum', 'resource', 'url', 'page', 'book',
            'lesson', 'scorm', 'label', 'choice', 'feedback', 'survey',
            'workshop', 'wiki', 'glossary', 'data', 'chat', 'imscp'
        ];
        
        return in_array($content_type, $module_types);
    }
} 