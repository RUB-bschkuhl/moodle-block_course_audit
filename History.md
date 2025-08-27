# Course Audit Block - Change History

This file tracks changes made to the Course Audit Block plugin.

## Changelog

### 2025-01-02 (Version 0.1.28)
- **Improvement:** Enhanced form population logic to handle field dependencies correctly when loading existing rules
- **Feature:** Added sequential field population with proper timing to ensure dependent fields are updated before setting their values
- **Fix:** Resolved issue where form fields were not properly populated due to JavaScript dependency chain not being respected
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/amd/src/rule_form.js`: Replaced synchronous form population with sequential dependency-aware population, added new functions for handling check and resolution dependencies with proper timing

### 2025-01-02 (Version 0.1.27)
- **Fix:** Changed existing rule item clicks to refresh the page instead of using AJAX to ensure all data is updated on page load
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/amd/src/rule_form.js`: Replaced AJAX rule loading with page refresh to ensure fresh data is loaded

### 2025-01-02 (Version 0.1.26)
- **Improvement:** Enhanced dynamic rule evaluation to include evaluated item instances in check results
- **Fix:** Resolved redundant check evaluations by storing check results in rule object
- **Fix:** Fixed type mismatch in get_other_source_instances method parameter
- **Improvement:** Added methods to retrieve evaluated check results and instances from rule objects
- **Improvement:** Added validation of required fields for dynamic checks
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/rules/dynamic/dynamic_check.php`: Updated evaluate method to return evaluated instances, fixed create_result method signature, corrected get_other_source_instances method to accept string parameter
    - `/moodle-500/blocks/course_audit/classes/rules/dynamic/dynamic_rule.php`: Added evaluated_check_results property, eliminated redundant check evaluations, added methods to access evaluated instances, improved check validation

### 2024-12-30 (Version 0.1.25)
- **Improvement:** Fixed spacing and styling issues in rules tables for better visual hierarchy
- **Improvement:** Reduced padding and margins for more compact, professional appearance
- **Improvement:** Changed border colors from black to grey for softer, more consistent styling
- **Improvement:** Added background color to collection headers for better visual separation
- **Improvement:** Optimized responsive design for mobile devices
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/styles.css`: Adjusted collection header padding and margins, reduced table header and cell sizes, changed border colors to grey, improved spacing between groups, enhanced mobile responsive styles

### 2024-12-30 (Version 0.1.24)
- **Improvement:** Set existing rules section to be collapsed by default for cleaner initial interface
- **Improvement:** User preferences are still preserved - if user has previously expanded/collapsed the section, that state is remembered
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/amd/src/rule_form.js`: Modified collapsible initialization to default to collapsed state when no user preference is saved

### 2024-12-30 (Version 0.1.23)
- **Feature:** Added scrollbar functionality for rules table when it exceeds 400px height
- **Feature:** Made existing rules section collapsible with smooth animations and state persistence
- **Improvement:** Enhanced UI to handle large numbers of rules while maintaining clean interface
- **Improvement:** Added visual indicators (expand/collapse arrow) and hover effects for better UX
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Added collapsible HTML structure with header controls and content wrapper, implemented scrollable container for rules tables
    - `/moodle-500/blocks/course_audit/amd/src/rule_form.js`: Added collapsible functionality with localStorage for state persistence, click handling to prevent conflicts with New Rule button
    - `/moodle-500/blocks/course_audit/styles.css`: Added scrollbar styling, collapsible animations, hover effects, responsive adjustments for mobile devices, improved table styling within scrollable container

### 2024-12-30 (Version 0.1.22)
- **Feature:** Grouped existing rules by collection for better organization
- **Improvement:** Converted rules display from grid layout to compact table format for better scalability
- **Improvement:** Minimized displayed information to rule name and last modified date for cleaner interface
- **Improvement:** Enhanced UI to handle dozens of rules without overwhelming the interface
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Modified SQL query to group rules by collection, restructured HTML output to use table format instead of grid, reduced displayed information to essential data only
    - `/moodle-500/blocks/course_audit/styles.css`: Updated CSS from grid-based layout to table-based layout, added collection grouping styles, improved responsive design for table format

### 2024-12-30 (Version 0.1.21)
- **Improvement:** Changed save button text to "Update" when editing existing rules for better UX clarity
- **Improvement:** Enhanced button text update reliability with error handling and proper timing
- **Fix:** Fixed language string loading in JavaScript to properly display translated button text instead of placeholder strings
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Modified add_action_buttons() call to dynamically set button text based on whether editing existing rule or creating new one
    - `/moodle-500/blocks/course_audit/amd/src/rule_form.js`: Added functionality to update submit button text when loading existing rules via AJAX and when clicking "New Rule" button, with improved error handling and timing. Updated to use modern Moodle string loading (core/str) instead of deprecated M.util.get_string() method

### 2024-12-30 (Version 0.1.20)
- **Feature:** Added existing rules list display before the edit form
- **Feature:** Click-to-load functionality for existing rules - users can click any rule to populate the form with its data
- **Feature:** "New Rule" button to clear the form and start creating a fresh rule
- **Feature:** Visual feedback and loading states for rule selection
- **Feature:** Responsive grid layout for rules display with metadata (collection, creator, modification date)
- **Feature:** AJAX-powered rule loading without page refresh
- **Feature:** URL updates to reflect the currently loaded rule
- **Feature:** Automatic field visibility updates when loading rules (triggers change events to show/hide conditional fields)
- **Fix:** Added hidden form fields for courseid and id to preserve URL parameters when adding/removing form fields via "Add Check" or "Add Resolution" buttons
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Added existing rules list display section before form, SQL query to fetch rule data with user and collection information, consolidated AJAX rule loading endpoint, New Rule button in header, courseid parameter in form customdata
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added hidden fields for courseid and id preservation during form submissions
    - `/moodle-500/blocks/course_audit/amd/src/rule_form.js`: Added rule loading functionality, form population logic, visual feedback, URL history management, change event triggering for conditional field visibility, New Rule button functionality with form clearing and default state reset, hidden field updates for parameter preservation, and JSDoc parameter documentation
    - `/moodle-500/blocks/course_audit/styles.css`: Added comprehensive CSS for rules list layout, hover effects, loading animations, responsive design, and New Rule button styling
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added language strings for existing rules functionality and New Rule button
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translations for existing rules functionality and New Rule button

###  (Version 0.1.19)
- **Feature:** Added "Show" resolution type for displaying informational messages
- **Feature:** Added dynamic content type selection for "Add Content" actions based on target scope
- **Feature:** Content options now depend on target type - courses/sections show activities, quizzes show question types, non-containers only allow hints
- **Feature:** Comprehensive content type options including sections, activities (assign, quiz, forum, wiki, folder, url, page, book) and question types (multichoice, truefalse, essay, shortanswer, numerical, matching, cloze)
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added "show" resolution type, dynamic content type fields for each target type, comprehensive conditional logic and JavaScript for content type visibility
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added "show" resolution type, "showmessage", "addcontenttype", "choose" and all content type language strings
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translations for show resolution type and all content type strings
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added hint_message, show_message, and content_type fields to resolution table, restructured to support proper message handling
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Created upgrade script to add new resolution fields
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated form processing to handle show messages and dynamic content type selection, updated form loading to properly populate new fields

###  (Version 0.1.18)
- **Feature:** Added "Other target" functionality to resolution scopes matching the check source logic
- **Feature:** Resolution scope fields are now automatically disabled and synchronized with the first check's source
- **Feature:** Added "Other [target](s)" checkbox for resolutions to target different instances of the same scope type
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added res_other_target checkbox, JavaScript to sync resolution scopes with first check source, CSS styling for disabled resolution fields
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added othertarget language string
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translation for other target
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added other_target field to resolution table
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script for other_target field
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated to handle res_other_target field in save/load operations

###  (Version 0.1.17)
- **Feature:** Added rule preconditions multiselect field allowing rules to depend on other rules
- **Feature:** Added group management - rules can be added to existing groups or create new groups
- **Feature:** Added preconditions database table to store rule dependencies
- **Feature:** Smart form logic that excludes current rule from its own preconditions list
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added preconditions multiselect and group management fields with conditional logic
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added language strings for preconditions and group management
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translations for new fields
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added block_course_audit_precond table for rule dependencies
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script for preconditions table
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Added handling for preconditions and group management in save/load operations

###  (Version 0.1.16)
- **Feature:** Added First/Last checkboxes for source instance selection (only visible in first check)
- **Feature:** Changed from radio buttons to checkboxes to allow: first only, last only, both, or neither
- **Feature:** Added source_instance_first and source_instance_last boolean fields to database
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added source_instance checkboxes, CSS to hide for non-first checks
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added firstinstance and lastinstance language strings
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translations for instance selection
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added source_instance_first and source_instance_last boolean fields to check table
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script with migration from single field to two boolean fields
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated to handle both source_instance boolean fields in save/load operations

###  (Version 0.1.15)
- **Improvement:** Replaced JavaScript-based form grouping with native HTML container elements
- **Fix:** Resolved "too much recursion" console error by removing problematic event listeners
- **Improvement:** Cleaner form structure using Moodle core HTML elements instead of DOM manipulation
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added HTML container elements, removed styleCheckGroups function, updated CSS for containers, improved recursion prevention

###  (Version 0.1.14)
- **Feature:** Added source field dependency system where first check determines source for all subsequent checks
- **Feature:** Added "Other [source]" checkbox for additional checks to check different instances of same source type
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added other_source checkbox, JavaScript logic for source field dependency, CSS styling for disabled fields
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added othersource language string
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added othersource German translation
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added other_source field to check table
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script for other_source field
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated to handle other_source field in save/load operations

###  (Version 0.1.13)
- **Feature:** Added content count comparison functionality with mathematical symbols
- **Improvement:** Enhanced UI with inline layout for content comparison fields
- **Improvement:** Added dynamic field hiding/showing based on user selections
- **Feature:** Added German translations for all new features
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added content_comp and content_count fields with mathematical symbols
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Updated labels and added new strings
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translations
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added content_comp and content_count fields
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script for new fields