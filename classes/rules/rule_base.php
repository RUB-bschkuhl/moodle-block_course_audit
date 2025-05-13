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
 * Base rule class for the course_audit block.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules;

defined('MOODLE_INTERNAL') || die();

/**
 * Abstract base class for rules in course_audit
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class rule_base implements rule_interface
{

    /** @var string The rule key */
    protected $key;

    /** @var string The rule target */
    protected $target;

    /** @var string The rule name */
    protected $name;

    /** @var string The rule description */
    protected $description;

    /** @var string The rule category */
    protected $category;

    /**
     * Constructor.
     *
     * @param string $key The rule key
     * @param string $target_type The rule target
     * @param string $name The rule name
     * @param string $description The rule description
     * @param string $category The rule category ('hint' or 'action')
     */
    public function __construct($key, $target_type, $name, $description, $category)
    {
        $this->key = $key;
        $this->target = $target_type;
        $this->name = $name;
        $this->description = $description;
        $this->category = $category;
    }

    /**
     * Get the rule target
     *
     * @return string
     */
    public function get_target()
    {
        return $this->target;
    }

    /**
     * Get the rule name (for display)
     *
     * @return string
     */
    public function get_key()
    {
        return $this->key;
    }

    /**
     * Get the rule name (for display)
     *
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * Get the rule description
     *
     * @return string
     */
    public function get_description()
    {
        return $this->description;
    }

    /**
     * Get the rule category
     *
     * @return string One of 'hint', 'action'
     */
    public function get_category()
    {
        return $this->category;
    }

    /**
     * Get details for rendering an action button in the frontend, if applicable.
     *
     * If this rule has an associated action button, this method should return an array
     * containing the necessary details for the frontend. Otherwise, it should return null.
     *
     * Expected structure:
     * [
     *   'label' => (string) get_string(...) for the button text,
     *   'type' => (string) 'ajax' or 'js' indicating the action type,
     *   'endpoint' => (string) The AJAX method name (if type is 'ajax'),
     *   'function_name' => (string) The JS function name (if type is 'js'),
     *   'params' => (array) Key-value pairs of parameters needed for the action
     * ]
     *
     * @param int $target_id Target ID of the rule result if needed for the action.
     * @return array|null Action button details or null if no button.
     */
    public function get_action_button_details($target_id = null, $courseid = null) {
        return null;
    }

    /**
     * Create a result object
     *
     * @param bool $status Whether the rule passed (true) or failed (false)
     * @param array $messages Messages to display, typically explaining the result
     * @return object Result object
     */
    protected function create_result($status, $messages = [], $target_id = null, $courseid = null)
    {
        return (object) [
            'status' => $status,
            'messages' => $messages,
            'rule_name' => $this->name,
            'rule_category' => $this->category,
            'rule_key' => $this->key,
            'rule_target' => $this->target,
            'rule_target_id' => $target_id,
            'action_button_details' => $this->get_action_button_details($target_id, $courseid)
        ];
    }
}
