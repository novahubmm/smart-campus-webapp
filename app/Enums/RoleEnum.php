<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case TEACHER = 'teacher';
    case STUDENT = 'student';
    case GUARDIAN = 'guardian';

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role label
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staff',
            self::TEACHER => 'Teacher',
            self::STUDENT => 'Student',
            self::GUARDIAN => 'Guardian',
        };
    }
}
