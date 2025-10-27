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
 * Hint rule validating course format information.
 *
 * @package   block_course_audit
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

use block_course_audit\rules\rule_base;

/**
 * Checks whether the course format is supported and configured.
 */
class course_has_format extends rule_base
{
    /** @var string Unique rule identifier. */
    public const rule_key = 'course_has_format';

    /** @var string Target this rule operates on. */
    public const target_type = 'course';

    /** @var array Preferred formats with description language keys. */
    private const preferredformats = [
        'topics' => 'rule_course_has_format_topics',
        'weekly' => 'rule_course_has_format_weekly',
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_course_has_format_name', 'block_course_audit'),
            get_string('rule_course_has_format_description', 'block_course_audit'),
            'hint'
        );
    }

    /**
     * Evaluate course format state.
     *
     * @param object $target Course under evaluation.
     * @param object|null $course Optional course reference.
     * @return object|null
     */
    public function check_target($target, $course = null)
    {
        $course = $course ?? $target;

        if (empty($course) || empty($course->id)) {
            return null;
        }

        if (empty($course->format)) {
            return $this->create_result(
                false,
                [get_string('rule_course_has_format_missing', 'block_course_audit')],
                $course->id,
                $course->id
            );
        }

        $format = (string) $course->format;
        $preferredkey = self::preferredformats[$format] ?? null;

        if ($preferredkey !== null) {
            return $this->create_result(
                true,
                [get_string($preferredkey, 'block_course_audit')],
                $course->id,
                $course->id
            );
        }

        $message = get_string('rule_course_has_format_nonpreferred', 'block_course_audit', (object) [
            'format' => format_string($format),
        ]);

        $suggestions = array_map(function ($langkey) {
            return get_string($langkey, 'block_course_audit');
        }, array_values(self::preferredformats));

        $messages = array_merge([$message], $suggestions);

        return $this->create_result(false, $messages, $course->id, $course->id);
    }
}


