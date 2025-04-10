<?php

namespace block_course_audit;

defined('MOODLE_INTERNAL') || die();

use core\invalid_parameter_exception;
use core\dml\exception as dmlexception;

class observer
{

    /**
     * Triggered when a user tour is completed or exited.
     *
     * @param \tool_usertours\event\tour_ended $event The event data.
     * @return void
     */
    public static function tour_ended(\tool_usertours\event\tour_ended $event): void
    {
        $eventdata = $event->get_data();
        $tourid = $eventdata['objectid'] ?? null; // The tour ID
        $userid = $eventdata['userid'] ?? null; // The user ID

        mtrace("Tour ended event triggered for tour ID: {$tourid}, User ID: {$userid}");

        if (empty($tourid)) {
            mtrace("Tour ended event received without a valid tour ID. Cannot delete.");
            return; // Cannot proceed without a tour ID
        }

        try {
            mtrace("Observer attempting to delete tour ID: {$tourid}");
            $deleted = tour_manager::delete_tour($tourid); // Call the delete function
            if ($deleted) {
                mtrace("Observer successfully deleted tour ID: {$tourid}");
            } else {
                // This might happen if the tour was already deleted or the delete function returned false for other reasons.
                mtrace("Observer: delete_tour function returned false for tour ID: {$tourid}. It might have already been deleted.");
            }
        } catch (invalid_parameter_exception $ipe) {
            // This shouldn't happen if we check empty($tourid), but good practice.
            mtrace("Observer: Invalid parameter exception while trying to delete tour ID: {$tourid}. Error: " . $ipe->getMessage());
        } catch (dmlexception $dml) {
            mtrace("Observer: Database exception while trying to delete tour ID: {$tourid}. Error: " . $dml->getMessage());
        } catch (\Exception $e) {
            // Catch any other unexpected exceptions
            mtrace("Observer: Unexpected error while deleting tour ID: {$tourid}. Error: " . $e->getMessage());
        }
    }
}
