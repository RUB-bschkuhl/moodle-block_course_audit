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
 * Block definition class for the block_course_audit plugin.
 *
 * @package   block_course_audit
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

// SVG-Inhalt aus Datei lesen
$svgpath = __DIR__ . '/assets/01_Logo_lang_blau_Untertitel.svg';
$svgcontent = '';

if (file_exists($svgpath)) {
    $svgcontent = file_get_contents($svgpath);
} 

// An Template übergeben
$data = ['svgcontent' => $svgcontent];

$PAGE->set_url(new moodle_url('/blocks/course_audit/documentation.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('documentation_title', 'block_course_audit'));
$PAGE->set_heading(get_string('documentation_heading', 'block_course_audit'));

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_course_audit/documentation/documentation', $data);
echo $OUTPUT->footer();