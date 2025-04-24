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
 * External API for retrieving audit results.
 *
 * @package block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;
use moodle_exception;

/**
 * External API for retrieving audit results for a specified tour
 *
 * @package block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_summary extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters()
    {
        return new external_function_parameters([
            'tourid' => new external_value(PARAM_INT, 'The tour ID to get audit results for')
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
            'data' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'ID of the audit result'),
                    'auditid' => new external_value(PARAM_INT, 'ID of the audit run'),
                    'rulekey' => new external_value(PARAM_TEXT, 'Unique identifier for the audit rule'),
                    'rulecategory' => new external_value(PARAM_TEXT, 'Category of the rule'),
                    'status' => new external_value(PARAM_TEXT, 'Outcome of the rule check (pass, fail, info)'),
                    'messages' => new external_value(PARAM_RAW, 'Details about the result', VALUE_OPTIONAL),
                    'targettype' => new external_value(PARAM_TEXT, 'Type of entity the rule targets', VALUE_OPTIONAL),
                    'targetid' => new external_value(PARAM_INT, 'ID of the entity the rule targets', VALUE_OPTIONAL),
                    'timecreated' => new external_value(PARAM_INT, 'Timestamp when the result was recorded')
                ]),
                'List of audit results'
            )
        ]);
    }

    /**
     * Get audit results for a specified tour
     *
     * @param int $tourid The tour ID to retrieve audit results for
     * @return array Operation status, response message, and audit results data
     */
    public static function execute($tourid)
    {
        global $DB, $OUTPUT;

        // Validate parameters using parent method
        $params = parent::validate_parameters(self::execute_parameters(), ['tourid' => $tourid]);
        $tourid = $params['tourid'];

        try {
            $audittour = $DB->get_record('block_course_audit_tours', ['tourid' => $tourid], '*', MUST_EXIST);

            $coursecontext = context_course::instance($audittour->courseid);

            // Validate context using parent method
            parent::validate_context($coursecontext);

            require_capability('block/course_audit:view', $coursecontext);

            $sql = "SELECT ar.* 
                    FROM {block_course_audit_results} ar
                    WHERE ar.auditid = ?
                    ORDER BY ar.rulecategory, ar.rulekey";

            $auditresults = $DB->get_records_sql($sql, [$audittour->id]);

            if (empty($auditresults)) {
                return [
                    'status' => true,
                    'message' => get_string('noauditresultsfound', 'block_course_audit', $tourid),
                    'data' => []
                ];
            }

            $results = array_map(function ($result) {
                $messages = !empty($result->messages) ? $result->messages : null;

                return [
                    'id' => $result->id,
                    'auditid' => $result->auditid,
                    'rulekey' => $result->rulekey,
                    'rulecategory' => $result->rulecategory,
                    'status' => $result->status,
                    'messages' => $messages,
                    'targettype' => $result->targettype,
                    'targetid' => $result->targetid,
                    'timecreated' => $result->timecreated
                ];
            }, $auditresults);

            return [
                'status' => true,
                'message' => get_string('auditresultsfetched', 'block_course_audit'),
                'data' => $results
            ];
        } catch (moodle_exception $e) {
            error_log("Error retrieving audit results for tour ID $tourid: " . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
}
