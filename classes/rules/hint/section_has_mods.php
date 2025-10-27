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
 * Hint rule that checks if a section contains specific activities.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules\hint;

defined('MOODLE_INTERNAL') || die();

use block_course_audit\rules\rule_base;
use block_course_audit\rules\mod_classifier;

/**
 * Section activity presence check with configurable parameters.
 */
class section_has_mods extends rule_base
{
    /** @var string Rule identifier. */
    public const rule_key = 'section_has_mods';

    /** @var string Target type. */
    public const target_type = 'section';

    /**
     * Constructor.
     *
     * @param array $parameters Optional parameters for rule configuration:
     *   - 'required_modules': array of module types that must be present
     *   - 'allow_empty': bool whether empty sections are allowed (default: false)
     *   - 'allow_more': bool whether additional modules beyond required are allowed (default: true)
     *   - 'min_count': int minimum number of modules required (default: 1)
     *   - 'max_count': int maximum number of modules allowed (default: null = unlimited)
     */
    public function __construct($parameters = [])
    {
        // Set default parameters
        $default_params = [
            'required_modules' => [], // Empty means any modules are acceptable
            'allow_empty' => false,
            'allow_more' => true,
            'min_count' => 1,
            'max_count' => null,
        ];

        $parameters = array_merge($default_params, $parameters);

        parent::__construct(
            self::rule_key,
            self::target_type,
            get_string('rule_section_has_mods_name', 'block_course_audit'),
            get_string('rule_section_has_mods_description', 'block_course_audit'),
            'hint',
            $parameters
        );
    }

    /**
     * Evaluate whether the section meets the configured module requirements.
     *
     * @param object $target Section data.
     * @param object|null $course Parent course.
     * @return object|null
     */
    public function check_target($target, $course = null)
    {
        if (empty($target) || empty($course)) {
            return null;
        }

        $required_modules = $this->get_parameter('required_modules', []);
        $allow_empty = $this->get_parameter('allow_empty', false);
        $allow_more = $this->get_parameter('allow_more', true);
        $min_count = $this->get_parameter('min_count', 1);
        $max_count = $this->get_parameter('max_count', null);

        // Get visible modules in section
        $visible_modules = [];
        if (!empty($target->modules)) {
            foreach ($target->modules as $module) {
                if (!empty($module->uservisible) && empty($module->deletioninprogress)) {
                    $visible_modules[] = $module;
                }
            }
        }

        $module_count = count($visible_modules);

        // Check if section is empty
        if ($module_count === 0) {
            if ($allow_empty) {
                return $this->create_result(
                    true,
                    [get_string('rule_section_has_mods_empty_allowed', 'block_course_audit')],
                    $target->id,
                    $course->id
                );
            } else {
                return $this->create_result(
                    false,
                    [get_string('rule_section_has_mods_empty', 'block_course_audit')],
                    $target->id,
                    $course->id
                );
            }
        }

        // Check minimum count
        if ($module_count < $min_count) {
            return $this->create_result(
                false,
                [get_string('rule_section_has_mods_min_count', 'block_course_audit', $min_count)],
                $target->id,
                $course->id
            );
        }

        // Check maximum count
        if ($max_count !== null && $module_count > $max_count) {
            return $this->create_result(
                false,
                [get_string('rule_section_has_mods_max_count', 'block_course_audit', $max_count)],
                $target->id,
                $course->id
            );
        }

        // Check required modules if specified
        if (!empty($required_modules)) {
            $found_modules = [];
            $missing_modules = [];

            foreach ($required_modules as $required_mod) {
                $found = false;
                foreach ($visible_modules as $module) {
                    if ($this->module_matches_requirement($module, $required_mod)) {
                        $found_modules[] = $required_mod;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $missing_modules[] = $required_mod;
                }
            }

            // Check if all required modules are present
            if (!empty($missing_modules)) {
                return $this->create_result(
                    false,
                    [get_string('rule_section_has_mods_missing', 'block_course_audit', implode(', ', $missing_modules))],
                    $target->id,
                    $course->id
                );
            }

            // Check if additional modules are allowed
            if (!$allow_more) {
                $extra_modules = [];
                foreach ($visible_modules as $module) {
                    $is_required = false;
                    foreach ($required_modules as $required_mod) {
                        if ($this->module_matches_requirement($module, $required_mod)) {
                            $is_required = true;
                            break;
                        }
                    }
                    if (!$is_required) {
                        $extra_modules[] = $module->modname;
                    }
                }

                if (!empty($extra_modules)) {
                    return $this->create_result(
                        false,
                        [get_string('rule_section_has_mods_extra', 'block_course_audit', implode(', ', $extra_modules))],
                        $target->id,
                        $course->id
                    );
                }
            }
        }

        // All checks passed
        return $this->create_result(
            true,
            [get_string('rule_section_has_mods_success', 'block_course_audit')],
            $target->id,
            $course->id
        );
    }

    /**
     * Check if a module matches a requirement.
     *
     * @param object $module The module object
     * @param string $requirement The requirement (module type or category constant)
     * @return bool True if module matches requirement
     */
    private function module_matches_requirement($module, $requirement)
    {
        // Direct module type match
        if ($module->modname === $requirement) {
            return true;
        }

        // Category constant match
        if ($requirement === mod_classifier::MOD_WISSENSAUFBAU) {
            return mod_classifier::is_module_in_category($module->modname, mod_classifier::MOD_WISSENSAUFBAU);
        }

        if ($requirement === mod_classifier::MOD_WISSENSUEBERPRUEFUNG) {
            return mod_classifier::is_module_in_category($module->modname, mod_classifier::MOD_WISSENSUEBERPRUEFUNG);
        }

        return false;
    }
}


