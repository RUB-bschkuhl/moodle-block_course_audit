<?php
// This file is part of Moodle - http://moodle.org/
// Moodle is free software: you can redistribute it and/or modify ... (license header)

/**
 * Rule that checks if a section contains at least one label module.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\action;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/course_audit/classes/rules/rule_base.php');

use block_course_audit\rules\rule_base;
use core_course_list_element; // Needed for type hinting if using modinfo

/**
 * Rule that checks if a section contains at least one label module.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class has_label extends rule_base
{

    const rule_key = 'section_has_label';
    const target_type = 'section';
    const prerequisite_rules = ['section_has_mods'];
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_has_label_name', 'block_course_audit'),
            get_string('rule_has_label_description', 'block_course_audit'),
            'action', // Category
            self::prerequisite_rules
        );
    }

    /**
     * Check if the section contains a label.
     *
     * @param object $target The course_section object (or equivalent structure with modules)
     * @param object $course The course object
     * @return object Result object
     */
    public function check_target($target, $course = null)
    {
        $haslabel = false;

        // Assume $section->modules contains course_list_element objects or similar
        // based on how the auditor fetches section data.
        if (!empty($target->modules)) {
            foreach ($target->modules as $module) {
                // Check if the module type is 'label'
                if (isset($module->modname) && $module->modname === 'label') {
                    $haslabel = true;
                    break; // Found one, no need to check further
                }
            }
        }

        if ($haslabel) {
            // Section contains at least one label
            return $this->create_result(true, [
                get_string('rule_has_label_success', 'block_course_audit')
            ], $target->id, $course->id);
        } else {
            // No labels found in the section
            return $this->create_result(false, [
                get_string('rule_has_label_failure', 'block_course_audit')
            ], $target->id, $course->id);
        }
    }

    /**
     * Get details for the action button (add a label).
     *
     * @param int $target_id Target ID of the rule result if needed for the action.
     * @return array|null Action button details.
     */
    public function get_action_button_details($target_id = null, $courseid = null)
    {
        // Only show button if the check failed (no label found) and we have a section ID.
        if (!$target_id) {
            return null;
        }

        // Button details for an AJAX action to add a label
        return [
            'mapkey' => 'section_' . $target_id . '_' . self::rule_key,
            'label' => get_string('button_add_label', 'block_course_audit'),
            'endpoint' => 'block_course_audit_manage_labels',
            'params' => 'sectionid=' . $target_id . '&courseid=' . $courseid
        ];
    }
}
