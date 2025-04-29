# History

## 2025-06-20

- Fixed `check_target` method signature incompatibility in `classes/rules/hint/section_has_pdfs.php` by making `$course` parameter optional.
- Implemented `check_target` method in `classes/rules/todo/section_has_quiz.php` to check for quiz modules.
- Added corresponding language strings for `section_has_quiz` rule in `lang/en/block_course_audit.php`.
- Added `check_prerequisites` method to `classes/rules/rule_base.php` to handle rule dependencies.
- Corrected type hint for `$prerequisite_rules` in `classes/rules/rule_base.php`.
- Added `get_action_button_details` to `classes/rules/todo/section_has_quiz.php` (though AJAX action is complex).
- Defined external function `block_course_audit_manage_quiz` in `db/services.php`.
- Implemented external function logic in `block_course_audit\external\manage_quiz`  to add a basic quiz.
- Added language strings for the quiz creation action in `lang/en/block_course_audit.php`. 