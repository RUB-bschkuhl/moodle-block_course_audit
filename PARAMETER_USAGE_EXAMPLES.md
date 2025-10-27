# Parameter Usage Examples

This document demonstrates how to use the new parameter functionality in course audit rules.

## Overview

Rules can now accept configuration parameters to customize their behavior. This allows for flexible rule configurations without creating separate rule classes.

## Basic Usage

### 1. Simple Module Count Check

```php
// Check if section has at least 3 modules
$parameters = [
    'min_count' => 3,
    'allow_empty' => false
];

$rule = new section_has_mods($parameters);
```

### 2. Required Specific Modules

```php
// Require quiz and page modules
$parameters = [
    'required_modules' => ['quiz', 'page'],
    'allow_more' => true,
    'min_count' => 2
];

$rule = new section_has_mods($parameters);
```

### 3. Using Module Categories

```php
// Require both knowledge building and assessment
$parameters = [
    'required_modules' => [
        mod_classifier::MOD_WISSENSAUFBAU,
        mod_classifier::MOD_WISSENSUEBERPRUEFUNG
    ],
    'allow_more' => true
];

$rule = new section_has_mods($parameters);
```

### 4. Strict Module Requirements

```php
// Only allow exactly quiz and page, no more, no less
$parameters = [
    'required_modules' => ['quiz', 'page'],
    'allow_more' => false,
    'min_count' => 2,
    'max_count' => 2
];

$rule = new section_has_mods($parameters);
```

## Advanced Examples

### 5. Balanced Content Check

```php
// Ensure section has both learning and assessment content
$parameters = [
    'required_modules' => [
        mod_classifier::MOD_WISSENSAUFBAU,
        mod_classifier::MOD_WISSENSUEBERPRUEFUNG
    ],
    'min_count' => 2,
    'allow_more' => true
];

$rule = new section_has_balanced_content($parameters);
```

### 6. Flexible Module Requirements

```php
// Allow any combination of specified modules
$parameters = [
    'required_modules' => ['quiz', 'assign', 'page', 'resource'],
    'min_count' => 1, // At least one of the specified modules
    'max_count' => 5,  // But no more than 5 total
    'allow_more' => false // No other module types allowed
];

$rule = new section_has_mods($parameters);
```

## Using with Rule Manager

### 7. Dynamic Rule Configuration

```php
$rule_manager = new rule_manager();

// Get rules with custom parameters
$rules = $rule_manager->get_rules('hint', 'section', [
    'required_modules' => ['quiz'],
    'min_count' => 1
]);

// Run rules on target
$results = $rule_manager->run_rules($section, $course, 'hint', 'section');
```

### 8. Runtime Parameter Modification

```php
$rule = new section_has_mods();

// Modify parameters after instantiation
$rule->set_parameter('min_count', 3);
$rule->set_parameter('required_modules', ['quiz', 'page']);

// Or set multiple parameters at once
$rule->set_parameters([
    'min_count' => 2,
    'max_count' => 4,
    'allow_empty' => false
]);
```

## Parameter Reference

### Available Parameters for `section_has_mods`

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `required_modules` | array | `[]` | Array of required module types or categories |
| `allow_empty` | bool | `false` | Whether empty sections are allowed |
| `allow_more` | bool | `true` | Whether additional modules beyond required are allowed |
| `min_count` | int | `1` | Minimum number of modules required |
| `max_count` | int | `null` | Maximum number of modules allowed (null = unlimited) |

### Module Categories

Use these constants from `mod_classifier`:

- `mod_classifier::MOD_WISSENSAUFBAU` - Knowledge building modules
- `mod_classifier::MOD_WISSENSUEBERPRUEFUNG` - Knowledge assessment modules

### Module Types

Common module types include:
- `quiz` - Quiz activity
- `assign` - Assignment
- `page` - Page resource
- `resource` - File resource
- `book` - Book activity
- `forum` - Forum activity
- `glossary` - Glossary activity
- `wiki` - Wiki activity
- `lesson` - Lesson activity
- `scorm` - SCORM package
- `h5pactivity` - H5P activity
- `lti` - External tool (LTI)

## Best Practices

1. **Use meaningful parameter names** that clearly describe their purpose
2. **Provide sensible defaults** for all parameters
3. **Validate parameter values** in the constructor
4. **Document parameter usage** in class docblocks
5. **Use module categories** when possible for flexibility
6. **Test with various parameter combinations** to ensure robustness

## Migration Guide

### Updating Existing Rules

1. **Add parameters array** to constructor signature
2. **Merge with defaults** using `array_merge()`
3. **Pass parameters to parent constructor**
4. **Use `get_parameter()`** method to access values
5. **Update rule logic** to use parameter values

### Example Migration

```php
// Before
public function __construct()
{
    parent::__construct(
        self::rule_key,
        self::target_type,
        get_string('rule_name', 'block_course_audit'),
        get_string('rule_description', 'block_course_audit'),
        'hint'
    );
}

// After
public function __construct($parameters = [])
{
    $default_params = [
        'min_count' => 1,
        'allow_empty' => false
    ];
    
    $parameters = array_merge($default_params, $parameters);
    
    parent::__construct(
        self::rule_key,
        self::target_type,
        get_string('rule_name', 'block_course_audit'),
        get_string('rule_description', 'block_course_audit'),
        'hint',
        $parameters
    );
}
```
