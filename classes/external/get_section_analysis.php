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
 * External function to get section analysis data
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use context_course;

/**
 * External function to get section analysis data
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_section_analysis extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters()
    {
        return new external_function_parameters([
            'sectionid' => new external_value(PARAM_INT, 'ID of the section to analyze'),
        ]);
    }

    /**
     * Get section analysis data
     *
     * @param int $sectionid ID of the section to analyze
     * @return array Section analysis data
     */
    public static function execute($sectionid)
    {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'sectionid' => $sectionid,
        ]);

        // Get section info
        $section = $DB->get_record('course_sections', ['id' => $params['sectionid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $section->course], '*', MUST_EXIST);

        // Check permissions
        $context = context_course::instance($course->id);
        self::validate_context($context);
        require_capability('block/course_audit:view', $context);

        $auditblock = block_instance('course_audit');
        $analysisdata = $auditblock->analyse_section($params['sectionid']);

        // Log the analysis data for debugging
        error_log('Section analysis data: ' . print_r($analysisdata, true));

        return $analysisdata;
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns()
    {
        return new external_single_structure([
            'section_id' => new external_value(PARAM_INT, 'ID of the section'),
            'section_name' => new external_value(PARAM_TEXT, 'Name of the section'),
            'section_number' => new external_value(PARAM_INT, 'Number of the section'),
            'course_id' => new external_value(PARAM_INT, 'ID of the course'),
            'course_shortname' => new external_value(PARAM_TEXT, 'Short name of the course'),
            'flow_rules' => new external_single_structure([
                'status' => new external_value(PARAM_TEXT, 'Overall status of flow rules (passed/warning/failed)'),
                'score' => new external_value(PARAM_FLOAT, 'Score between 0 and 100', VALUE_OPTIONAL),
                'rules' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_TEXT, 'Rule identifier'),
                        'name' => new external_value(PARAM_TEXT, 'Human-readable name of the rule'),
                        'description' => new external_value(PARAM_TEXT, 'Description of the rule'),
                        'status' => new external_value(PARAM_TEXT, 'Status of the rule (passed/warning/failed)'),
                        'feedback' => new external_value(PARAM_TEXT, 'Feedback message for this rule', VALUE_OPTIONAL),
                        'data' => new external_value(PARAM_RAW, 'Additional data for the rule as JSON', VALUE_OPTIONAL),
                    ])
                )
            ]),
            'type_rules' => new external_single_structure([
                'status' => new external_value(PARAM_TEXT, 'Overall status of type rules (passed/warning/failed)'),
                'score' => new external_value(PARAM_FLOAT, 'Score between 0 and 100', VALUE_OPTIONAL),
                'rules' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_TEXT, 'Rule identifier'),
                        'name' => new external_value(PARAM_TEXT, 'Human-readable name of the rule'),
                        'description' => new external_value(PARAM_TEXT, 'Description of the rule'),
                        'status' => new external_value(PARAM_TEXT, 'Status of the rule (passed/warning/failed)'),
                        'feedback' => new external_value(PARAM_TEXT, 'Feedback message for this rule', VALUE_OPTIONAL),
                        'data' => new external_value(PARAM_RAW, 'Additional data for the rule as JSON', VALUE_OPTIONAL),
                    ])
                )
            ]),
            'summary' => new external_single_structure([
                'total_score' => new external_value(PARAM_FLOAT, 'Overall score between 0 and 100'),
                'total_activities' => new external_value(PARAM_INT, 'Total number of activities in the section'),
                'activity_types' => new external_multiple_structure(
                    new external_single_structure([
                        'module' => new external_value(PARAM_TEXT, 'Module name'),
                        'count' => new external_value(PARAM_INT, 'Number of this type of activity'),
                        'icon' => new external_value(PARAM_URL, 'URL to the activity icon', VALUE_OPTIONAL),
                    ])
                ),
                'recommendations' => new external_multiple_structure(
                    new external_single_structure([
                        'type' => new external_value(PARAM_TEXT, 'Type of recommendation (info/warning/error)'),
                        'message' => new external_value(PARAM_TEXT, 'Recommendation message'),
                        'actionable' => new external_value(PARAM_BOOL, 'Whether this recommendation can be acted upon automatically', VALUE_OPTIONAL),
                        'action_id' => new external_value(PARAM_TEXT, 'ID of the action that can be performed', VALUE_OPTIONAL),
                    ])
                )
            ])
        ]);
    }
}
