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
 * External API for creating tours.
 *
 * @package block_course_audit
 * @copyright 2024 Your Name <your.email@example.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;
use block_course_audit\tour\manager as tour_manager;
use block_course_audit\audit\auditor;
use moodle_exception;
use tool_usertours\tour;
use tool_usertours\helper;
use tool_usertours\target;

/**
 * External API for creating tours
 *
 * @package block_course_audit
 * @copyright 2024 Your Name <your.email@example.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_tour extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course ID to create the tour for')
        ]);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of the operation'),
            'message' => new external_value(PARAM_TEXT, 'Response message'),
            'tourdata' => new external_single_structure([
                'tourDetails' => new external_multiple_structure(
                    new external_single_structure([
                        'tourId' => new external_value(PARAM_INT, 'ID of the tour'),
                        'startTour' => new external_value(PARAM_BOOL, 'Whether the tour starts immediately'),
                        'filtervalues' => new external_single_structure([
                            'cssselector' => new external_multiple_structure(
                                new external_value(PARAM_TEXT, 'A single CSS selector'),
                                'List of CSS selectors'
                            )
                        ], 'Filter values object', VALUE_OPTIONAL)

                    ]),
                    'List of tours in tourdata'
                ),
                'filterNames' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'A filter name'),
                    'List of filter names',
                    VALUE_OPTIONAL
                )
            ])
        ]);
    }


    /**
     *
     */
    private static function init_tour($tour)
    {
        // Needed because finished core tours might block init otherwise.
        global $PAGE;
        $filters = helper::get_all_clientside_filters();

        // Create tourdetails for init
        $tourdetails = array_map(function ($t) use ($filters) {
            $filtervalues = $t->get_client_filter_values($filters);

            // Ensure cssselector is an array as expected by external_multiple_structure.
            if (isset($filtervalues['cssselector']) && is_object($filtervalues['cssselector'])) {
                // Convert stdClass object {0 => 'selector'} to array ['selector']
                $filtervalues['cssselector'] = array_values((array)$filtervalues['cssselector']);
            } else if (!isset($filtervalues['cssselector'])) {
                // Ensure it's at least an empty array if not set, to match the structure.
                $filtervalues['cssselector'] = [];
            }

            // Ensure other potential filter value types are also arrays if needed
            // Example for a hypothetical 'role' filter:
            // if (isset($filtervalues['role']) && is_object($filtervalues['role'])) {
            // $filtervalues['role'] = array_values((array)$filtervalues['role']);
            // }

            return [
                'tourId' => $t->get_id(),
                'startTour' => $t->should_show_for_user(),
                'filtervalues' => $filtervalues, // Now filtervalues['cssselector'] is guaranteed to be an array
            ];
        }, [$tour]);

        $filternames = helper::get_clientside_filter_module_names($filters);

        return [
            'tourDetails' => $tourdetails,
            'filterNames' => $filternames
        ];
    }

    /**
     * Create a course audit tour
     *
     * @param int $courseid The course ID
     * @return array Operation status and response message
     */
    public static function execute($courseid)
    {
        global $DB, $USER;

        // Parameter validation
        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);
        $courseid = $params['courseid'];

        // Get the course context
        $coursecontext = context_course::instance($courseid);

        // Validate the context exists and is the correct type
        self::validate_context($coursecontext);

        // Check if the user has permission to manage activities in this course context.
        // This throws an exception if the user lacks the capability.
        require_capability('moodle/course:manageactivities', $coursecontext);
        mtrace("Capability 'moodle/course:manageactivities' checked successfully for User ID: {$USER->id} in Context ID: {$coursecontext->id}");

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // Create the tour manager
        $manager = new tour_manager();

        // Check if a tour already exists for this course and delete it
        $existingtours = $DB->get_records_sql(
            "SELECT t.* FROM {tool_usertours_tours} t JOIN {block_course_audit_tours} ca ON t.id = ca.tourid WHERE ca.courseid = ? AND t.name LIKE ?",
            [$courseid, "Course Audit: Course #$courseid%"]
        );

        // If a tour already exists, delete it using the tour manager
        foreach ($existingtours as $existingtour) {
            try {
                // Call the instance method delete_tour on the existing $manager object
                $manager->delete_tour($existingtour->id);
                mtrace("Deleted existing tour ID: {$existingtour->id} for course ID: {$courseid}");
                // Also delete the corresponding entry in block_course_audit_tours
                $DB->delete_records('block_course_audit_tours', ['tourid' => $existingtour->id]);
            } catch (\Exception $e) {
                // Log the error but continue, as we are creating a new tour anyway
                mtrace("Error deleting existing tour ID: {$existingtour->id}. Error: " . $e->getMessage());
            }
        }

        // Execute audit
        $auditor = new auditor();
        $audit_data = $auditor->get_audit_results($course);
        $tour_steps_data = $audit_data['tour_steps']; // Data for creating tour steps
        $raw_audit_results = $audit_data['raw_results']; // Raw results for DB storage

        // Check if we actually got any steps/results
        if (empty($tour_steps_data)) {
            return [
                'status' => false,
                'message' => get_string('noauditresults', 'block_course_audit'), // Add this string
                'tourdata' => null
            ];
        }

        // Create a new user tour
        $pathmatch = "/course/view.php?id=$courseid";
        $tourconfig = [
            'displaystepnumbers' => true,
            'showtourwhen' => tour::SHOW_TOUR_UNTIL_COMPLETE,
            'backdrop' => true,
            'reflex' => false
        ];

        $tour = $manager->create_tour(
            "Course Audit: Course #$courseid",
            "This tour will guide the user through the course audit for $course->fullname",
            $pathmatch,
            $tourconfig
        );

        // Record the main audit run in our table FIRST
        $auditrunrecord = new \stdClass();
        $auditrunrecord->tourid = $tour->get_id();
        $auditrunrecord->courseid = $courseid;
        $auditrunrecord->timecreated = time();
        $auditrunrecord->timemodified = time();
        $auditrunid = $DB->insert_record('block_course_audit_tours', $auditrunrecord); // Get the ID of this audit run

        // Now, try to create steps and store individual results
        try {
            // Create tour steps
            foreach ($tour_steps_data as $step_data) {
                // Ensure target selector is valid
                $targetselector = '#' . $step_data['type'] . '-' . $step_data['number'];
                // Potentially add checks here if target might not exist

                $manager->add_step(
                    $step_data['title'],
                    $step_data['content'],
                    target::TARGET_SELECTOR,
                    $targetselector,
                    ['placement' => 'right', 'backdrop' => true]
                );
            }

            // Store individual audit results
            $now = time();
            foreach ($raw_audit_results as $result) {
                $resultrecord = new \stdClass();
                $resultrecord->auditid = $auditrunid;
                $resultrecord->rulekey = $result['key']; // Assuming 'key' field exists
                $resultrecord->status = $result['status']; // Assuming 'status' field exists
                $resultrecord->message = $result['message'] ?? null; // Assuming 'message' field exists
                $resultrecord->targettype = $result['targettype'] ?? null; // Assuming 'targettype' field exists
                $resultrecord->targetid = $result['targetid'] ?? null; // Assuming 'targetid' field exists
                $resultrecord->timecreated = $now;
                $DB->insert_record('block_course_audit_results', $resultrecord);
            }

            // Reset tour for users only after steps and results are successfully created
            $manager->reset_tour_for_all_users($tour->get_id());

            $tourdata = self::init_tour($tour);
            $resp = [
                'status' => true,
                'message' => get_string('createtoursuccess', 'block_course_audit'), // Add this string
                'tourdata' => $tourdata
            ];
            return $resp;

        } catch (\Exception $e) {
            // If any step or result saving fails, delete the tour and the audit run record
            mtrace("Error during step creation or result saving for tour ID: {$tour->get_id()}. Error: " . $e->getMessage());
            try {
                \block_course_audit\tour_manager::delete_tour($tour->get_id());
                // Also delete the main audit run record if steps failed
                $DB->delete_records('block_course_audit_tours', ['id' => $auditrunid]);
            } catch (\Exception $delEx) {
                // Log deletion error but prioritize original exception
                mtrace("Failed to clean up tour ID: {$tour->get_id()} after error. Deletion Error: " . $delEx->getMessage());
            }
            // Re-throw the original exception
            throw $e;
        }
    }
}
