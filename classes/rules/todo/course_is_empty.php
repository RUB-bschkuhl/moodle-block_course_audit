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

        namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_base.php');

use block_course_audit\rules\rule_base;

/**
 * Rule that checks if a course is empty.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_is_empty extends rule_base {

    const rule_key = 'course_is_empty';
    const target_type = 'course';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_course_is_empty_name', 'block_course_audit'),
            get_string('rule_course_is_empty_description', 'block_course_audit'),
            'hint'
            //get_string('rule_category_hint', 'block_course_audit')
        );
    }
    
    /**
     * Check if a course is empty
     *
     * @param object $course The course to check
     * @return object Result object with 'status' (boolean) and 'messages' (array of string)
     */
    public function check_target($target, $course = null) {
        return $this->create_result(true, []);
    }
} 