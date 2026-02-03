<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case ACCESS_DASHBOARD = 'access dashboard';

        // User management permissions
    case VIEW_USERS = 'view users';
    case CREATE_USERS = 'create users';
    case UPDATE_USERS = 'update users';
    case EDIT_USERS = 'edit users';
    case DELETE_USERS = 'delete users';
    case MANAGE_USERS = 'manage users';

        // Role & Permission management
    case MANAGE_ROLES = 'manage roles';
    case MANAGE_PERMISSIONS = 'manage permissions';

        // Academic
    case VIEW_ACADEMIC_MANAGEMENT = 'view academic management';
    case MANAGE_ACADEMIC_SETUP = 'manage academic setup';
    case MANAGE_ACADEMIC_MANAGEMENT = 'manage academic management';
    case MANAGE_EXAM_DATABASE = 'manage exam database';

        // Events & announcements
    case VIEW_EVENTS_ANNOUNCEMENTS = 'view events and announcements';
    case MANAGE_EVENT_ANNOUNCEMENT_SETUP = 'manage events and announcement setup';
    case MANAGE_EVENT_PLANNER = 'manage event planner';
    case MANAGE_ANNOUNCEMENTS = 'manage announcements';

        // Time-table & attendance
    case VIEW_TIME_TABLE_ATTENDANCE = 'view time-table and attendance';
    case MANAGE_TIME_TABLE_ATTENDANCE_SETUP = 'manage time-table and attendance setup';
    case MANAGE_TIME_TABLE_PLANNER = 'manage time-table planner';
    case MANAGE_STUDENT_ATTENDANCE = 'manage student attendance';
    case COLLECT_STUDENT_ATTENDANCE = 'collect student attendance';
    case MANAGE_TEACHER_ATTENDANCE = 'manage teacher attendance';
    case MANAGE_STAFF_ATTENDANCE = 'manage staff attendance';
    case MANAGE_LEAVE_REQUESTS = 'manage leave requests';
    case APPLY_LEAVE_REQUESTS = 'apply leave requests';

        // Departments & profiles
    case VIEW_DEPARTMENTS_PROFILES = 'view departments and profiles';
    case MANAGE_DEPARTMENTS = 'manage departments';
    case MANAGE_TEACHER_PROFILES = 'manage teacher profiles';
    case MANAGE_STUDENT_PROFILES = 'manage student profiles';
    case MANAGE_STAFF_PROFILES = 'manage staff profiles';

        // Finance
    case VIEW_FINANCE = 'view finance management';
    case MANAGE_FINANCE_SETUP = 'manage finance setup';
    case MANAGE_STUDENT_FEES = 'manage student fees';
    case MANAGE_SALARY_PAYROLL = 'manage salary and payroll';
    case MANAGE_FINANCE_TRANSACTIONS = 'manage finance transactions';

        // Settings
    case VIEW_SYSTEM_SETTINGS = 'view system settings';
    case MANAGE_SCHOOL_SETTINGS = 'manage school settings';
    case MANAGE_ACADEMIC_YEAR_TERMS = 'manage academic year and terms';
    case MANAGE_USER_ACTIVITY_LOGS = 'manage user activity logs';

        // Reports
    case VIEW_REPORTS = 'view reports';
    case GENERATE_REPORTS = 'generate reports';

        // Communication & support
    case VIEW_COMMUNICATION_SUPPORT = 'view communication and support';
    case MANAGE_CONTACTS = 'manage contacts';
    case MANAGE_SUPPORT_TICKETS = 'manage support tickets';
    case VIEW_NOTIFICATIONS = 'view notifications';

        // System
    case VIEW_SYSTEM_MANAGEMENT = 'view system management';

    /**
     * Get all permission values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get grouped permissions
     */
    public static function grouped(): array
    {
        return [
            'Core' => [
                self::ACCESS_DASHBOARD,
            ],
            'User Management' => [
                self::VIEW_USERS,
                self::CREATE_USERS,
                self::UPDATE_USERS,
                self::EDIT_USERS,
                self::DELETE_USERS,
                self::MANAGE_USERS,
            ],
            'Roles & Permissions' => [
                self::MANAGE_ROLES,
                self::MANAGE_PERMISSIONS,
            ],
            'Academic Management' => [
                self::VIEW_ACADEMIC_MANAGEMENT,
                self::MANAGE_ACADEMIC_SETUP,
                self::MANAGE_ACADEMIC_MANAGEMENT,
                self::MANAGE_EXAM_DATABASE,
            ],
            'Events & Announcements' => [
                self::VIEW_EVENTS_ANNOUNCEMENTS,
                self::MANAGE_EVENT_ANNOUNCEMENT_SETUP,
                self::MANAGE_EVENT_PLANNER,
                self::MANAGE_ANNOUNCEMENTS,
            ],
            'Time-table & Attendance' => [
                self::VIEW_TIME_TABLE_ATTENDANCE,
                self::MANAGE_TIME_TABLE_ATTENDANCE_SETUP,
                self::MANAGE_TIME_TABLE_PLANNER,
                self::MANAGE_STUDENT_ATTENDANCE,
                self::COLLECT_STUDENT_ATTENDANCE,
                self::MANAGE_TEACHER_ATTENDANCE,
                self::MANAGE_STAFF_ATTENDANCE,
                self::MANAGE_LEAVE_REQUESTS,
                self::APPLY_LEAVE_REQUESTS,
            ],
            'Departments & Profiles' => [
                self::VIEW_DEPARTMENTS_PROFILES,
                self::MANAGE_DEPARTMENTS,
                self::MANAGE_TEACHER_PROFILES,
                self::MANAGE_STUDENT_PROFILES,
                self::MANAGE_STAFF_PROFILES,
            ],
            'Finance Management' => [
                self::VIEW_FINANCE,
                self::MANAGE_FINANCE_SETUP,
                self::MANAGE_STUDENT_FEES,
                self::MANAGE_SALARY_PAYROLL,
                self::MANAGE_FINANCE_TRANSACTIONS,
            ],
            'System Settings' => [
                self::VIEW_SYSTEM_SETTINGS,
                self::MANAGE_SCHOOL_SETTINGS,
                self::MANAGE_ACADEMIC_YEAR_TERMS,
                self::MANAGE_USER_ACTIVITY_LOGS,
            ],
            'Reports' => [
                self::VIEW_REPORTS,
                self::GENERATE_REPORTS,
            ],
            'Communication & Support' => [
                self::VIEW_COMMUNICATION_SUPPORT,
                self::MANAGE_CONTACTS,
                self::MANAGE_SUPPORT_TICKETS,
                self::VIEW_NOTIFICATIONS,
            ],
            'System Management' => [
                self::VIEW_SYSTEM_MANAGEMENT,
            ],
        ];
    }
}
