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
 * Hint rule checking quiz availability connections or completion dependencies.
 *
 * @package   block_course_audit
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

use block_course_audit\rules\rule_base;

/**
 * Quiz connection hint.
 */
class quiz_has_connections extends rule_base
{
    /** @var string Rule identifier. */
    public const rule_key = 'quiz_has_connections';

    /** @var string Target type. */
    public const target_type = 'mod';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_quiz_has_connections_name', 'block_course_audit'),
            get_string('rule_quiz_has_connections_description', 'block_course_audit'),
            'hint'
        );
    }

    /**
     * Evaluate quiz dependencies.
     *
     * @param object $target Course module (quiz).
     * @param object|null $course Parent course.
     * @return object|null
     */
    public function check_target($target, $course = null)
    {
        if (empty($target) || $target->modname !== 'quiz') {
            return null;
        }

        $hasavailability = !empty($target->availability) && trim($target->availability) !== '';
        $hascompletiondependency = false;

        if ($hasavailability) {
            $decoded = json_decode($target->availability);
            if (is_object($decoded) && !empty($decoded->c)) {
                foreach ($decoded->c as $condition) {
                    if (isset($condition->type) && $condition->type === 'completion') {
                        $hascompletiondependency = true;
                        break;
                    }
                }
            }
        }

        if ($hascompletiondependency) {
            return $this->create_result(
                true,
                [get_string('rule_quiz_has_connections_success', 'block_course_audit')],
                $target->id ?? null,
                $course->id ?? null
            );
        }

        $messages = [get_string('rule_quiz_has_connections_missing', 'block_course_audit')];

        return $this->create_result(false, $messages, $target->id ?? null, $course->id ?? null);
    }
}


