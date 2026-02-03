# School Management Platform (Smart Campus Base)

This is a comprehensive school management platform built with Laravel 12. It uses Tailwind + Alpine + dark-mode conventions and aligns UI with modern dashboard designs for academic features.

## What this repo is

-   Laravel 12 with Sanctum auth, Spatie roles/permissions, clean architecture (Repository + Service + DTO); **no public registration** (admins provision users).
-   Frontend uses Tailwind, Alpine.js, Breeze-style auth views, and built-in dark mode. PWA/service worker and push notifications exist but are **disabled for now**.
-   Modern responsive design with gradients and dashboard widgets using the Tailwind/Alpine stack.
-   Comprehensive documentation reflects the complete school-management scope.

## Current focus

1. Roles/permissions: admin, staff, teacher, student, guardian; guarded with Spatie + `@can` in Blade.
2. Guest flows: welcome + login, language/theme toggles, two-step password recovery (email/phone lookup → NRC check → OTP stored).
3. Domain build-out: academic data (schools, grades, classes, schedules, attendance), user profiles, fees/payments, events/announcements, and related controllers/resources.
4. UI integration: port dashboard/navigation patterns from `blade_prototype` into `resources/views` while keeping dark-mode support.
5. PWA/Push: keep assets off until we explicitly re-enable.
6. Developer workflow: follows clean code principles (SOLID, PSR-12, tests with Pest). See `QUICKSTART.md` and `IMPLEMENTATION_GUIDE.md`.
7. Settings + safety: single `settings` row stores school info and setup flags (academic/finance/school info). Users have an `is_active` flag; inactive users cannot log in (admin toggles).
8. IDs: all primary keys use UUIDs (users, roles/permissions, settings, tokens). Run fresh migrations/seeds after pulling changes.

## Quick start

The usual Laravel flow applies:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

Default seed users (all password `password`):

-   admin@smartcampusedu.com (admin role)
-   staff@smartcampusedu.com (staff role)
-   teacher@smartcampusedu.com (teacher role)

Use `/forgot-account` for the new recovery flow (email/phone → NRC → OTP sent later).
