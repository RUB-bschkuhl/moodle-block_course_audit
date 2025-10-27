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
 * Hint rule that checks whether a course contains learning sections or activities.
 *
 * @package   block_course_audit
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

use block_course_audit\rules\rule_base;

/**
 * Course emptiness check.
 */
class course_is_empty extends rule_base
{
    /** @var string Rule identifier. */
    public const rule_key = 'course_is_empty';

    /** @var string Target for the rule. */
    public const target_type = 'course';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_course_is_empty_name', 'block_course_audit'),
            get_string('rule_course_is_empty_description', 'block_course_audit'),
            'hint'
        );
    }

    /**
     * Evaluate if the course contains any visible content.
     *
     * @param object $target Course object.
     * @param object|null $course Optional course reference.
     * @return object|null
     */
    public function check_target($target, $course = null)
    {
        $course = $course ?? $target;

        if (empty($course) || empty($course->id)) {
            return null;
        }

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        $hasvisiblemodules = false;
        $hasvisibleactivities = false;

        foreach ($sections as $sectioninfo) {
            if ($sectioninfo->section == 0 || !$sectioninfo->visible) {
                continue;
            }

            $hasvisiblesections = true;

            if (!empty($modinfo->sections[$sectioninfo->section])) {
                foreach ($modinfo->sections[$sectioninfo->section] as $cmid) {
                    $cm = $modinfo->get_cm($cmid);
                    if ($cm->uservisible && !$cm->deletioninprogress) {
                        $hasvisiblemodules = true;
                        break 2;
                    }
                }
            }
        }

        if (empty($hasvisiblesections ?? false)) {
            return $this->create_result(
                false,
                [get_string('rule_course_is_empty_no_sections', 'block_course_audit')],
                $course->id,
                $course->id
            );
        }

        if (!$hasvisiblemodules) {
            return $this->create_result(
                false,
                [get_string('rule_course_is_empty_no_modules', 'block_course_audit')],
                $course->id,
                $course->id
            );
        }

        return $this->create_result(
            true,
            [get_string('rule_course_is_empty_success', 'block_course_audit')],
            $course->id,
            $course->id
        );
    }
}


