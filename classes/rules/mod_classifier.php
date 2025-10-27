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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Module classifier for categorizing Moodle modules into knowledge building and knowledge assessment.
 *
 * @package   block_course_audit
 * @copyright 2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\rules;

defined('MOODLE_INTERNAL') || die();

/**
 * Module classifier class.
 */
class mod_classifier
{
    /** @var string Constant for knowledge building modules. */
    public const MOD_WISSENSAUFBAU = 'wissensaufbau';

    /** @var string Constant for knowledge assessment modules. */
    public const MOD_WISSENSUEBERPRUEFUNG = 'wissensueberpruefung';

    /** @var array Array of module types categorized as knowledge building. */
    private static $wissensaufbau_modules = [
        'book',
        'page',
        'resource',
        'url',
        'folder',
        'file',
        'label',
        'glossary',
        'wiki',
        'forum',
        'chat',
        'choice',
        'feedback',
        'survey',
        'lesson',
        'scorm',
        'hvp',
        'h5pactivity',
        'lti',
        'externalcontent',
    ];

    /** @var array Array of module types categorized as knowledge assessment. */
    private static $wissensueberpruefung_modules = [
        'quiz',
        'assign',
        'workshop',
        'hotpot',
        'data',
        'database',
        'journal',
        'checklist',
        'attendance',
        'turnitintooltwo',
        'turnitintool',
    ];

    /**
     * Classify a module type into knowledge building or knowledge assessment.
     *
     * @param string $modtype The module type (e.g., 'quiz', 'page', 'resource')
     * @return string|null The category (MOD_WISSENSAUFBAU, MOD_WISSENSUEBERPRUEFUNG) or null if unknown
     */
    public static function classify_module(string $modtype): ?string
    {
        $modtype = strtolower($modtype);

        if (in_array($modtype, self::$wissensaufbau_modules)) {
            return self::MOD_WISSENSAUFBAU;
        }

        if (in_array($modtype, self::$wissensueberpruefung_modules)) {
            return self::MOD_WISSENSUEBERPRUEFUNG;
        }

        return null;
    }

    /**
     * Get all modules of a specific category.
     *
     * @param string $category The category (MOD_WISSENSAUFBAU or MOD_WISSENSUEBERPRUEFUNG)
     * @return array Array of module types
     */
    public static function get_modules_by_category(string $category): array
    {
        switch ($category) {
            case self::MOD_WISSENSAUFBAU:
                return self::$wissensaufbau_modules;
            case self::MOD_WISSENSUEBERPRUEFUNG:
                return self::$wissensueberpruefung_modules;
            default:
                return [];
        }
    }

    /**
     * Check if a module type belongs to a specific category.
     *
     * @param string $modtype The module type
     * @param string $category The category to check against
     * @return bool True if the module belongs to the category
     */
    public static function is_module_in_category(string $modtype, string $category): bool
    {
        return self::classify_module($modtype) === $category;
    }

    /**
     * Get all available module types.
     *
     * @return array Array of all module types
     */
    public static function get_all_modules(): array
    {
        return array_merge(self::$wissensaufbau_modules, self::$wissensueberpruefung_modules);
    }

    /**
     * Get modules by multiple categories.
     *
     * @param array $categories Array of categories
     * @return array Array of module types from all specified categories
     */
    public static function get_modules_by_categories(array $categories): array
    {
        $modules = [];
        foreach ($categories as $category) {
            $modules = array_merge($modules, self::get_modules_by_category($category));
        }
        return array_unique($modules);
    }
}
