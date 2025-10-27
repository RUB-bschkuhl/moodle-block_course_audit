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
 * Hint rule ensuring modules contain meaningful descriptions.
 *
 * @package   block_course_audit
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

use block_course_audit\rules\rule_base;

/**
 * Module description presence rule.
 */
class mod_has_description extends rule_base
{
    /** @var string Unique rule key. */
    public const rule_key = 'mod_has_description';

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
            get_string('rule_mod_has_description_name', 'block_course_audit'),
            get_string('rule_mod_has_description_description', 'block_course_audit'),
            'hint'
        );
    }

    /**
     * Evaluate module description.
     *
     * @param object $target Course module.
     * @param object|null $course Parent course.
     * @return object|null
     */
    public function check_target($target, $course = null)
    {
        if (empty($target) || empty($course)) {
            return null;
        }

        // Resource-like modules expose intro/introformat. Others use content or description fields.
        $hasintrotext = false;

        if (property_exists($target, 'intro')) {
            $hasintrotext = trim(strip_tags($target->intro)) !== '';
        } elseif (property_exists($target, 'content')) {
            $hasintrotext = trim(strip_tags($target->content)) !== '';
        }

        if ($hasintrotext) {
            return $this->create_result(
                true,
                [get_string('rule_mod_has_description_success', 'block_course_audit')],
                $target->id ?? null,
                $course->id ?? null
            );
        }

        $message = get_string('rule_mod_has_description_missing', 'block_course_audit', (object) [
            'name' => format_string($target->name ?? ''),
        ]);

        return $this->create_result(false, [$message], $target->id ?? null, $course->id ?? null);
    }
}


