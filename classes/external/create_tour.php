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
            ], 'Tour data required by the frontend tour library', VALUE_REQUIRED),
            'actionDetailsMap' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'mapkey' => new external_value(PARAM_TEXT, 'Map key'),
                        'label' => new external_value(PARAM_TEXT, 'Label'),
                        'endpoint' => new external_value(PARAM_TEXT, 'Endpoint'),
                        'params' => new external_value(PARAM_TEXT, 'Params'),
                    ],
                    'Action details map',
                    VALUE_OPTIONAL
                )
            )
        ]);
    }

    private static function init_tour_data($tour)
    {
        $filters = helper::get_all_clientside_filters();
        $tourdetails = array_map(function ($t) use ($filters) {
            $filtervalues = $t->get_client_filter_values($filters);

            if (isset($filtervalues['cssselector']) && is_object($filtervalues['cssselector'])) {
                $filtervalues['cssselector'] = array_values((array)$filtervalues['cssselector']);
            } else if (!isset($filtervalues['cssselector'])) {
                $filtervalues['cssselector'] = [];
            }

            return [
                'tourId' => $t->get_id(),
                'startTour' => $t->should_show_for_user(),
                'filtervalues' => $filtervalues,
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
     * @return array Operation status, message, tour data, and action details map
     */
    public static function execute($courseid)
    {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);
        $courseid = $params['courseid'];

        $coursecontext = context_course::instance($courseid);

        self::validate_context($coursecontext);

        require_capability('moodle/course:manageactivities', $coursecontext);
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        //TODO: Delete the audit tour in tour_manager instead
        self::delete_existing_tours($courseid);

        $auditor = new auditor();
        $audit_data = $auditor->get_audit_results($course);
        $tour_steps_data = $audit_data['tour_steps'];
        $raw_audit_results = $audit_data['raw_results'];
        $action_details_map = $audit_data['action_details_map'];

        if (empty($tour_steps_data) && empty($raw_audit_results)) {
            return [
                'status' => false,
                'message' => get_string('noauditresults', 'block_course_audit'),
                'tourdata' => [
                    'tourDetails' => [],
                    'filterNames' => []
                ],
                'actionDetailsMap' => []
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

        $manager = new tour_manager();

        $tour = $manager->create_tour(
            "Course Audit: Course #$courseid",
            "This tour will guide the user through the course audit for $course->fullname",
            $pathmatch,
            $tourconfig
        );

        //TODO: Store the audit tour in tour_manager instead
        $auditrunid = self::store_audit_tour($tour, $courseid);

        try {

            //TODO: Add steps in tour_manager instead
            foreach ($tour_steps_data as $step_data) {
                $targetselector = '#' . $step_data['type'] . '-' . $step_data['number'];
                switch ($step_data['type']) {
                    case 'section':
                        $targetselector = '#' . $step_data['type'] . '-' . $step_data['number'];
                        $targettype = target::TARGET_SELECTOR;
                        break;
                    case 'mod':
                        $targetselector = '#module-' . $step_data['number'];
                        $targettype = target::TARGET_SELECTOR;
                        break;
                    case 'course':
                        $targetselector = '';
                        $targettype = target::TARGET_UNATTACHED;
                        break;
                }

                $manager->add_step(
                    $step_data['title'],
                    $step_data['content'],
                    $targettype,
                    $targetselector,
                    ['placement' => 'right', 'backdrop' => true]
                );
            }

            self::store_audit_results($auditrunid, $raw_audit_results);

            // Reset tour for users only after steps and results are successfully created
            $manager->reset_tour_for_all_users($tour->get_id());

            $tourdata = self::init_tour_data($tour);
            $resp = [
                'status' => true,
                'message' => get_string('toursuccess', 'block_course_audit'),
                'tourdata' => $tourdata,
                'actionDetailsMap' => $action_details_map
            ];
            return $resp;
        } catch (\Exception $e) {
            // If any step or result saving fails, delete the tour and the audit run record
            error_log("Error during step creation or result saving for tour ID: {$tour->get_id()}. Error: " . $e->getMessage());
            try {
                \block_course_audit\tour_manager::delete_tour($tour->get_id());
                // Also delete the main audit run record if steps failed
                $DB->delete_records('block_course_audit_tours', ['id' => $auditrunid]);
                $DB->delete_records('block_course_audit_results', ['auditid' => $auditrunid]);
            } catch (\Exception $delEx) {
                // Log deletion error but prioritize original exception
                error_log("Failed to clean up tour ID: {$tour->get_id()} after error. Deletion Error: " . $delEx->getMessage());
            }
            // Re-throw the original exception
            throw $e;
        }
    }

    private static function delete_existing_tours($courseid)
    {
        global $DB;
        $manager = new tour_manager();
        $existingtours = $DB->get_records_sql(
            "SELECT t.*, ca.id AS auditid FROM {tool_usertours_tours} t JOIN {block_course_audit_tours} ca ON t.id = ca.tourid WHERE ca.courseid = ? AND t.name LIKE ?",
            [$courseid, "Course Audit: Course #$courseid%"]
        );

        // If a tour already exists, delete it using the tour manager
        foreach ($existingtours as $existingtour) {
            try {
                $manager->delete_tour($existingtour->id);
                // Also delete the corresponding entry in block_course_audit_tours
                $DB->delete_records('block_course_audit_tours', ['tourid' => $existingtour->id]);
                $DB->delete_records('block_course_audit_results', ['auditid' => $existingtour->auditid]);
            } catch (\Exception $e) {
                error_log("Error deleting existing tour ID: {$existingtour->id}. Error: " . $e->getMessage());
            }
        }
    }

    private static function store_audit_results($auditrunid, $raw_audit_results)
    {
        global $DB;
        $now = time();
        foreach ($raw_audit_results as $result) {
            $resultrecord = new \stdClass();
            $resultrecord->auditid = $auditrunid;
            $resultrecord->rulekey = $result->rule_key;
            $resultrecord->status = $result->status;
            if (isset($result->messages) && is_array($result->messages)) {
                $resultrecord->messages = json_encode($result->messages);
            } else {
                $resultrecord->messages = null;
            }
            $resultrecord->rulecategory = $result->rule_category;
            $resultrecord->targettype = $result->rule_target ?? null;
            $resultrecord->targetid = $result->rule_target_id ?? null;
            $resultrecord->timecreated = $now;
            $DB->insert_record('block_course_audit_results', $resultrecord);
        }
    }

    private static function store_audit_tour($tour, $courseid)
    {
        global $DB;
        $auditrunrecord = new \stdClass();
        $auditrunrecord->tourid = $tour->get_id();
        $auditrunrecord->courseid = $courseid;
        $auditrunrecord->timecreated = time();
        $auditrunrecord->timemodified = time();
        $auditrunid = $DB->insert_record('block_course_audit_tours', $auditrunrecord);
        return $auditrunid;
    }
}
