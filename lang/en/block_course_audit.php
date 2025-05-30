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

// Disclaimer and documentation related - used in documentation.php
$string['documentation_link'] = 'Open Documentation documentation';
$string['documentation_title'] = 'Course audit Documentation';
$string['documentation_heading'] = 'Audit Guidelines & Best Practices';

// Rules
$string['rule_category_hint'] = 'Hint';
$string['rule_category_action'] = 'Action';

// Rules - PDF Only - used in rule implementations
$string['rule_pdf_only_name'] = 'Exclusivly PDF Resources';
$string['rule_pdf_only_description'] = 'Checks if a section contains only PDF resources';
$string['rule_pdf_only_empty_section'] = 'The section is empty. Please add some resources.';
$string['rule_pdf_only_non_pdf_resources'] = 'Section contains non-PDF resources or activities:';
$string['rule_pdf_only_non_pdf_resource_item'] = '- "{$a->name}" ({$a->type})';
$string['rule_pdf_only_success'] = 'All {$a->count} resources in the section are PDFs.';

// Standard format keys with section_ prefix
$string['rule_section_has_pdfs_name'] = 'Presence of PDF Resources in Section';
$string['rule_section_has_pdfs_description'] = 'Checks if a section contains only PDF resources';
$string['rule_section_has_pdfs_empty_section'] = 'The section is empty. Please add some resources.';
$string['rule_section_has_pdfs_non_pdf_resources'] = 'Section contains non-PDF resources or activities:';
$string['rule_section_has_pdfs_non_pdf_resource_item'] = ' - {$a->name} (Type: {$a->type})';
$string['rule_section_has_pdfs_success'] = 'Section contains {$a->count} PDF resource(s).';

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

// Rules - Has Label - used in rule implementations
$string['rule_has_label_name'] = 'Presence of Labels';
$string['rule_has_label_description'] = 'Checks if a section contains a label';
$string['rule_has_label_empty_section'] = 'The section is empty. Please add some resources.';
$string['rule_has_label_success'] = 'The section contains a label.';
$string['rule_has_label_failure'] = 'The section does not contain a label. Adding labels can improve course structure and clarity for learners. Consider adding a label to:<ul><li>Provide clear headings for content blocks.</li><li>Offer brief instructions or context for activities.</li><li>Visually break up long lists of resources or activities.</li></ul>';

// Standard format keys with section_ prefix for labels
$string['rule_section_has_label_name'] = 'Presence of Labels in Section';
$string['rule_section_has_label_description'] = 'Checks if a section contains a label';
$string['rule_section_has_label_empty_section'] = 'The section is empty. Please add some resources.';
$string['rule_section_has_label_success'] = 'The section contains a label.';
$string['rule_section_has_label_failure'] = 'The section does not contain a label. Adding labels can improve course structure and clarity for learners. Consider adding a label to:<ul><li>Provide clear headings for content blocks.</li><li>Offer brief instructions or context for activities.</li><li>Visually break up long lists of resources or activities.</li></ul>';

$string['button_add_label'] = 'Add Label';
$string['label_added_success'] = 'Label added successfully';
$string['label_added_failure'] = 'Failed to add label';
$string['label_intro'] = 'Use labels to add explanatory text, instructions, or headings directly within a course section, helping to structure content and guide learners.';
$string['label_name'] = 'New Label';

// Strings for adding Quiz via AJAX
$string['button_add_quiz'] = 'Add Quiz';
$string['quiz_name_default'] = 'New Quiz';
$string['quiz_intro_default'] = 'Quiz introduction - please configure this quiz further.';
$string['quiz_added_success'] = 'Basic quiz added successfully. Please configure its settings and add questions.';
$string['quiz_added_failure'] = 'Failed to add quiz';

// Summary related - used in summary display
$string['summary_heading'] = 'Course Audit Summary';
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

// Scheduled task string
$string['cleanup_audit_tours'] = 'Cleanup audit tours task';

$string['showdetails'] = 'Show details';
$string['close'] = 'Close';
$string['status_done'] = 'Completed';
$string['status_todo'] = 'Pending';

// Rule: section_has_quiz
$string['rule_section_has_quiz_name'] = 'Presence of Quizzes in Section';
$string['rule_section_has_quiz_description'] = 'Checks if the section contains at least one quiz activity.';
$string['rule_section_has_quiz_success'] = 'Section contains a quiz: {$a->quizname}';
$string['rule_section_has_quiz_failure'] = 'Section does not contain any quiz activities. Quizzes are valuable for assessing and reinforcing learning. Consider adding a quiz to:<ul><li>Test learners\' understanding of the section content.</li><li>Provide immediate feedback to help identify knowledge gaps.</li><li>Encourage active recall and engagement with the material.</li></ul>';
$string['rule_section_has_quiz_empty_section'] = 'Section is empty, cannot contain a quiz.';

// Rule: course_has_section
$string['rule_course_has_section_name'] = 'Presence of Course Sections';

// Settings page strings
$string['settings_heading'] = 'Course Audit Settings';
$string['example_setting_name'] = 'Example Text Setting';
$string['example_setting_desc'] = 'This is an example text setting for the Course Audit block.';
$string['settings_link_description'] = 'To configure the settings for the Course Audit block, please go to <a href="{$a}">Block settings</a>.';

// Standard format keys with section_ prefix
$string['rule_section_has_connections_name'] = 'Activity Connections in Section';
$string['rule_section_has_connections_description'] = 'Checks if activities in a section have connections through completion conditions';
$string['rule_section_has_connections_empty_section'] = 'The section is empty. Please add some activities.';
$string['rule_section_has_connections_single_module'] = 'The section has only one activity ("{$a->name}"). At least two activities are needed to create connections.';
$string['rule_section_has_connections_no_conditions'] = 'No activities in this section have completion conditions set up. Please add conditions to create a learning path.';
$string['rule_section_has_connections_success'] = '{$a->count} activities have completion conditions set up.';
$string['rule_section_has_connections_module_with_condition'] = '- "{$a->name}" has completion conditions';
$string['rule_section_has_connections_some_without_conditions'] = '{$a->count} activities do not have any completion conditions:';
$string['rule_section_has_connections_module_without_condition'] = '- "{$a->name}" has no completion conditions';

// Strings for enable_repeatable external function
$string['quiznotfound'] = 'Quiz with ID {$a->id} not found.';
$string['repeatalreadyenabled'] = 'Quiz is already set to unlimited attempts.';
$string['errorupdatequiz'] = 'Error updating quiz settings.';
$string['repeatenabledsuccess'] = 'Quiz attempts successfully set to unlimited.';
$string['rule_quiz_is_repeatable_failure'] = 'Quiz allows {$a->attempts} attempt(s). For practice or formative assessment, unlimited attempts can be beneficial. Consider enabling unlimited attempts if:<ul><li>The quiz is for self-assessment and learning, not formal grading.</li><li>You want to allow students to practice until they master the material.</li><li>The goal is to encourage repeated engagement with the quiz content.</li></ul>';

// Rule: quiz_is_repeatable
$string['rule_quiz_is_repeatable_name'] = 'Repeatability of Quizzes';
$string['rule_quiz_is_repeatable_description'] = 'Checks if a quiz is set to allow unlimited attempts.';
$string['button_enable_repeatable'] = 'Enable repeatable attempts';
$string['lastaudit'] = 'Last audit:';
$string['checksprocessed'] = 'Total checks';
$string['passedrules'] = 'Passed';
$string['failedrules'] = 'Failed';

$string['courselevel'] = 'Course Level';
$string['startnewaudit'] = 'Start New Audit';