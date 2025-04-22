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
 * English language pack for Course audit
 *
 * @package    block_course_audit
 * @category   string
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
 
// Core block strings - used
$string['pluginname'] = 'Course audit';
$string['addinstance'] = 'Add a new Course Analysis block';
$string['myaddinstance'] = 'Add a new Course Analysis block to the My Moodle page';

// Section related strings - used in templates/code
$string['section'] = 'Section';
$string['section_title'] = 'Section evaluation';
$string['summary_title'] = 'Summary';
$string['disclaimer_title'] = 'Course Audit Information & Guidelines';
$string['disclaimer_button'] = 'Start audit';
$string['start_hint'] = 'Start the course audit';

// Navigation and UI elements - used in templates/main.mustache
$string['page'] = 'Page';
$string['previous'] = 'Previous';
$string['next'] = 'Next';

// Module related strings - used in course content display
$string['modules'] = 'Modules';
$string['nomodules'] = 'No activities in this section';
$string['norestrictions'] = 'No restricitions in this section';

// Disclaimer and wiki related - used in wiki.php
$string['wiki_link'] = 'Open Documentation Wiki';
$string['wiki_title'] = 'Course audit Documentation';
$string['wiki_heading'] = 'Conversion Guidelines & Best Practices';

// Rules
$string['rule_category_hint'] = 'Hint';
$string['rule_category_action'] = 'Action';

// Rules - PDF Only - used in rule implementations
$string['rule_pdf_only_name'] = 'PDF Only Resources';
$string['rule_pdf_only_description'] = 'Checks if a section contains only PDF resources';
$string['rule_pdf_only_empty_section'] = 'The section is empty. Please add some resources.';
$string['rule_pdf_only_non_pdf_resources'] = 'The section contains non-PDF resources:';
$string['rule_pdf_only_non_pdf_resource_item'] = '- "{$a->name}" ({$a->type})';
$string['rule_pdf_only_success'] = 'All {$a->count} resources in the section are PDFs.';

// Rules - Has Connections - used in rule implementations
$string['rule_has_connections_name'] = 'Activity Connections';
$string['rule_has_connections_description'] = 'Checks if activities in a section have connections through completion conditions';
$string['rule_has_connections_empty_section'] = 'The section is empty. Please add some activities.';
$string['rule_has_connections_single_module'] = 'The section has only one activity ("{$a->name}"). At least two activities are needed to create connections.';
$string['rule_has_connections_no_conditions'] = 'No activities in this section have completion conditions set up. Please add conditions to create a learning path.';
$string['rule_has_connections_success'] = '{$a->count} activities have completion conditions set up.';
$string['rule_has_connections_module_with_condition'] = '- "{$a->name}" has completion conditions';
$string['rule_has_connections_some_without_conditions'] = '{$a->count} activities do not have any completion conditions:';
$string['rule_has_connections_module_without_condition'] = '- "{$a->name}" has no completion conditions';


// Summary related - used in summary display
$string['summary_heading'] = 'Course Conversion Summary';
$string['summary_button'] = 'End audit';

// Error messages - used in error handling
$string['error_invalid_module'] = 'Could not find specified activity';
$string['error_permission_denied'] = 'You don\'t have permission to modify this course';
$string['analysisfailed'] = 'Analysis operation failed: {$a}';

// Tour creation strings
$string['creatingtour'] = 'Creating tour...';
$string['toursuccess'] = 'Tour created!';
$string['startaudit'] = 'Start audit tour';
$string['startaudit_help'] = 'Start an interactive tour of the course audit features';
$string['tourstart_button'] = 'Start Tour';
$string['tourfinished'] = 'Tour finished';
$string['tour_introduction'] = 'Welcome to the Course Audit Tour! This guided experience will help you improve your course by:
<ul>
<li>Analyzing each section for content variety and student engagement</li>
<li>Identifying missing connections between activities that might disrupt learning flow</li>
<li>Suggesting improvements to enhance the learning experience</li>
<li>Providing actionable feedback on activity types, learning paths, and resource organization</li>
</ul>
Use the navigation buttons to move through each section. The tour will highlight areas that need attention with specific recommendations. At the end of the tour, you\'ll receive a comprehensive summary with a checklist of all audit results to help you track your progress in optimizing your course structure.';

// API and results strings
$string['noauditresults'] = 'No audit results found for this course.';
$string['noauditresultsfound'] = 'No audit results found for tour ID {$a}.';
$string['auditresultsfetched'] = 'Audit results fetched successfully.';
$string['loadingsummary'] = 'Loading audit summary...';
$string['summaryerror'] = 'Error loading summary';

// Capability strings
$string['course_audit:addinstance'] = 'Add a new course audit block';
$string['course_audit:myaddinstance'] = 'Add a new course audit block to the My Moodle page';
$string['course_audit:view'] = 'View course audit information';