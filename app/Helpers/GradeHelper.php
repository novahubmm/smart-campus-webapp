<?php

namespace App\Helpers;

class GradeHelper
{
    /**
     * Get localized grade name based on grade level
     *
     * @param int|string $level Grade level (0-12)
     * @return string Localized grade name
     */
    public static function getLocalizedName($level): string
    {
        $key = 'grades.grade_' . $level;
        $translated = __($key);
        
        // If translation not found, return default format
        if ($translated === $key) {
            return 'Grade ' . $level;
        }
        
        return $translated;
    }

    /**
     * Format class display name with localized grade and section
     * 
     * @param int|string $gradeLevel Grade level
     * @param string|null $section Section letter (A, B, C, etc.)
     * @return string Formatted class name
     */
    public static function formatClassName($gradeLevel, ?string $section = null): string
    {
        return SectionHelper::formatClassName($gradeLevel, $section);
    }

    /**
     * Format a full class name (e.g., "Grade 1 A") to localized version
     *
     * @param string $className Full class name
     * @param int|string|null $gradeLevel Grade level (optional)
     * @return string Formatted class name
     */
    public static function formatFullClassName(string $className, $gradeLevel = null): string
    {
        return SectionHelper::formatFullClassName($className, $gradeLevel);
    }
}
