<?php

namespace block_course_audit;

use core\invalid_parameter_exception;
use core\dml\exception as dmlexception;
use core\dml\record_not_found_exception;
use core\context;
use core\require_capability_exception;

defined('MOODLE_INTERNAL') || die();

class tour_manager
{

    /**
     * Deletes a user tour and its associated steps.
     *
     * Requires the user to have the 'moodle/course:manageactivities' capability
     * in the context where the tour was defined.
     *
     * @param int $tourid The ID of the tour to delete.
     * @return bool True on success, false if the tour didn't exist initially.
     * @throws invalid_parameter_exception If tourid is not valid.
     * @throws record_not_found_exception If the tour or its context cannot be found.
     * @throws require_capability_exception If the user lacks required permissions.
     * @throws dmlexception If a database error occurs during deletion.
     */
    public static function delete_tour(int $tourid): bool
    {
        global $DB, $USER;

        if (empty($tourid)) {
            mtrace("delete_tour called with empty tour ID.");
            throw new invalid_parameter_exception('Invalid tour ID provided.');
        }

        mtrace("Attempting to delete tour ID: {$tourid} by User ID: {$USER->id}");
        $tour = $DB->get_record('tool_usertours_tours', ['id' => $tourid], '*', MUST_EXIST);
        $context = context::instance_by_id($tour->contextid, MUST_EXIST);
        require_capability('moodle/course:manageactivities', $context);
        mtrace("Capability 'moodle/course:manageactivities' checked successfully for User ID: {$USER->id} in Context ID: {$context->id}");
        $transaction = $DB->start_delegated_transaction();

        try {
            $stepsdeleted = $DB->delete_records('tool_usertours_steps', ['tourid' => $tourid]);
            mtrace("Deleted {$stepsdeleted} steps for tour ID: {$tourid}");

            $tourdeleted = $DB->delete_records('tool_usertours_tours', ['id' => $tourid]);

            if ($tourdeleted) { // Checks if 1 or more records were deleted (should be exactly 1)
                $transaction->commit();
                mtrace("Transaction committed for deleting tour ID: {$tourid}");
                \tool_usertours_reset_tour_cache($tourid);
                return true;
            } else {
                $transaction->rollback();
                mtrace("Transaction rolled back. Tour ID: {$tourid} deletion returned false despite existing.");
                return false; // Indicate deletion did not happen as expected.
            }
        } catch (\Exception $e) {
            $transaction->rollback();
            mtrace("Transaction rolled back due to exception while deleting tour ID: {$tourid}. Error: " . $e->getMessage());
            throw $e;
        }
    }
}
