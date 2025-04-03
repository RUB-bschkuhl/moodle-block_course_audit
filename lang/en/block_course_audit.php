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
$string['structure_title'] = 'Structural evaluation';
$string['summary_title'] = 'Summary';
$string['disclaimer_title'] = 'Disclaimer';
$string['disclaimer_button'] = 'Start audit';

// Navigation and UI elements - used in templates/main.mustache
$string['page'] = 'Page';
$string['previous'] = 'Previous';
$string['next'] = 'Next';

// Restrictions - used in course structure display
$string['restriction'] = 'Access restrictions';
$string['restriction_completion'] = 'Complete "{$a->activity}"';
$string['restriction_grade_min'] = 'Grade for "{$a->grade}" at least {$a->min}%';
$string['restriction_grade_max'] = 'Grade for "{$a->grade}" up to {$a->max}%';
$string['restriction_grade_range'] = 'Grade for "{$a->grade}" between {$a->min}% and {$a->max}%';
$string['restriction_grouping'] = 'Member of "{$a}" grouping';
$string['restriction_until'] = 'Available until {$a}';
$string['restriction_unknown'] = 'Special condition';

// Deleted content references - used in availability condition display
$string['deletedactivity'] = '(deleted activity)';
$string['deletedgrade'] = '(deleted grade item)';
$string['deletedgrouping'] = '(deleted grouping)';

// Module related strings - used in course content display
$string['modules'] = 'Modules';
$string['nomodules'] = 'No activities in this section';
$string['norestrictions'] = 'No restricitions in this section';

// Help text - used in tooltips/popovers
$string['sectionpage_help'] = 'This section provides a detailed analysis of all activities and resources. 
It identifies dependencies (e.g., completion requirements, grading rules) and suggests adaptive adjustments to improve course flow. 
Use the action buttons to: 
- Convert legacy content to modern activities 
- Update access rules for better learner progression 
- Restructure sections based on analytics';

// Disclaimer and wiki related - used in wiki.php
$string['disclaimer_text'] = 'The analysis on the following pages relates to content from our project repository. 
For detailed documentation, see our <a href="{$a}" target="_blank">conversion guidelines wiki</a>.';
$string['wiki_link'] = 'Open Documentation Wiki';
$string['wiki_title'] = 'Course audit Documentation';
$string['wiki_heading'] = 'Conversion Guidelines & Best Practices';

// Navigation related - used in UI
$string['opendetached'] = 'Open in central view';
$string['backtocourse'] = 'Back to course';

// Rules - General - used in rule display and templates
$string['rules_activity_type_category'] = 'Activity Type Rules';
$string['rules_activity_flow_category'] = 'Activity Flow Rules';
$string['rules_passed'] = 'Passed';
$string['rules_failed'] = 'Failed';
$string['rules_total'] = 'Total';
$string['rules_success_rate'] = 'Success Rate';
$string['passed'] = 'Passed';
$string['failed'] = 'Failed';

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

// Flow violations - used in flow analysis
$string['flow_violation'] = 'Learning flow violation detected';
$string['testing_without_building'] = 'Testing activity "{$a}" has no prerequisite building knowledge activity';
$string['additional_without_testing'] = 'Additional resource "{$a}" not linked to any testing activity';
$string['apply_fix'] = 'Connect to "{$a}"';

// Summary related - used in summary display
$string['summary_heading'] = 'Course Conversion Summary';
$string['summary_button'] = 'End audit';
$string['action_building'] = 'Building knowledge prerequisites added';
$string['action_testing'] = 'Testing activity gates configured';
$string['action_additional'] = 'Additional resources linked';
$string['restriction_added'] = 'Successfully added required relationship';

// Error messages - used in error handling
$string['error_invalid_module'] = 'Could not find specified activity';
$string['error_permission_denied'] = 'You don\'t have permission to modify this course';
$string['analysisfailed'] = 'Analysis operation failed: {$a}';

// New UI elements for floating analysis - used in JS and templates
$string['minimize'] = 'Minimize';
$string['maximize'] = 'Maximize';
$string['close'] = 'Close';
$string['show_analysis'] = 'Show analysis';
$string['hide_analysis'] = 'Hide analysis';
$string['inline_mode'] = 'Display analysis inline with sections';
$string['block_mode'] = 'Display analysis in block';
$string['toggle_analysis_view'] = 'Toggle analysis view mode';
$string['analysis_mode_help'] = 'Choose how the course analysis is displayed:
<ul>
<li>Block mode: Analysis appears in the sidebar block</li>
<li>Inline mode: Analysis appears directly beneath each course section</li>
</ul>';

// Tour creation strings
$string['creatingtour'] = 'Creating tour...';
$string['toursuccess'] = 'Tour created!';
$string['startaudit'] = 'Start audit tour';
$string['startaudit_help'] = 'Start an interactive tour of the course audit features';
$string['tourstart_button'] = 'Start Tour';
$string['tourfinished'] = 'Tour finished';

// Capability strings
$string['course_audit:addinstance'] = 'Add a new course audit block';
$string['course_audit:myaddinstance'] = 'Add a new course audit block to the My Moodle page';
$string['course_audit:view'] = 'View course audit information';