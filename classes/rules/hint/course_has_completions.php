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
 * Rule that checks whether a course has completion tracking configured.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/completionlib.php');

use block_course_audit\rules\rule_base;

/**
 * Ensures that course completion tracking is enabled and activities participate.
 */
class course_has_completions extends rule_base
{
    /** @var string Rule identifier. */
    public const rule_key = 'course_has_completions';

    /** @var string Target that the rule evaluates. */
    public const target_type = 'course';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_course_has_completions_name', 'block_course_audit'),
            get_string('rule_course_has_completions_description', 'block_course_audit'),
            'hint'
        );
    }

    /**
     * Evaluate completion configuration.
     *
     * @param object $target Course object under evaluation.
     * @param object|null $course Optional course reference (unused, kept for interface compatibility).
     * @return object|null
     */
    public function check_target($target, $course = null)
    {
        $course = $course ?? $target;

        if (empty($course) || empty($course->id)) {
            return null;
        }

        $completioninfo = new \completion_info($course);

        if (!$completioninfo->is_enabled()) {
            return $this->create_result(
                false,
                [get_string('rule_course_has_completions_disabled', 'block_course_audit')],
                $course->id,
                $course->id
            );
        }

        $modinfo = get_fast_modinfo($course);
        $activitieswithcompletion = 0;

        foreach ($modinfo->get_cms() as $cm) {
            if ((int)$cm->completion !== COMPLETION_TRACKING_NONE) {
                $activitieswithcompletion++;
            }
        }

        if ($activitieswithcompletion === 0) {
            return $this->create_result(
                false,
                [get_string('rule_course_has_completions_no_activities', 'block_course_audit')],
                $course->id,
                $course->id
            );
        }

        $message = get_string('rule_course_has_completions_success', 'block_course_audit', (object) [
            'count' => $activitieswithcompletion,
        ]);

        return $this->create_result(true, [$message], $course->id, $course->id);
    }
}


