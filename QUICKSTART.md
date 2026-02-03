# 🚀 School Management Platform - Quick Start Guide

Welcome! This guide gets the school management platform (built on the Smart Campus) running in **15 minutes** with our Tailwind + Alpine + dark-mode stack aligned to `blade_prototype` designs.

---

## ✅ Prerequisites Checklist

Before starting, ensure you have:

-   [ ] PHP 8.2 or higher (`php -v`)
-   [ ] Composer (`composer --version`)
-   [ ] Node.js & NPM (`node -v` and `npm -v`)
-   [ ] MySQL running (`mysql --version`)
-   [ ] Git (`git --version`)

> PWA/service worker and push notifications are **disabled for now**. Keep them off during setup.

## 🧭 Project direction (school management)

-   Stack: Tailwind + Alpine + Breeze auth with built-in dark mode; follow `blade_prototype` layouts/visuals.
-   Authorization: Spatie roles/permissions with `@can` gates in `resources/views/dashboard.blade.php`.
-   Domains to build: academic data, users (admin/staff/teacher/student/guardian), schedules, attendance, fees/payments, events, announcements.
-   PWA/Push: assets exist but remain disabled until we re-enable later.

---

## 🎯 Quick Setup (15 Minutes)

### Step 1: Clone & Install (5 min)

```bash
# Clone the repository
cd ~/Desktop
git clone https://github.com/novahubmm/nova-starter.git
cd nova-starter

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### Step 2: Environment Setup (3 min)

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Edit `.env` file** with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nova_starter
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 3: Preflight validation (1 min)

```bash
php artisan preflight:check
```

-   Use `--quick` to skip the database probe if MySQL is not running yet.
-   Fix any `[FAIL]` items before continuing (app key, storage link, DB connection, queue driver).

### Step 4: Database Setup (2 min)

```bash
# Create database (in MySQL)
mysql -u root -p
CREATE DATABASE nova_starter;
EXIT;

# Run migrations
php artisan migrate

# Seed roles, permissions, and users
# (Seeder will be updated for school roles: admin, staff, teacher, student, guardian)
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=AdminUserSeeder
```

### Step 5: Start Development (1 min)

Open **two terminal windows**:

**Terminal 1** - Backend:

```bash
php artisan serve
```

**Terminal 2** - Frontend:

```bash
npm run dev
```

### Step 6: Smoke-test the UI (4 min)

-   Visit `http://localhost:8000` to see the welcome page.
-   Try the language switcher and dark-mode toggle (top-right on guest layout).
-   Go to Login and sign in with a seeded account (below).
-   Use **Forgot password** (`/forgot-account`): enter email/phone, then NRC. An OTP is generated/stored with a 10-minute expiry (delivery will be wired later).

---

## 🧪 API Testing with cURL (Auth only)

### 1. Login

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@novahubmm.com",
    "password": "password"
  }'
```

### 3. Get Profile (Authenticated)

```bash
curl -X GET http://localhost:8000/api/v1/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 🎭 Test User Accounts

Seeder accounts (password `password`):

| Email                 | Role    | Notes                  |
| --------------------- | ------- | ---------------------- |
| admin@novahubmm.com   | admin   | Full access            |
| staff@novahubmm.com   | staff   | Dashboard + view users |
| teacher@novahubmm.com | teacher | Dashboard access       |

> Registration is disabled; admins provision accounts. Use the seeded users above for access.

---

## 🧪 Run Tests

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage
```

Expected output:

```
PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response

Tests:    1 passed (1 assertions)
Duration: 0.15s
```

---

## 📁 Project Structure Overview

```
scp/
├── app/
│   ├── DTOs/              # Data Transfer Objects
│   ├── Enums/             # Constants & Enumerations
│   ├── Helpers/           # Helper classes (ApiResponse)
│   ├── Http/
│   │   ├── Controllers/   # API Controllers
│   │   ├── Requests/      # Validation requests
│   │   └── Resources/     # API response transformers
│   ├── Interfaces/        # Repository contracts
│   ├── Models/            # Eloquent models
│   ├── Repositories/      # Data access layer
│   └── Services/          # Business logic layer
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── routes/
│   └── api.php           # API routes
└── tests/                # Pest tests
```

---

## 🔐 Available API Endpoints (current)

| Method | Endpoint          | Description      |
| ------ | ----------------- | ---------------- |
| POST   | `/api/v1/login`   | Login user       |
| POST   | `/api/v1/logout`  | Logout user      |
| GET    | `/api/v1/profile` | Get user profile |

Domain APIs (academic, schedules, attendance, finance, etc.) will be added next.

---

## 🎯 What's Implemented?

-   Breeze auth views with Tailwind/Alpine + dark mode
-   Guest welcome + two-step password recovery (identifier → NRC → OTP stored with expiry)
-   Roles/permissions: admin, staff, teacher, student, guardian (seeded)
-   Auth API (login/logout/profile) with Sanctum
-   User/role/permission management screens
-   PWA/push assets present but disabled

---

## 🚀 Next Steps

1. Build academic modules (schools, grades, classes, schedules, attendance, finance, events/announcements).
2. Wire OTP delivery for recovery (email/SMS) and add verification step.
3. Re-enable PWA/push once dashboards are stable.
4. Add Pest feature/unit tests for auth, access control, and recovery flow.
5. Keep docs in sync (`README`, `IMPLEMENTATION_GUIDE`, API docs).

---

## ❓ Troubleshooting

### Database Connection Error

```bash
# Check MySQL is running
mysql -u root -p

# Verify database exists
SHOW DATABASES;

# Check .env credentials match
```

### Permission Denied Errors

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

### Composer Install Issues

```bash
# Clear composer cache
composer clear-cache
composer install
```

### NPM Issues

```bash
# Clear node modules
rm -rf node_modules package-lock.json
npm install
```

### Migration Errors

```bash
# Fresh migration (WARNING: Deletes all data!)
php artisan migrate:fresh --seed
```

---

## 📞 Getting Help

If you're stuck:

1. Check `IMPLEMENTATION_GUIDE.md`
2. Review error logs: `storage/logs/laravel.log`
3. Run tests to see what's failing
4. Ask team lead or senior developer

---

## ✨ Success Checklist

After setup, you should be able to:

-   [ ] Access http://localhost:8000 and toggle theme/language
-   [ ] Login with seeded admin/staff/teacher users
-   [ ] Start password recovery (email/phone → NRC) and see success notice
-   [ ] Call auth APIs (login/profile) successfully
-   [ ] Run `./vendor/bin/pest` successfully

---

**Congratulations! 🎉**

You now have a fully functional Laravel starter project following Nova Hub best practices!

---

**Version**: v1.0.1  
**Last Updated**: December 6, 2025  
**Support**: dev@novahubmm.com

> "Every commit tells our story — build it with care and clarity."
