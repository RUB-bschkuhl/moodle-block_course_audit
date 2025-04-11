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
        $tourid = $eventdata['objectid'] ?? null;
        $userid = $eventdata['userid'] ?? null;

        if (empty($tourid)) {
            return;
        }

        try {
            $deleted = tour_manager::delete_tour($tourid);
        } catch (invalid_parameter_exception $ipe) {
            mtrace("Observer: Invalid parameter exception while trying to delete tour ID: {$tourid}. Error: " . $ipe->getMessage());
        } catch (dmlexception $dml) {
            mtrace("Observer: Database exception while trying to delete tour ID: {$tourid}. Error: " . $dml->getMessage());
        } catch (\Exception $e) {
            mtrace("Observer: Unexpected error while deleting tour ID: {$tourid}. Error: " . $e->getMessage());
        }
    }
}
