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
 * External functions definitions for block_course_audit.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/mod/quiz/locallib.php"); // Required for quiz specific functions

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;
use moodle_exception;

/**
 * Add quiz external function definition.
 */
class manage_quiz extends external_api {
    /**
     * Define parameters for the manage_quiz function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'sectionid' => new external_value(PARAM_INT, 'The ID of the section to add the quiz to'),
                'courseid' => new external_value(PARAM_INT, 'The ID of the course')
            ]
        );
    }

    /**
     * Define the return value for the manage_quiz function.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'success' => new external_value(PARAM_BOOL, 'Indicates if the operation was successful'),
                'message' => new external_value(PARAM_TEXT, 'Success or error message')
            ]
        );
    }

    /**
     * Add a basic quiz to a section.
     *
     * @param int $sectionid The ID of the section.
     * @param int $courseid The ID of the course.
     * @return array Result indicating success or failure.
     * @throws moodle_exception
     */
    public static function execute($sectionid, $courseid) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'sectionid' => $sectionid,
            'courseid' => $courseid
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        $section = $DB->get_record('course_sections', ['id' => $params['sectionid'], 'course' => $params['courseid']], '*', MUST_EXIST);

        $quizdata = new \stdClass();
        $quizdata->course = $params['courseid'];
        $quizdata->name = get_string('quiz_name_default', 'block_course_audit'); // Default name
        $quizdata->intro = get_string('quiz_intro_default', 'block_course_audit'); // Default intro
        $quizdata->introformat = FORMAT_HTML;
        $quizdata->timemodified = time();
        $quizdata->timeopen = 0; // Default: no open date
        $quizdata->timeclose = 0; // Default: no close date
        $quizdata->timelimit = 0; // Default: no time limit
        $quizdata->preferredbehaviour = 'deferredfeedback'; // Default behaviour
        $quizdata->attempts = 0; // Unlimited attempts
        $quizdata->grademethod = QUIZ_GRADEHIGHEST; // Default grading
        $quizdata->decimalpoints = 2;
        $quizdata->questiondecimalpoints = -1;
        $quizdata->shuffleanswers = 1;
        $quizdata->shufflequestions = 0;

        $quizdata->id = $DB->insert_record('quiz', $quizdata);

        if (!$quizdata->id) {
            throw new moodle_exception('quiz_added_failure', 'block_course_audit', null, 'Failed to insert quiz record');
        }

        // Prepare module data.
        $moduledata = new \stdClass();
        $moduledata->course = $params['courseid'];
        $moduledata->module = $DB->get_field('modules', 'id', ['name' => 'quiz']);
        $moduledata->instance = $quizdata->id;
        $moduledata->section = $section->id; // Use the section ID here.
        $moduledata->added = time();
        $moduledata->visible = 1;
        $moduledata->visibleoncoursepage = 1;

        // Add the quiz module using course lib function.
        // Note: add_moduleinfo expects a slightly different object structure for adding
        if (!($cmid = add_course_module($moduledata))) {
            // Clean up the quiz record if module creation failed.
            $DB->delete_records('quiz', ['id' => $quizdata->id]);
            throw new moodle_exception('quiz_added_failure', 'block_course_audit', null, 'Failed to add course module');
        }

        // Set the sequence correctly for the new module in the section.
        $moduledata->id = $cmid;
        $DB->set_field('course_sections', 'sequence', $section->sequence . "," . $cmid, ['id' => $section->id]);

        rebuild_course_cache($params['courseid']);

        return [
            'success' => true,
            'message' => get_string('quiz_added_success', 'block_course_audit')
        ];
    }
} 