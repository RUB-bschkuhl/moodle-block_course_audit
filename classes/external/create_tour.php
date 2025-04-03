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
 * External API for creating tours.
 *
 * @package    block_course_audit
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;
use block_course_audit\tour\manager as tour_manager;
use moodle_exception;
use tool_usertours\tour;

/**
 * External API for creating tours
 *
 * @package    block_course_audit
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_tour extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course ID to create the tour for')
        ]);
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of the operation'),
            'tourid' => new external_value(PARAM_INT, 'ID of the created tour'),
            'message' => new external_value(PARAM_TEXT, 'Response message')
        ]);
    }

    /**
     * Create a course audit tour
     *
     * @param int $courseid The course ID
     * @return array Operation status and response message
     */
    public static function execute($courseid) {
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
        
        try {
            // Create the tour manager
            $manager = new tour_manager();
            
            // Check if a tour already exists for this course
            $existingtours = $DB->get_records_sql(
                "SELECT * FROM {tool_usertours_tours} WHERE pathmatch = ? AND name LIKE ?",
                ["/course/view.php\\?id=$courseid", "Course Audit Tour: $course->shortname%"]
            );
            
            // If a tour already exists, delete it
            foreach ($existingtours as $existingtour) {
                $manager->delete_tour($existingtour->id);
            }
            
            // Create a new tour
            $pathmatch = "/course/view.php?id=$courseid";
            $tourconfig = [
                'displaystepnumbers' => true,
                'showtourwhen' => tour::SHOW_TOUR_ON_EACH_PAGE_VISIT,
                'backdrop' => true,
                'reflex' => false
            ];
            
            $tour = $manager->create_tour(
                "Course Audit Tour: $course->shortname",
                "This tour will guide you through the course audit features for $course->fullname",
                $pathmatch,
                $tourconfig
            );
            
            // Create steps for the tour
            $manager->add_step(
                'Welcome to Course Audit',
                'This tour will show you how to use the Course Audit block to analyze your course content.',
                'selector',
                '#block-region-side-pre .block_course_audit, #block-region-side-post .block_course_audit, #side-pre .block_course_audit, #side-post .block_course_audit',
                ['placement' => 'right', 'backdrop' => true]
            );
            
            $manager->add_step(
                'Section Analysis',
                'The Course Audit tool analyzes your course sections and provides recommendations for improvement.',
                'selector',
                '.block_course_audit .content',
                ['placement' => 'bottom']
            );
            
            $manager->add_step(
                'Audit Features',
                'Click on different sections to see detailed analysis and recommendations.',
                'selector',
                '.block_course_audit .section-list',
                ['placement' => 'top']
            );
            
            $manager->add_step(
                'Activity Flow',
                'The Activity Flow visualization helps you understand how students progress through your course.',
                'selector',
                '.block_course_audit .flow-visualization',
                ['placement' => 'left']
            );
            
            $manager->add_step(
                'Start Your Audit',
                'Click here to begin a new audit of your course.',
                'selector',
                '#audit-start',
                ['placement' => 'bottom', 'backdrop' => true]
            );
            
            // Reset the tour to ensure it shows for the current user
            $manager->reset_tour_for_all_users($tour->get_id());
            
            return [
                'status' => true,
                'tourid' => $tour->get_id(),
                'message' => 'Tour created successfully'
            ];
            
        } catch (moodle_exception $e) {
            return [
                'status' => false,
                'tourid' => 0,
                'message' => $e->getMessage()
            ];
        }
    }
} 