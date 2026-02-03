# 📘 School Management Implementation Guide

## 🎯 Overview

This is a comprehensive school management platform built with Laravel. The stack uses Tailwind + Alpine + dark mode, with modern dashboard designs. PWA and push notifications are present but **disabled for now**.

---

## ✅ Implementation Checklist

### Phase 1: Starter foundation (COMPLETED ✅)
-   [x] Base Laravel 12 starter with Sanctum, Spatie permissions scaffold, Repository/Service/DTO pattern, ApiResponse helper, auth controllers/routes.

### Phase 2: Identity & roles (COMPLETED ✅)
-   [x] Finalize roles (admin, staff, teacher, student, guardian) and seed permissions for dashboard/access control.
-   [x] Add phone/NRC/OTP fields to users for recovery; seed default users with those fields.
-   [x] Gate dashboard via `@can` and simplify navigation to access controls.

### Phase 3: Domain modeling & data
-   [ ] Create/extend models, migrations, services, and controllers for academic data, users, schedules, attendance, fees/payments, events, announcements.
-   [ ] Ensure DTOs/resources include fields needed for dashboards and filtering.

### Phase 4: Dashboards & UI integration
-   [ ] Port `blade_prototype` layouts to Tailwind/Alpine with dark-mode support.
-   [ ] Wire dashboard widgets/tables to real data; add localization where needed.

### Phase 5: API/services & quality
-   [ ] Build API endpoints for the above domains with tests (Pest) and ≥70% coverage.
-   [ ] Keep SOLID/PSR-12, add feature/unit tests for role gating and dashboards.

### Phase 6: PWA/Push & docs
-   [ ] Keep PWA/service worker and push notification assets disabled until feature parity is ready; add re-enable plan.
-   [ ] Update docs (README, API docs, QUICKSTART) and .env.example as features ship.

---

## 📋 Detailed Implementation Steps

### PHASE 2: Identity, roles, and recovery (completed)

- Add phone + NRC + OTP fields to `users` (unique; OTP expires in 10 minutes).
- Seed roles/permissions: admin, staff, teacher, student, guardian. Admin gets all; staff can view dashboard/users; others have dashboard access for now.
- Seed default users with phone/NRC for recovery; default API registration assigns the `student` role.
- Guest recovery flow: `/forgot-account` (identifier) → `/forgot-account/verify` (NRC) → OTP saved for future delivery.
- Login/guest layouts expose dark-mode toggle and language switcher.

### PHASE 3: Domain modeling & dashboards (up next)

- Create models/migrations/services/controllers for: schools, grades, classes, schedules, attendance, fees/payments, events, announcements.
- Add dashboard widgets backed by real data; keep Tailwind/Alpine/dark-mode styling from `blade_prototype`.
- Expand permissions per module; gate dashboards with `@can`.

### PHASE 4: Recovery delivery & verification

- Integrate email/SMS delivery for OTP and add verification + password reset completion screens/APIs.
- Add throttling/rate limits and audit logging for recovery attempts.

### PHASE 5: Testing

- Feature tests: auth/login/logout/profile, password recovery flow, access control for roles.
- API tests for new domains as they arrive.
- Keep Pest coverage ≥70%.

### PHASE 6: PWA/Push & docs

- Keep service worker/manifest/push disabled until dashboards + recovery are stable.
- Document re-enable steps and QA checklist when ready.
- Keep README/QUICKSTART/API docs in sync as modules land.

---

## 🎨 Best Practices Applied

### 1. SOLID Principles

-   ✅ **Single Responsibility**: Each class has one purpose
-   ✅ **Open/Closed**: Extendable via interfaces
-   ✅ **Liskov Substitution**: Interfaces are substitutable
-   ✅ **Interface Segregation**: Focused interfaces
-   ✅ **Dependency Inversion**: Depend on abstractions (interfaces)

### 2. Repository Pattern

```
Controller → Service → Repository → Model → Database
```

### 3. DTOs (Data Transfer Objects)

```php
// Clean data flow
$data = LoginData::from($request->validated());
$result = $this->authService->login($data);
```

### 4. Standardized API Responses

```php
// Consistent across all endpoints
return ApiResponse::success($data, 'Message', 200);
return ApiResponse::error('Message', 400);
```

### 5. Dependency Injection

```php
// Constructor injection
public function __construct(
    private readonly AuthService $authService
) {}
```

### 6. Form Request Validation

```php
// Separate validation logic
public function store(StoreUserRequest $request) {
    // $request->validated() is already validated
}
```

### 7. Enums for Constants

```php
// Type-safe constants
$user->assignRole(RoleEnum::ADMIN->value);
```

---

## 🔍 Code Quality Checks

### Before Every Commit

1. **Run Tests**

    ```bash
    ./vendor/bin/pest
    ```

2. **Check Code Style** (PSR-12)

    ```bash
    ./vendor/bin/pint
    ```

3. **Static Analysis** (Future)
    ```bash
    ./vendor/bin/phpstan analyse
    ```

### Code Review Checklist

-   [ ] Follows PSR-12 coding standard
-   [ ] Uses DTOs for data transfer
-   [ ] Uses ApiResponse for all responses
-   [ ] Has proper validation (Form Requests)
-   [ ] Includes tests (70%+ coverage)
-   [ ] No hard-coded secrets
-   [ ] No direct Model access from Controllers
-   [ ] Proper error handling
-   [ ] Meaningful variable/method names
-   [ ] Comments for complex logic

---

## 🚀 Next Steps After Implementation

### 1. Additional Features

-   Password reset
-   Email verification
-   Two-factor authentication
-   User profile update
-   Bulk operations

### 2. Advanced Authorization

-   Custom permissions
-   Permission groups
-   Dynamic role assignment
-   Hierarchical roles

### 3. API Enhancements

-   API versioning (already v1)
-   Rate limiting
-   API documentation (Swagger/OpenAPI)
-   Response caching
-   Pagination
-   Filtering & sorting

### 4. DevOps

-   GitHub Actions CI/CD
-   Docker containerization
-   Automated testing
-   Deployment scripts

### 5. Monitoring

-   Error tracking (Sentry)
-   Performance monitoring
-   Log aggregation
-   API analytics

---

## 📞 Getting Help

If you encounter issues:

1. Check the **Smart Campus Documentation**
2. Review **Laravel 12 Documentation**
3. Check **Spatie Permissions Documentation**
4. Ask team lead or senior developer
5. Search GitHub issues

---

## 🎓 Learning Resources

-   [Laravel Documentation](https://laravel.com/docs)
-   [Pest Documentation](https://pestphp.com)
-   [Spatie Permissions](https://spatie.be/docs/laravel-permission)
-   [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
-   [Repository Pattern in Laravel](https://laravel.com/docs/repositories)

---

**Document Version**: v1.0.0  
**Last Updated**: November 13, 2025  
**Author**: Smart Campus Development Team

> "Every commit tells our story — build it with care and clarity."
