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
 * Rule interface for the course_audit block.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for rules in course_audit
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface rule_interface {
    /**
     * Check a section against this rule
     *
     * @param object $section The section to check
     * @param object $course The course the section belongs to
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_section($section, $course);

    /**
     * Get the rule name (for display)
     *
     * @return string
     */
    public function get_name();

    /**
     * Get the rule description
     *
     * @return string
     */
    public function get_description();

    /**
     * Get the rule category
     *
     * @return string One of 'activity_type', 'activity_flow'
     */
    public function get_category();
} 