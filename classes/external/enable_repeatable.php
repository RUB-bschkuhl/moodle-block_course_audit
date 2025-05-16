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
 * External function to enable repeatable attempts for a quiz.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/external/classes/external_api.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php'); // For mod_quiz_after_update

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use context_module;
use moodle_exception;

class enable_repeatable extends external_api {

    /**
     * Define parameters for the external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'modid' => new external_value(PARAM_INT, 'The course module ID of the quiz.'),
            'courseid' => new external_value(PARAM_INT, 'The course ID where the quiz resides.')
        ]);
    }

    /**
     * Execute the function to set quiz attempts to 0.
     *
     * @param int $modid The course module ID of the quiz.
     * @param int $courseid The course ID.
     * @return array Status of the operation.
     * @throws moodle_exception
     */
    public static function execute($modid, $courseid) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), ['modid' => $modid, 'courseid' => $courseid]);

        // Get context and check capability.
        $cm = get_coursemodule_from_id('quiz', $params['modid'], $params['courseid'], true, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        // Fetch the full quiz object as mod_quiz_after_update expects it.
        $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

        if (!$quiz) {
            throw new moodle_exception('quiznotfound', 'block_course_audit', '', ['id' => $cm->instance]);
        }

        // Already unlimited?
        if ((int)$quiz->attempts === 0) {
            return ['status' => true, 'message' => get_string('repeatalreadyenabled', 'block_course_audit')];
        }

        $transaction = $DB->start_delegated_transaction();

        try {
            $quiz->attempts = 0;
            $quiz->timemodified = time();
            // Update the quiz record in the database.
            if (!$DB->update_record('quiz', $quiz)) {
                throw new moodle_exception('errorupdatequiz', 'block_course_audit');
            }

            // Call Moodle's internal function to handle post-update actions for the quiz.
            // This is crucial for gradebook updates, event triggering, etc.
            \mod_quiz_after_update($quiz, $cm, true);

            // Log the action.
            $event = \block_course_audit\event\quiz_attempts_updated::create([
                'context' => $context,
                'objectid' => $quiz->id,
                'courseid' => $cm->course,
                'relateduserid' => $USER->id,
                'other' => ['attempts' => 0]
            ]);
            $event->trigger();

            $transaction->allow_commit();
            return ['status' => true, 'message' => get_string('repeatenabledsuccess', 'block_course_audit')];

        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw $e; // Re-throw the exception to be caught by Moodle's external lib error handler.
        }
    }

    /**
     * Define the return value structure for the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'True if the operation was successful, false otherwise.'),
            'message' => new external_value(PARAM_TEXT, 'A message describing the outcome.')
        ]);
    }
} 