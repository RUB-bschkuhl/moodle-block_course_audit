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
 * Hook class for tour completion events.
 *
 * @package block_course_audit
 * @copyright 2024 Your Name <your.email@example.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\hooks;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook class for tour completion events.
 *
 * @package block_course_audit
 * @copyright 2024 Your Name <your.email@example.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tour_completed
{
    /**
     * Callback function that is executed when a tour is completed.
     *
     * @param array $hookdata The data passed to the hook
     * @return array The modified hook data
     */
    public static function callback(array $hookdata): array
    {
        global $DB, $USER;

        // Log the tour completion
        debugging('Tour completed: ' . json_encode($hookdata), DEBUG_DEVELOPER);

        // Get the tour ID from the hook data
        $tourid = $hookdata['tourid'] ?? null;
        if (!$tourid) {
            return $hookdata;
        }

        // Check if this is one of our course audit tours
        $tourrecord = $DB->get_record('block_course_audit_tours', ['tourid' => $tourid]);
        if ($tourrecord) {
            // Update the timemodified field
            $tourrecord->timemodified = time();
            $DB->update_record('block_course_audit_tours', $tourrecord);

            // You can add additional logic here, such as:
            // - Logging completion in a separate table
            // - Triggering other actions
            // - Sending notifications
        }

        return $hookdata;
    }
}
