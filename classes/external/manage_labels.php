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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * External API for managing labels.
 *
 * @package block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php'); // For course/module functions
require_once($CFG->dirroot . '/course/modlib.php'); // For add_moduleinfo

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;
use stdClass;
use moodle_exception;

/**
 * External API for managing labels within the course audit block.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_labels extends external_api {
    /**
     * Parameters for the execute function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'sectionid' => new external_value(PARAM_INT, 'The section ID to add the label to'),
            'courseid' => new external_value(PARAM_INT, 'The course ID where the section belongs')
        ]);
    }

    /**
     * Value returned by the execute function.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'True if the label was added successfully, false otherwise.'),
            'message' => new external_value(PARAM_TEXT, 'Success or error message.'),
            'cmid' => new external_value(PARAM_INT, 'The course module ID of the newly added label', VALUE_OPTIONAL)
        ]);
    }

    /**
     * Adds a new label module to a specific course section.
     *
     * @param int $sectionid Section ID.
     * @param int $courseid Course ID.
     * @return array Status array.
     * @throws moodle_exception
     */
    public static function execute(int $sectionid, int $courseid) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'sectionid' => $sectionid,
            'courseid' => $courseid
        ]);

        // Get course context and validate it.
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);

        // Check capability.
        require_capability('moodle/course:manageactivities', $context);

        // Get course and section info.
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $section = $DB->get_record('course_sections', ['id' => $params['sectionid'], 'course' => $course->id], '*', MUST_EXIST);

        // Get the module ID for the label module.
        $labelmodule = $DB->get_record('modules', ['name' => 'label'], '*', MUST_EXIST);

        $label = new stdClass();
        $label->sortorder = 0; //TODO doesnt work
        
        $label->course = $course->id;
        $label->modulename = 'label';
        $label->name = get_string('label_name', 'block_course_audit');
        $label->intro = get_string('label_intro', 'block_course_audit');
        $label->introformat = FORMAT_HTML;
        $label->visible = 1;
        $label->timemodified = time();
        $label->section = $section->section;

        list($module, $context, $cw) = can_add_moduleinfo($course, $label->modulename, $label->section);

        $label->module = $module->id;

        $cm = add_moduleinfo($label, $course);

        if (!$cm) {
            throw new moodle_exception('erroraddlabel', 'block_course_audit'); 
        }

        return [
            'status' => true,
            'message' => get_string('labeladdedsuccess', 'block_course_audit')
        ];
    }
} 