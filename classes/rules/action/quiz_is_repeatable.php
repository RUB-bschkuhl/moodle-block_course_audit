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
class quiz_is_repeatable extends rule_base
{

    const rule_key = 'quiz_is_repeatable';
    const target_type = 'mod';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_quiz_is_repeatable_name', 'block_course_audit'),
            get_string('rule_quiz_is_repeatable_description', 'block_course_audit'),
            'action'
            //get_string('rule_category_hint', 'block_course_audit')
        );
    }

    /**
     * Check if a quiz is repeatable
     *
     * @param object $target The target to check
     * @param object $course The course the target belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_target($target, $course = null)
    {
        global $DB;
        
        if ($target->modname !== 'quiz') {
            return null;
        }

        try {
            $quiz = $DB->get_record('quiz', ['id' => $target->instance], 'id, attempts');

            if (!$quiz) {
                return null;
            }

            // 0 means unlimited attempts.
            if ((int)$quiz->attempts === 0) {
                return $this->create_result(true, []);
            } else {
                $messages[] = get_string('rule_quiz_is_repeatable_failure', 'block_course_audit', 
                    ['attempts' => $quiz->attempts]);
                return $this->create_result(false, $messages, $target->id, $target->course);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param object $context Context containing rule result details like target_id.
     * @return array|null Action button details.
     */
    public function get_action_button_details($target_id = null, $courseid = null)
    {
        if (empty($target_id) || empty($courseid)) {
            return null;
        }
        
        $action_button_details = [];
        $action_button_details[] = [
            'mapkey' => 'mod_' . $target_id . '_' . self::rule_key,
            'label' => get_string('button_enable_repeatable', 'block_course_audit'),
            'endpoint' => 'block_course_audit_enable_repeatable',
            'params' => 'modid=' . $target_id . '&courseid=' . $courseid
        ];
        return $action_button_details;
    }
}
