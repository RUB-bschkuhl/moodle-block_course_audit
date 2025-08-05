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
 * Content type validator for dynamic rules.
 *
 * @package    block_course_audit
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules;

/**
 * Class for validating content types based on source and target combinations.
 */
class content_type_validator {

    /**
     * Valid content types for each source type.
     *
     * @var array
     */
    private static $source_content_types = [
        'course' => [
            'modules', 'sections', 'visible_modules', 'hidden_modules',
            'quiz', 'assign', 'forum', 'lesson', 'scorm', 'url', 'resource', 'label', 'page'
        ],
        'section' => [
            'modules', 'visible_modules', 'hidden_modules',
            'quiz', 'assign', 'forum', 'lesson', 'scorm', 'url', 'resource', 'label', 'page'
        ],
        'mod' => [
            'modules', 'visible_modules', 'hidden_modules'
        ],
        'quiz' => [
            'questions', 'attempts', 'grades'
        ],
        'assign' => [
            'submissions', 'grades', 'attempts'
        ]
    ];

    /**
     * Valid content types for resolution scopes.
     *
     * @var array
     */
    private static $scope_content_types = [
        'course' => [
            'section', 'quiz', 'assign', 'forum', 'lesson', 'scorm', 'url', 'resource', 'label', 'page'
        ],
        'section' => [
            'quiz', 'assign', 'forum', 'lesson', 'scorm', 'url', 'resource', 'label', 'page'
        ],
        'mod' => []  // Cannot add content to a module
    ];

    /**
     * Get valid content types for a given source type.
     *
     * @param string $source_type Source type (course, section, mod, quiz, etc.)
     * @return array Array of valid content types
     */
    public static function get_valid_content_types_for_source(string $source_type): array {
        return self::$source_content_types[$source_type] ?? [];
    }

    /**
     * Get valid content types for a given resolution scope.
     *
     * @param string $scope Resolution scope (course, section, mod)
     * @return array Array of valid content types for adding
     */
    public static function get_valid_content_types_for_scope(string $scope): array {
        return self::$scope_content_types[$scope] ?? [];
    }

    /**
     * Validate if a content type is valid for a given source.
     *
     * @param string $content_type Content type to validate
     * @param string $source_type Source type
     * @return bool True if valid, false otherwise
     */
    public static function is_valid_content_type_for_source(string $content_type, string $source_type): bool {
        $valid_types = self::get_valid_content_types_for_source($source_type);
        return in_array($content_type, $valid_types);
    }

    /**
     * Validate if a content type is valid for a given resolution scope.
     *
     * @param string $content_type Content type to validate
     * @param string $scope Resolution scope
     * @return bool True if valid, false otherwise
     */
    public static function is_valid_content_type_for_scope(string $content_type, string $scope): bool {
        $valid_types = self::get_valid_content_types_for_scope($scope);
        return in_array($content_type, $valid_types);
    }
} 