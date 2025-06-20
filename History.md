# Course Audit Block - Change History

This file tracks changes made to the Course Audit Block plugin.

## Changelog

### 2024-03-18 (Version 0.1.19)
- **Feature:** Added "Show" resolution type for displaying informational messages
- **Feature:** Added dynamic content type selection for "Add Content" actions based on target scope
- **Feature:** Content options now depend on target type - courses/sections show activities, quizzes show question types, non-containers only allow hints
- **Feature:** Comprehensive content type options including sections, activities (assign, quiz, forum, wiki, folder, url, page, book) and question types (multichoice, truefalse, essay, shortanswer, numerical, matching, cloze)
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added "show" resolution type, dynamic content type fields for each target type, comprehensive conditional logic and JavaScript for content type visibility
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added "show" resolution type, "showmessage", "addcontenttype", "choose" and all content type language strings
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translations for show resolution type and all content type strings
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added hint_message, show_message, and content_type fields to resolution table, restructured to support proper message handling
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Created upgrade script for version 2024031806 to add new resolution fields
    - `/moodle-500/blocks/course_audit/version.php`: Updated version to 2024031806
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated form processing to handle show messages and dynamic content type selection, updated form loading to properly populate new fields

### 2024-03-18 (Version 0.1.18)
- **Feature:** Added "Other target" functionality to resolution scopes matching the check source logic
- **Feature:** Resolution scope fields are now automatically disabled and synchronized with the first check's source
- **Feature:** Added "Other [target](s)" checkbox for resolutions to target different instances of the same scope type
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added res_other_target checkbox, JavaScript to sync resolution scopes with first check source, CSS styling for disabled resolution fields
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added othertarget language string
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translation for other target
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added other_target field to resolution table
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script for other_target field
    - `/moodle-500/blocks/course_audit/version.php`: Updated version to 2024031805
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated to handle res_other_target field in save/load operations

### 2024-03-18 (Version 0.1.17)
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
    - `/moodle-500/blocks/course_audit/version.php`: Updated version to 2024031804
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Added handling for preconditions and group management in save/load operations

### 2024-03-18 (Version 0.1.16)
- **Feature:** Added First/Last checkboxes for source instance selection (only visible in first check)
- **Feature:** Changed from radio buttons to checkboxes to allow: first only, last only, both, or neither
- **Feature:** Added source_instance_first and source_instance_last boolean fields to database
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added source_instance checkboxes, CSS to hide for non-first checks
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added firstinstance and lastinstance language strings
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added German translations for instance selection
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added source_instance_first and source_instance_last boolean fields to check table
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script with migration from single field to two boolean fields
    - `/moodle-500/blocks/course_audit/version.php`: Updated version to 2024031803
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated to handle both source_instance boolean fields in save/load operations

### 2024-03-18 (Version 0.1.15)
- **Improvement:** Replaced JavaScript-based form grouping with native HTML container elements
- **Fix:** Resolved "too much recursion" console error by removing problematic event listeners
- **Improvement:** Cleaner form structure using Moodle core HTML elements instead of DOM manipulation
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added HTML container elements, removed styleCheckGroups function, updated CSS for containers, improved recursion prevention

### 2024-03-18 (Version 0.1.14)
- **Feature:** Added source field dependency system where first check determines source for all subsequent checks
- **Feature:** Added "Other [source]" checkbox for additional checks to check different instances of same source type
- **Files Changed:**
    - `/moodle-500/blocks/course_audit/classes/form/rule_form.php`: Added other_source checkbox, JavaScript logic for source field dependency, CSS styling for disabled fields
    - `/moodle-500/blocks/course_audit/lang/en/block_course_audit.php`: Added othersource language string
    - `/moodle-500/blocks/course_audit/lang/de/block_course_audit.php`: Added othersource German translation
    - `/moodle-500/blocks/course_audit/db/install.xml`: Added other_source field to check table
    - `/moodle-500/blocks/course_audit/db/upgrade.php`: Added upgrade script for other_source field
    - `/moodle-500/blocks/course_audit/version.php`: Updated version to 2024031802
    - `/moodle-500/blocks/course_audit/edit_rule.php`: Updated to handle other_source field in save/load operations

### 2024-03-18 (Version 0.1.13)
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
    - `/moodle-500/blocks/course_audit/version.php`: Updated version to 2024031801 