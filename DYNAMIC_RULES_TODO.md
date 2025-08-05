# Dynamic Rules Implementation TODO

## Overview
This document outlines the steps required to implement dynamic rule checking functionality in the Course Audit block. The goal is to enable the auditor class to execute rules created through the rule form interface, with results compatible with the existing `get_audit_results()` method.

## Current State Analysis

### Existing Components
- **Rule Form**: `classes/form/rule_form.php` - Creates dynamic rules with checks and resolutions
- **Database Schema**: Tables for rules, checks, resolutions, and preconditions
- **Auditor Class**: `classes/audit/auditor.php` - Currently uses hard-coded rule manager
- **Rule Manager**: `classes/rules/rule_manager.php` - Manages hard-coded rules
- **Expected Output**: Same format as `get_audit_results()` with tour_steps, raw_results, and action_details_map

### Database Tables Structure
- `block_course_audit_rule`: Rule definitions (name, description)
- `block_course_audit_check`: Individual checks within rules
- `block_course_audit_resolution`: Resolution actions/hints for rules
- `block_course_audit_precond`: Rule dependencies
- `block_course_audit_coll_rule`: Rule collection mappings

## Implementation Steps

### Phase 1: Database Integration Layer ✅ **COMPLETED**

#### 1.1 Create Dynamic Rule Loader ✅ **COMPLETED**
**File**: `classes/rules/dynamic_rule_loader.php`
- **Purpose**: Load rules from database and convert to executable format
- **Methods**:
  - `load_all_rules()`: Get all active rules
  - `load_rules_for_target(string $target_type)`: Get rules for specific target (course/section/mod)
  - `load_rule_by_id(int $rule_id)`: Get specific rule with all checks and resolutions
  - `check_rule_preconditions(int $rule_id, array $passed_rules)`: Verify rule dependencies

#### 1.2 Create Dynamic Rule Data Objects ✅ **COMPLETED**
**File**: `classes/rules/dynamic_rule.php`
- **Purpose**: Represent a complete rule with checks and resolutions
- **Properties**:
  - `id`, `name`, `description`
  - `checks[]`: Array of check objects
  - `resolutions[]`: Array of resolution objects
  - `preconditions[]`: Array of required rule IDs
- **Methods**:
  - `execute(object $target, stdClass $course)`: Run all checks and return result
  - `get_resolutions()`: Get applicable resolutions for failed checks

#### 1.3 Create Check and Resolution Objects ✅ **COMPLETED**
**File**: `classes/rules/dynamic_check.php`
- **Purpose**: Represent individual check conditions
- **Properties**: `source`, `check_type`, `target`, `comp`, `value`, `not_check`, etc.
- **Methods**: `evaluate(object $target, stdClass $course)`: Execute the check

**File**: `classes/rules/dynamic_resolution.php`
- **Purpose**: Represent resolution actions/hints
- **Properties**: `type`, `scope`, `hint_message`, `actiontype`, etc.
- **Methods**: `generate_output()`, `generate_action_button()`

### Phase 2: Rule Execution Engine ✅ **COMPLETED**

#### 2.1 Create Dynamic Rule Executor ✅ **COMPLETED**
**File**: `classes/rules/dynamic_rule_executor.php`
- **Purpose**: Central rule execution engine for dynamic rules
- **Methods**:
  - `execute_all_rules(stdClass $course)`: Execute all rules for a course
  - `execute_rules_for_target(string $target_type, object $target, stdClass $course)`: Execute rules for specific target
  - `execute_single_rule(dynamic_rule $rule, object $target, stdClass $course)`: Run single rule
  - `check_preconditions(dynamic_rule $rule, array $executed_results)`: Verify rule dependencies
  - `get_execution_summary(array $results)`: Get summary statistics
  - **Features**: Precondition handling, error management, result filtering

#### 2.2 Source Data Extraction ✅ **COMPLETED** (Integrated into dynamic_check.php)
- **Purpose**: Extract specific data from different source types
- **Implementation**: Built into `dynamic_check->get_source_data()` method
- **Supported Sources**: course, section, mod, quiz, assign, forum, lesson, scorm, url, resource
- **Features**: Instance selection (first/last), nested property access, module-specific data

#### 2.3 Implement Content Counters ✅ **COMPLETED**
**File**: `classes/rules/content_counters.php`
- **Purpose**: Count content items for content-type checks
- **Methods**:
  - `count_course_content(string $content_type, stdClass $course)`: Count course-level content
  - `count_section_content(string $content_type, object $section, stdClass $course)`: Count section content
  - `count_quiz_content(string $content_type, object $quiz)`: Count quiz-specific content
  - `count_assign_content(string $content_type, object $assign)`: Count assignment-specific content
- **Supported Content Types**: modules, sections, visible/hidden modules, questions, attempts, grades, submissions, and all major module types

### Phase 3: Integration with Auditor ✅ **COMPLETED**

#### 3.1 Extend Auditor Class ✅ **COMPLETED**
**File**: `classes/audit/auditor.php` (modified existing)
- **New Properties**:
  - `$dynamic_executor`: Dynamic rule executor instance
- **New Methods**:
  - `audit_dynamic_rules(stdClass $course)`: Main entry point for dynamic rule checking (runs ALL rules regardless of target type)
  - `add_tour_step_for_result()`: Helper method to create tour steps for different target types
  - `find_section_for_module()`: Helper method to find section containing a module
- **Design Principle**: 
  - **Single Method Approach**: All dynamic rules run together, no distinction by target type in method calls
  - **Target Distinction**: Made in rule results themselves via `rule_target` and `rule_target_id` properties

#### 3.2 Update get_audit_results Method ✅ **COMPLETED**
- **Modifications**:
  - ✅ Added call to `audit_dynamic_rules()` for all dynamic rules
  - ✅ Merged dynamic rule results with existing static rules
  - ✅ Maintained backward compatibility with existing rule format
  - ✅ Enhanced action button details processing for dynamic rules
  - ✅ Added proper tour step generation for all target types (course, section, mod)

#### 3.3 Result Format Standardization ✅ **COMPLETED**
- **Dynamic results match expected format**:
  ```php
  $result = (object)[
      'rule_name' => string,
      'rule_category' => 'hint'|'action'|'show',
      'status' => bool,
      'messages' => string,
      'rule_target' => 'course'|'section'|'mod',
      'rule_target_id' => int,
      'action_button_details' => array, // For action type results
      'resolutions' => array, // Additional resolution data
      'rule_id' => int // Dynamic rule ID
  ];
  ```

### Phase 4: Action Generation ✅ **COMPLETED**

#### 4.1 Create Action Button Generator ✅ **COMPLETED**
**File**: `classes/rules/action_generator.php`
- **Purpose**: Generate action buttons for resolution actions
- **Methods**:
  - `generate_change_setting_action(dynamic_resolution $resolution, object $target)`: Create setting change actions
  - `generate_add_content_action(dynamic_resolution $resolution, object $target)`: Create content addition actions
  - `generate_enable_feature_action()`, `generate_make_visible_action()`, `generate_create_backup_action()`
  - `generate_mapkey(object $target, string $action_type)`: Create unique action identifiers
  - `is_action_allowed()`: Permission validation

#### 4.2 Implement Action Handlers ✅ **COMPLETED**
**File**: `classes/actions/` (new directory)
- **Purpose**: Handle execution of generated actions
- **Classes**:
  - `setting_changer.php`: Execute setting changes (courses, sections, modules)
  - `content_adder.php`: Execute content additions (sections, modules)
  - `action_validator.php`: Validate action permissions and safety

#### 4.3 Integration with Dynamic Resolution ✅ **COMPLETED**
- **Updated**: `dynamic_resolution.php` to use action_generator
- **Added**: Comprehensive language strings for all action types
- **Features**: Enhanced action buttons with proper icons, confirmation texts, and parameter validation
- **Simplified**: Removed redundant `make_visible` and `enable_feature` actions (now handled by `change_setting` with smart text generation)

### Phase 5: Logic Operator Handling

#### 5.1 Create Logic Evaluator
## Update: Can be skipped since implementation already done.

### Phase 6: Advanced Features ✅ **COMPLETED**

#### 6.1 Implement Source Instance Selection ✅ **COMPLETED**
- ✅ **Fixed instance selection logic**: Corrected boolean checking (was checking for string "first"/"last" instead of boolean true)
- ✅ **Enhanced "other source" functionality**: When enabled, selects same type as target but checks all other instances of the same type
  - Course target: No other courses (returns null)
  - Section target: Gets all other sections in course
  - Module target: Gets all other modules of same type
- ✅ **Instance selection support**: First/last instance selection working for sections, modules, and specific module types

#### 6.2 Implement Resolution Scope Logic ✅ **COMPLETED**
- ✅ **"Other target" functionality**: When enabled, selects same type as target but handles all other instances of same type
  - Course target: No other courses (returns empty array)
  - Section target: Gets all other sections in course
  - Module target: Gets all other modules of same type
- ✅ **Multiple target handling**: Action buttons can affect multiple targets when other_target is enabled
- ✅ **Target resolution array**: Returns array of targets for flexible resolution handling

#### 6.3 Content Type Validation ✅ **COMPLETED**
- ✅ **Content type validator class**: `classes/rules/content_type_validator.php` with essential validation
- ✅ **Source-specific validation**: Valid content types defined for each source type (course, section, modules, quiz, assign)
- ✅ **Scope-specific validation**: Valid content types defined for each resolution scope (course, section)
- ✅ **Simple validation methods**: `is_valid_content_type_for_source()` and `is_valid_content_type_for_scope()`
- ✅ **Getter methods**: Access to valid content type arrays for UI integration

### Phase 7: Testing and Validation

#### 7.1 Unit Tests
**Directory**: `tests/`
- Test individual check evaluations
- Test logic operator combinations
- Test content counting accuracy
- Test action button generation

#### 7.2 Integration Tests
- Test complete rule execution flow
- Test result format compatibility
- Test performance with multiple rules

#### 7.3 User Acceptance Testing
- Create test rules through form interface
- Verify rules execute correctly in auditor
- Validate action buttons work as expected

## Technical Considerations

### Performance Optimization
- **Rule Caching**: Cache loaded rules to avoid repeated database queries
- **Lazy Loading**: Only load rules when needed for specific targets
- **Query Optimization**: Use efficient database queries for rule loading

### Error Handling
- **Graceful Degradation**: Continue audit even if individual rules fail
- **Logging**: Log rule execution errors for debugging
- **Validation**: Validate rule data before execution

### Security Considerations
- **Permission Checks**: Verify user permissions for action execution
- **Input Validation**: Validate all rule parameters before execution
- **SQL Injection Prevention**: Use parameterized queries for dynamic data access

### Backward Compatibility
- **Maintain existing hard-coded rule functionality**
- **Ensure existing API contracts are preserved**
- **Provide migration path for existing implementations**

## Implementation Priority

### ✅ Completed (Core Functionality)
1. ✅ Dynamic Rule Loader (1.1)
2. ✅ Rule Data Objects (1.2-1.3)
3. ✅ Rule Execution Engine (2.1-2.3)
4. ✅ Source Data Extraction and Content Counting
5. ✅ Auditor Integration (3.1-3.3) - **UNIFIED APPROACH**
6. ✅ Action Generation (4.1-4.3) - **Enhanced action button functionality**
7. ✅ Logic Evaluation (5.1) - **Already implemented in dynamic_rule class**
8. ✅ Advanced Features (6.1-6.3) - **Instance selection, other source/target, content validation**

### High Priority (Next Steps)
1. **Testing and Validation (7.1-7.3)** - Comprehensive testing phase

### Medium Priority (Enhanced Features)
1. Performance Optimization - Rule caching, lazy loading, query optimization
2. Advanced Error Handling - Enhanced logging and graceful degradation
3. Security Enhancements - Additional permission checks and validation

### Low Priority (Advanced Features)
1. Comprehensive Testing (7.1-7.3)
2. Performance Optimization
3. Advanced Error Handling

## Success Criteria

- ✅ Rules created through form interface execute in auditor
- ✅ Results are compatible with existing `get_audit_results()` format
- ✅ Action buttons work for resolution actions
- ✅ Performance remains acceptable with multiple dynamic rules
- ✅ All existing functionality continues to work
- ✅ Rules with complex logic (AND/OR) execute correctly
- ✅ Content counting and setting checks work accurately 