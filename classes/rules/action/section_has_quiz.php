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

namespace block_course_audit\rules\action;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_base.php');

use block_course_audit\rules\rule_base;

/**
 * Rule that checks if a course has a section.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_has_quiz extends rule_base
{
    const rule_key = 'section_has_quiz';
    const target_type = 'section';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_section_has_quiz_name', 'block_course_audit'),
            get_string('rule_section_has_quiz_description', 'block_course_audit'),
            'action',
        );
    }

    /**
     * Check if a course has a section
     *
     * @param object $target The target to check
     * @param object $course The course the target belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_target($target, $course = null)
    {
        if (!isset($target->id) || !isset($target->modules)) {
            if (isset($target->id)) {
                return $this->create_result(false, [
                    get_string('rule_section_has_quiz_empty_section', 'block_course_audit')
                ], $target->id, $course->id ?? null);
            } else {
                return $this->create_result(false, [
                    get_string('error_invalid_target_object', 'block_course_audit')
                ], null, $course->id ?? null);
            }
        }

        $section = $target;

        // If no modules in section, return false
        if (empty($section->modules)) {
            return $this->create_result(false, [
                get_string('rule_section_has_quiz_empty_section', 'block_course_audit')
            ], $section->id, $course->id);
        }

        foreach ($section->modules as $module) {
            if ($module->modname === 'quiz') {
                return $this->create_result(true, [
                    get_string('rule_section_has_quiz_success', 'block_course_audit', (object)['quizname' => $module->name])
                ], $section->id, $course->id);
            }
        }

        return $this->create_result(false, [
            get_string('rule_section_has_quiz_failure', 'block_course_audit')
        ], $section->id, $course->id);
    }

    /**
     * Get details for the action button (add a label).
     *
     * @param int $target_id Target ID of the rule result if needed for the action.
     * @return array|null Action button details.
     */
    public function get_action_button_details($target_id = null, $courseid = null)
    {
        // Only show button if the check failed (no label found) and we have a section ID.
        if (!$target_id) {
            return null;
        }

        // Button details for an AJAX action to add a label
        $action_button_details = [];
        $action_button_details[] = [
            'mapkey' => 'section_' . $target_id . '_' . self::rule_key,
            'label' => get_string('button_add_quiz', 'block_course_audit'),
            'endpoint' => 'block_course_audit_manage_quiz',
            'params' => 'sectionid=' . $target_id . '&courseid=' . $courseid
        ];
        return $action_button_details;
    }
}
