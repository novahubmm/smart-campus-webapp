<?php

namespace App\Helpers;

class SectionHelper
{
    /**
     * Myanmar consonants mapping for sections A-Z
     * က, ခ, ဂ, ဃ, င, စ, ဆ, ဇ, ဈ, ည, ဋ, ဌ, ဍ, ဎ, ဏ, တ, ထ, ဒ, ဓ, န, ပ, ဖ, ဗ, ဘ, မ, ယ
     */
    private static array $myanmarConsonants = [
        'A' => 'က', 'B' => 'ခ', 'C' => 'ဂ', 'D' => 'ဃ', 'E' => 'င',
        'F' => 'စ', 'G' => 'ဆ', 'H' => 'ဇ', 'I' => 'ဈ', 'J' => 'ည',
        'K' => 'ဋ', 'L' => 'ဌ', 'M' => 'ဍ', 'N' => 'ဎ', 'O' => 'ဏ',
        'P' => 'တ', 'Q' => 'ထ', 'R' => 'ဒ', 'S' => 'ဓ', 'T' => 'န',
        'U' => 'ပ', 'V' => 'ဖ', 'W' => 'ဗ', 'X' => 'ဘ', 'Y' => 'မ',
        'Z' => 'ယ',
    ];

    /**
     * Get localized section letter
     *
     * @param string $section Section letter (A, B, C, etc.)
     * @return string Localized section
     */
    public static function getLocalizedSection(string $section): string
    {
        $locale = app()->getLocale();
        $upperSection = strtoupper(trim($section));
        
        if ($locale === 'mm' && isset(self::$myanmarConsonants[$upperSection])) {
            return self::$myanmarConsonants[$upperSection];
        }
        
        return $upperSection;
    }

    /**
     * Extract section from class name
     * e.g., "Grade 1 A" -> "A", "Kindergarten B" -> "B"
     *
     * @param string $className
     * @return string|null
     */
    public static function extractSection(string $className): ?string
    {
        // Match patterns like "Grade X A", "Kindergarten A", etc.
        if (preg_match('/\s([A-Za-z])$/i', $className, $matches)) {
            return strtoupper($matches[1]);
        }
        return null;
    }

    /**
     * Format class name with localized grade and section
     * e.g., "Grade 1 A" -> "ပထမတန်း (က)" in Myanmar
     *
     * @param int|string $gradeLevel
     * @param string|null $section
     * @return string
     */
    public static function formatClassName($gradeLevel, ?string $section = null): string
    {
        $gradeName = GradeHelper::getLocalizedName($gradeLevel);
        
        if ($section) {
            $localizedSection = self::getLocalizedSection($section);
            $locale = app()->getLocale();
            
            if ($locale === 'mm') {
                return "{$gradeName} ({$localizedSection})";
            }
            return "{$gradeName} {$localizedSection}";
        }
        
        return $gradeName;
    }

    /**
     * Parse and format a full class name
     * e.g., "Grade 1 A" -> "ပထမတန်း (က)" in Myanmar, "Kindergarten A" -> "Kindergarten (က)"
     *
     * @param string $className Full class name
     * @param int|string|null $gradeLevel Grade level (optional, will be extracted if not provided)
     * @return string
     */
    public static function formatFullClassName(string $className, $gradeLevel = null): string
    {
        // First, try to extract section from the class name
        $section = self::extractSection($className);
        
        // If no section found and className is just a single letter, use it as the section
        if ($section === null && preg_match('/^[A-Za-z]$/', trim($className))) {
            $section = strtoupper(trim($className));
        }
        
        // If grade level not provided, try to extract from class name
        if ($gradeLevel === null) {
            if (preg_match('/Grade\s*(\d+)/i', $className, $matches)) {
                $gradeLevel = $matches[1];
            } elseif (preg_match('/Kindergarten/i', $className)) {
                $gradeLevel = 0; // Kindergarten is grade level 0
            } else {
                // Can't determine grade, return original
                return $className;
            }
        }
        
        return self::formatClassName($gradeLevel, $section);
    }
}
