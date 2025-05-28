<?php

namespace block_course_audit;

defined('MOODLE_INTERNAL') || die();

use core\invalid_parameter_exception;
use core\dml\exception as dmlexception;
use block_course_audit\tour\manager as tour_manager;

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
        //TODO debug. Is this event called when tour is "cancelled"?
        $eventdata = $event->get_data();
        $tourid = $eventdata['objectid'] ?? null;
        $userid = $eventdata['userid'] ?? null;

        if (empty($tourid)) {
            return;
        }

        $manager = new tour_manager();
        //TODO throws error after deletion, when tour is supposed to be marked as complete
        if (!$manager->get_tour_by_id($tourid)) {
            return;
        }
        $deleted = $manager->delete_tour($tourid);
    }
}
