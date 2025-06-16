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
        global $DB;
        //TODO check
        $time = time() - 43200; 
        //Select and delete all records from audit_tours that are older than 12 hours
        $audit_tours = $DB->get_records('block_course_audit_tours', ['timemodified' => $time]);
        $DB->delete_records('tool_usertours_steps', ['tourid' => $audit_tours]);
        $DB->delete_records('tool_usertours_tours', ['id' => $audit_tours]);
        $DB->delete_records('block_course_audit_tours', ['timemodified' => $time]);
        $DB->delete_records('block_course_audit_results', ['timecreated' => $time]);
    }
} 