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
class quiz_has_completion extends rule_base
{

    const rule_key = 'quiz_has_completion';
    const target_type = 'mod';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_quiz_has_completion_name', 'block_course_audit'),
            get_string('rule_quiz_has_completion_description', 'block_course_audit'),
            'action'
            //get_string('rule_category_hint', 'block_course_audit')
        );
    }

    /**
     * Check if a quiz has completion enabled
     *
     * @param object $target The target to check
     * @param object $course The course the target belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_target($target, $course = null)
    {
        global $DB;

        $quiz = $DB->get_record('quiz', ['id' => $target->id], 'id, attempts');

        if (!$quiz) {
            return null;
        }

        // TODO check if completion is enabled
        if ((int)$quiz->attempts === 0) {
            return $this->create_result(true, []);
        } else {
            return $this->create_result(false, ['Quiz allows ' . $quiz->attempts . ' attempt(s).']);
        }
    }

    /**
     * @param object $context Context containing rule result details like target_id.
     * @return array|null Action button details.
     */
    public function get_action_button_details($target_id = null, $courseid = null)
    {
        return null;
        //TODO: Implement
        if (!$context || $context->status === true || empty($context->rule_target_id)) {
            return null;
        }

        return [
            'mapkey' => 'section_' . $context->rule_target_id . '_' . self::rule_key,
            'label' => get_string('button_enable_completion', 'block_course_audit'),
            'endpoint' => 'block_course_audit_enable_completion',
            'params' => 'modid=' . $context->rule_target_id . '&courseid=' . $courseid
        ];
    }
}
