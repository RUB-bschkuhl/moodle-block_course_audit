# Course Audit Rules

This directory contains the rule system for the Course Audit block. There are two types of rules:

## Static Rules vs Dynamic Rules

### Static Rules
- **Hard-coded** in PHP classes
- Located in `static/` subdirectory
- Defined by developers, cannot be changed by users
- Fast execution, no database queries needed
- Use the `rule_interface` for consistent implementation

### Dynamic Rules
- **Database-driven** rules created through the web interface
- Created by teachers/admins using the rule form
- Stored in database tables and executed at runtime
- Flexible and user-configurable
- Support complex logic with AND/OR operators

## Creating Static Rules

1. **Create a new rule class** in `static/` directory
2. **Implement `rule_interface`** with required methods:
   - `get_rule_name()`: Return rule name
   - `get_rule_category()`: Return 'action' or 'hint'
   - `check()`: Return true if rule passes, false if it fails
   - `get_message()`: Return message to display when rule fails
   - `get_action_button_details()`: Return action button details (for action rules)

3. **Register the rule** in `static/rule_manager.php`

### Example Static Rule

```php
<?php
namespace block_course_audit\rules\static;

class example_rule implements rule_interface {
    public function get_rule_name(): string {
        return 'Example Rule';
    }
    
    public function get_rule_category(): string {
        return 'hint'; // or 'action'
    }
    
    public function check(object $target, \stdClass $course): bool {
        // Your rule logic here
        return $target->visible == 1;
    }
    
    public function get_message(): string {
        return 'This item should be visible';
    }
    
    public function get_action_button_details(): array {
        return []; // For hint rules, empty array
    }
}
```

### Rule Categories
- **hint**: Provides suggestions/warnings, no automated actions
- **action**: Provides action buttons for automated fixes

### Best Practices
- Keep rules simple and focused on one check
- Use descriptive rule names and clear messages
- Test rules thoroughly before deployment
- Consider performance impact for rules that run frequently