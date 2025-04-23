<?php
// moodle-405/blocks/course_audit/classes/external/manage_labels.php

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die();

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
use \core_external\external_api_method_helper; // Include the trait for validation methods

/**
 * External API for managing labels within the course audit block.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_labels extends external_api {

    use external_api_method_helper; // Include the trait for validation methods

    /**
     * Parameters for the add_label_to_section function.
     *
     * @return external_function_parameters
     */
    public static function add_label_to_section_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course ID where the section belongs'),
            'sectionid' => new external_value(PARAM_INT, 'The section ID to add the label to')
        ]);
    }

    /**
     * Value returned by the add_label_to_section function.
     *
     * @return external_single_structure
     */
    public static function add_label_to_section_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'True if the label was added successfully, false otherwise.'),
            'message' => new external_value(PARAM_TEXT, 'Success or error message.'),
            'cmid' => new external_value(PARAM_INT, 'The course module ID of the newly added label', VALUE_OPTIONAL)
        ]);
    }

    /**
     * Adds a new label module to a specific course section.
     *
     * @param int $courseid Course ID.
     * @param int $sectionid Section ID.
     * @return array Status array.
     * @throws moodle_exception
     */
    public static function add_label_to_section(int $courseid, int $sectionid) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::add_label_to_section_parameters(), [
            'courseid' => $courseid,
            'sectionid' => $sectionid
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
        $label->course = $course->id;
        $label->name = get_string('newlabel', 'block_course_audit');
        $label->intro = get_string('newlabelintro', 'block_course_audit');
        $label->introformat = FORMAT_HTML;
        $label->timemodified = time();
        $label->section = $section->section;

        $cm = add_moduleinfo($label, $course, $labelmodule, $section->section);

        if (!$cm || !$cm->id) {
            throw new moodle_exception('erroraddlabel', 'block_course_audit'); 
        }

        return [
            'status' => true,
            'message' => get_string('labeladdedsuccess', 'block_course_audit'),
            'cmid' => $cm->id
        ];
    }
} 