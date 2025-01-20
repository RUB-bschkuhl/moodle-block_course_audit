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

$string['pluginname'] = 'Course audit';
$string['restriction'] = 'Access restrictions';
$string['restriction_completion'] = 'Complete "{$a->activity}"';
$string['restriction_grade_min'] = 'Grade for "{$a->grade}" at least {$a->min}%';
$string['restriction_grade_max'] = 'Grade for "{$a->grade}" up to {$a->max}%';
$string['restriction_grade_range'] = 'Grade for "{$a->grade}" between {$a->min}% and {$a->max}%';
$string['restriction_grouping'] = 'Member of "{$a}" grouping';
$string['restriction_until'] = 'Available until {$a}';
$string['restriction_unknown'] = 'Special condition';
$string['deletedactivity'] = '(deleted activity)';
$string['deletedgrade'] = '(deleted grade item)';
$string['deletedgrouping'] = '(deleted grouping)';
$string['addinstance'] = 'Add a new Course Analysis block';
$string['myaddinstance'] = 'Add a new Course Analysis block to the My Moodle page';
$string['section'] = 'Section';
$string['modules'] = 'Modules';
$string['nomodules'] = 'No activities in this section';
$string['norestrictions'] = 'No restricitions in this section';
$string['page'] = 'Page';
$string['previous'] = 'Previous';
$string['next'] = 'Next';
$string['sectionpage_help'] = 'This section provides a detailed analysis of all activities and resources. 
It identifies dependencies (e.g., completion requirements, grading rules) and suggests adaptive adjustments to improve course flow. 
Use the action buttons to: 
- Convert legacy content to modern activities 
- Update access rules for better learner progression 
- Restructure sections based on analytics';
$string['disclaimer_text'] = 'The analysis on the following pages relates to content from our project repository. 
For detailed documentation, see our <a href="{$a}" target="_blank">conversion guidelines wiki</a>.';
$string['disclaimer_title'] = 'Disclaimer';
$string['structure_title'] = 'Structural evaluation';
$string['section_title'] = 'Section evaluation';
$string['Summary_title'] = 'Summary';
$string['wiki_link'] = 'Open Documentation Wiki';
$string['wiki_title'] = 'Course audit Documentation';
$string['wiki_heading'] = 'Conversion Guidelines & Best Practices';
$string['opendetached'] = 'Open in central view';
$string['backtocourse'] = 'Back to course';
$string['flow_violation'] = 'Learning flow violation detected';
$string['testing_without_building'] = 'Testing activity "{$a}" has no prerequisite building knowledge activity';
$string['additional_without_testing'] = 'Additional resource "{$a}" not linked to any testing activity';
$string['apply_fix'] = 'Connect to "{$a}"';
$string['summary_heading'] = 'Course Conversion Summary';
$string['action_building'] = 'Building knowledge prerequisites added';
$string['action_testing'] = 'Testing activity gates configured';
$string['action_additional'] = 'Additional resources linked';
$string['restriction_added'] = 'Successfully added required relationship';
$string['error_invalid_module'] = 'Could not find specified activity';
$string['error_permission_denied'] = 'You don\'t have permission to modify this course';

// Rules - General
$string['rules_activity_type_category'] = 'Activity Type Rules';
$string['rules_activity_flow_category'] = 'Activity Flow Rules';
$string['rules_passed'] = 'Passed';
$string['rules_failed'] = 'Failed';
$string['rules_total'] = 'Total';
$string['rules_success_rate'] = 'Success Rate';

// Rules - PDF Only
$string['rule_pdf_only_name'] = 'PDF Only Resources';
$string['rule_pdf_only_description'] = 'Checks if a section contains only PDF resources';
$string['rule_pdf_only_empty_section'] = 'The section is empty. Please add some resources.';
$string['rule_pdf_only_non_pdf_resources'] = 'The section contains non-PDF resources:';
$string['rule_pdf_only_non_pdf_resource_item'] = '- "{$a->name}" ({$a->type})';
$string['rule_pdf_only_success'] = 'All {$a->count} resources in the section are PDFs.';

// Rules - Has Connections
$string['rule_has_connections_name'] = 'Activity Connections';
$string['rule_has_connections_description'] = 'Checks if activities in a section have connections through completion conditions';
$string['rule_has_connections_empty_section'] = 'The section is empty. Please add some activities.';
$string['rule_has_connections_single_module'] = 'The section has only one activity ("{$a->name}"). At least two activities are needed to create connections.';
$string['rule_has_connections_no_conditions'] = 'No activities in this section have completion conditions set up. Please add conditions to create a learning path.';
$string['rule_has_connections_success'] = '{$a->count} activities have completion conditions set up.';
$string['rule_has_connections_module_with_condition'] = '- "{$a->name}" has completion conditions';
$string['rule_has_connections_some_without_conditions'] = '{$a->count} activities do not have any completion conditions:';
$string['rule_has_connections_module_without_condition'] = '- "{$a->name}" has no completion conditions';

// New UI elements for floating analysis
$string['minimize'] = 'Minimize';
$string['maximize'] = 'Maximize';
$string['close'] = 'Close';
$string['show_analysis'] = 'Show analysis';
$string['hide_analysis'] = 'Hide analysis';
$string['inline_mode'] = 'Display analysis inline with sections';
$string['block_mode'] = 'Display analysis in block';
$string['passed'] = 'Passed';
$string['failed'] = 'Failed';
$string['toggle_analysis_view'] = 'Toggle analysis view mode';
$string['analysis_mode_help'] = 'Choose how the course analysis is displayed:
<ul>
<li>Block mode: Analysis appears in the sidebar block</li>
<li>Inline mode: Analysis appears directly beneath each course section</li>
</ul>';