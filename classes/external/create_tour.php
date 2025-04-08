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
        global $DB;

        // Parameter validation
        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);
        $courseid = $params['courseid'];

        // Get the course context and check capability
        $coursecontext = context_course::instance($courseid);
        self::validate_context($coursecontext);
        require_capability('block/course_audit:view', $coursecontext);

        // Get course information
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // Create the tour manager
        $manager = new tour_manager();

        // Check if a tour already exists for this course
        $existingtours = $DB->get_records_sql(
            "SELECT * FROM {tool_usertours_tours} WHERE pathmatch = ? AND name LIKE ?",
            ["/course/view.php\\?id=$courseid", "Course Audit: Course #$courseid%"]
        );

        // If a tour already exists, delete it
        foreach ($existingtours as $existingtour) {
            $manager->delete_tour($existingtour->id);
        }

        // Execute audit
        $auditor = new auditor();
        $audit_results = $auditor->get_audit_results($course);

        // Create a new tour
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

        // Create steps for the tour with error handling
        try {
            foreach ($audit_results as $audit_result) {
                if ($audit_result->type == "section") {
                    $manager->add_step(
                        $audit_result->title,
                        $audit_result->content,
                        target::TARGET_SELECTOR,
                        "#$audit_result->type" . "-" . "$audit_result->number",
                        ['placement' => 'right', 'backdrop' => true]
                    );
                }
                // TODO else if (mod und course)
            }
        } catch (\Exception $e) {
            // If any step fails to be created, delete the tour
            debugging('Failed to create tour step: ' . $e->getMessage() . '. Deleting tour.', DEBUG_DEVELOPER);
            $manager->delete_tour($tour->get_id());

            // Re-throw the exception to be caught by the outer try-catch
            throw $e;
        }

        $manager->reset_tour_for_all_users($tour->get_id());

        // Store the tour reference in our plugin's database table
        $tourrecord = new \stdClass();
        $tourrecord->tourid = $tour->get_id();
        $tourrecord->courseid = $courseid;
        $tourrecord->timecreated = time();
        $tourrecord->timemodified = time();
        $DB->insert_record('block_course_audit_tours', $tourrecord);

        $tourdata = self::init_tour($tour);
        $resp = [
            'status' => true,
            'message' => 'Tour created successfully',
            'tourdata' => $tourdata
        ];
        return $resp;
    }
}
