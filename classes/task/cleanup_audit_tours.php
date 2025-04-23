<?php
// moodle-405/blocks/course_audit/classes/task/cleanup_audit_tours.php
namespace block_course_audit\task;

defined('MOODLE_INTERNAL') || die();

class cleanup_audit_tours extends \core\task\scheduled_task {

    /**
     *
     * @return \string
     */
    public function get_name() {
        return get_string('cleanup_audit_tours', 'block_course_audit');
    }

    /**
     * Execute the task.
     *
     * @throws \moodle_exception Can throw exceptions if the task fails.
     */
    public function execute() {
        $time = time() - 43200; 
        // Cleanup all usertours that are connected to the audit tours that are older than 12 hours
        $DB->delete_records('tool_usertours_tours', ['id' => $DB->get_records('block_course_audit_tours', ['timemodified' => $time])]);
        // Delete all audit tours that are older than 12 hours
        $DB->delete_records('block_course_audit_tours', ['timemodified' => $time]);
        $DB->delete_records('block_course_audit_results', ['timecreated' => $time]);
    }
} 