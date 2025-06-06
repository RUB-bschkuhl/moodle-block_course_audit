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
 * Block edit form class for the block_course_audit plugin.
 *
 * @package   block_course_audit
 * @copyright 2025, Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class block_course_audit_edit_form extends block_edit_form
{

    protected function specific_definition($mform)
    {
        //TODO Rule Sets and Checks need to be selectable here with a multiselect form

        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('settings:managesettings', 'block_course_audit'));
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_placeholder_instance', get_string('settings:placeholder', 'block_course_audit'));
        $global_placeholder = get_config('block_course_audit_settings', 'placeholder');
        if (!$global_placeholder) {
            $mform->setDefault('config_placeholder_instance', '');
        } else {
            $mform->setDefault('config_placeholder_instance', $global_placeholder);
        }
        $mform->setType('config_placeholder_instance', PARAM_TEXT);
    }
}
