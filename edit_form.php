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
 * Edit form for Course audit block instance configuration.
 *
 * @package    block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/edit_form.php');

use moodle_url;
use stdClass;

/**
 * Form class that lets teachers configure which loops run per block instance.
 */
class block_course_audit_edit_form extends block_edit_form
{
    // /** @var loop_manager Loop manager dependency. */
    // private loop_manager $loopmanager;

    /**
     * Constructor.
     *
     * @param moodle_url|null $action Form action URL.
     * @param mixed $customdata Custom data passed to the form.
     */
    public function __construct($action = null, $customdata = null)
    {
        // $this->loopmanager = new loop_manager();
        parent::__construct($action, $customdata);
    }

    /**
     * Adds custom configuration fields to the form.
     *
     * @param MoodleQuickForm $mform The form being constructed.
     * @return void
     */
    protected function specific_definition($mform)
    {
        parent::specific_definition($mform);

        $mform->addElement('header', 'courseauditsettings', get_string('courseauditsettings', 'block_course_audit'));

        // $options = $this->loopmanager->get_loop_options();

        // if (empty($options)) {
            $mform->addElement('static', 'config_loopids_empty', get_string('loopsheading', 'block_course_audit'),
                get_string('noloopsdefined', 'block_course_audit'));
            return;
        // }

        // $select = $mform->addElement('select', 'config_loopids', get_string('loopsheading', 'block_course_audit'), $options);
        // $select->setMultiple(true);
        // $mform->addHelpButton('config_loopids', 'loopsheading', 'block_course_audit');
    }

    /**
     * Provide default values for the form.
     *
     * @param stdClass $defaults Default configuration values.
     * @return stdClass
     */
    protected function prepare_defaults(stdClass $defaults): stdClass
    {
        $defaults = parent::prepare_defaults($defaults);

        if (!isset($defaults->config_loopids)) {
            $defaults->config_loopids = [];
        }

        if (is_string($defaults->config_loopids)) {
            $decoded = json_decode($defaults->config_loopids, true);
            if (is_array($decoded)) {
                $defaults->config_loopids = $decoded;
            }
        }

        return $defaults;
    }

    /**
     * Retrieve submitted data ensuring arrays are encoded.
     *
     * @return stdClass|null
     */
    public function get_data()
    {
        $data = parent::get_data();
        if (!$data) {
            return null;
        }

        if (isset($data->config_loopids) && is_array($data->config_loopids)) {
            $data->config_loopids = array_map('intval', $data->config_loopids);
            $data->config_loopids = json_encode(array_values(array_unique($data->config_loopids)));
        } else if (isset($data->config_loopids) && is_string($data->config_loopids)) {
            $decoded = json_decode($data->config_loopids, true);
            if (is_array($decoded)) {
                $decoded = array_map('intval', $decoded);
                $data->config_loopids = json_encode(array_values(array_unique($decoded)));
            }
        }

        return $data;
    }
}


