# School Management Migration - To Do

> Test status (php artisan test --testsuite=Feature,Unit on 2025-12-10): total 30, passed 30, remaining 0.
> Tasks: total 51, completed 17, remaining 34.

## Tech Stack & Architecture

-   **Framework:** Laravel 12 (PHP 8.2+)
-   **Database:** PostgreSQL (Production)
-   **API Auth:** Laravel Sanctum 4.2
-   **RBAC:** Spatie Permission 6.23
-   **UI:** Tailwind CSS + Alpine.js
-   **DataTables:** Yajra DataTables 12.6
-   **Architecture:** Repository Pattern + Service Layer + DTOs
-   **Notification System:**
    -   FCM (Firebase Cloud Messaging) for Mobile Apps (Teacher App)
    -   Web Push (minishlink/web-push) for Web Browser notifications

## Security Features

-   UUID primary keys (non-sequential)
-   Role-Based Access Control (Admin, Staff, Teacher, Student, Guardian)
-   Sanctum token authentication (API)
-   Session-based authentication (Web)
-   CSRF protection
-   Password hashing (bcrypt)
-   Account activation/deactivation control
-   SSL/HTTPS in production
-   Form Request validation (40+ validators)

## Deployment Model

-   One school = One private cloud server
-   PostgreSQL with encryption at rest
-   Flexible storage (local or S3 cloud per school request)
-   Automated backups per school requirement
-   Public-facing Teacher App API

## Preflight

-   [x] Confirm target stack for UI: Tailwind + Alpine + dark mode; PWA disabled for now.
-   [x] Map core roles/permissions (admin, staff, teacher, student, guardian) and seeders.
-   [x] Validate environment (.env, npm build, queues) for new modules and notifications; ensure toast provider + Tailwind modal confirm are loaded globally.

## Planning & Mapping

-   [ ] Mirror data schemas from `smart-campus-platform` (grades/batches/classes/rooms/subjects/students/teachers/staff/departments/events/fees/attendance/finance/reports) into `scp` migrations/models; note any repairs.
-   [x] Define setup flags: `setup_completed_school_info`, `setup_completed_academic`, `setup_completed_event_and_announcements`, `setup_completed_time_table_and_attendance`, `setup_completed_finance` in settings; seed school info defaults.
-   [x] Design dashboard metrics: school info card, counts (students/staff/teachers), today attendance per role, fee collections % this month, upcoming events/exams; allow fallback to zero when empty.
-   [x] Map navigation/routes and access gating for admin modules (Academic Setup/Management, Exam Database, Departments, Profiles, Attendance, Finance, School Info, Activity Logs, Reports, User Management with deactivate/reactivate/reset, Terms/Support).

## Backend Alignment

-   [ ] Add status flag on `users` (active/inactive) and guard login; add reset-password action for admins.
-   [x] Implement setup wizard data capture (school info, batch/grade/class, rooms, subjects, events/announcements, attendance/timetable, finance) with one-time completion flag per domain and route protection. _(Academic, events/announcements, timetable/attendance, and finance setup now persist with flags complete.)_
-   [x] Finance setup baseline: tuition fee structure (frequency, late fee, grace, default discount) stored in settings; grade monthly fees stored to settings and grades; expense categories synced to `expense_categories`; finance setup flag toggles on save. _Run `php artisan migrate` to add finance columns to settings before testing._
-   [ ] Create CRUD controllers/resources for academic entities, exams (with marks), departments, profiles (student/teacher/staff tied to users), attendance (student timetable-based; staff/teacher daily record), events/announcements with queued notifications (UI hook only until enable), leave requests, reports, user activity logs, terms/support content.
-   [x] Add finance ledger: income/expense CRUD, student fee payment rollup, and P&L dashboard aggregates.
-   [ ] Salary/payroll for staff/teachers.
-   [ ] Add school info update (with logo upload, short logo) and activity logs for create/update/delete + login/logout.
-   [x] Expose dashboard data endpoints/services used by Blade; include pagination/search filters where relevant.

## Frontend Integration

-   [x] Build setup wizard UI with progress steps and contextual descriptions; enforce redirects when steps incomplete (School Info, Academic, Events/Announcements, Time-table & Attendance, Finance).
-   [x] Update dashboard layout with school info card, attendance counters, fee collection %, upcoming events/exams.
-   [ ] Implement CRUD blades with tabs/tables per module (academic management, exams, departments, profiles, attendance, finance) using shared dashboard theme, responsive, dark mode, localization (EN/MM), Alpine loading states, toast messaging, and Tailwind modal dialogs for confirms.
-   [ ] Update user management UI to support deactivate/reactivate, reset password, status badges, role assignment; ensure access gating.
-   [ ] Wire announcements/events forms to dispatch queued notifications (UI hook only if backend pending).
-   [ ] Update error/maintenance blades to match theme and localization.
-   [x] Apply global toast + confirm patterns to all pages (no browser confirms).
-   [ ] Update localization strings for EN/MM where blades change.

## Feature Delivery (per requirements)

-   [ ] Unified UI theme: responsive, dark mode, localization, loading states, toasts for system messages, Tailwind modal dialogs for confirms; apply to all blades including errors.
-   [x] Setup completion enforcement for: School Info, Academic, Events & Announcements, Time-table & Attendance, Finance (redirect/gate modules until done).
-   [x] Academic Management: Batch/Grade/Class/Room/Subject CRUD (repo/service/DTO/FormRequest) with tabs + modals.
-   [ ] User Management: User CRUD with roles, reset password, disable/enable, status badges.
-   [ ] Departments CRUD.
-   [ ] Profiles: Student/Teacher/Staff profiles with CRUD tied to users.
-   [ ] Time-table Planner.
-   [ ] Student Attendance.
-   [ ] Teacher Attendance.
-   [ ] Staff Attendance.
-   [ ] Leave Requests apply/manage.
-   [ ] Exam Database CRUD + mark entry.
-   [ ] Event CRUD.
-   [ ] Announcement CRUD (with notification hook; notification delivery later).
-   [x] Student Fee: collect grade fee monthly.
-   [ ] Salary and Payroll: teachers/staff.
-   [x] Finance: income/expense CRUD and P&L report.
-   [ ] Report Centre: student/class reports.
-   [ ] User activities log.
-   [ ] User activities log (capture admin-triggered status changes and password resets; add later).
-   [ ] Terms and conditions view.
-   [ ] Roles and permissions update UI.
-   [ ] System support content view.
-   [ ] User manual update.
-   [ ] Localization updates where blades change.

## Quality & Ops

-   [x] Add feature tests for login gating on inactive users, setup wizard completion flags, and key CRUD flows. _(Dashboard access + inactive user redirects covered.)_
-   [ ] Add tests for dashboard metrics aggregation and attendance “today” queries.
-   [x] Add tests for finance aggregates (fee collection %, P&L).
-   [ ] Update documentation (`README.md`, `IMPLEMENTATION_GUIDE.md`, `API_DOCUMENTATION.md`, `QUICKSTART.md`, user manual, terms/support) to reflect new modules, routes, and setup flags.
-   [x] Fix current test failures (php artisan test): registration/password reset routes 404 / notifications missing; profile update/delete assertions; StudentFeatureTest facade bootstrap error.
