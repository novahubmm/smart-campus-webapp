# Smart Campus Development Workflow

## Overview
This document defines the Git workflow and feature flag approach for Smart Campus development. We use a single codebase with feature flags to manage multiple schools, avoiding the complexity of maintaining separate school branches.

---

## Branch Strategy

### Main Branches
```
main (production - always stable)
├── develop (integration/testing)
├── feature/* (new features & improvements)
├── fix/* (bug fixes)
└── hotfix/* (urgent production fixes)
```

### Branch Types

| Branch Type | Purpose | Created From | Merged To | Example |
|------------|---------|--------------|-----------|---------|
| `main` | Production-ready code | - | - | `main` |
| `develop` | Integration & testing | `main` | `main` | `develop` |
| `feature/*` | New features & improvements | `develop` | `develop` | `feature/card-attendance` |
| `fix/*` | Bug fixes in develop | `develop` | `develop` | `fix/payroll-calculation` |
| `hotfix/*` | Urgent production fixes | `main` | `main` & `develop` | `hotfix/critical-login-bug` |

---

## Workflow Process

### Daily Workflow

When you start work each day, communicate your task:
- "I want to create [feature name] for all schools"
- "I want to create [feature name] for only School 1 and School 2"
- "I need to fix [bug description]"
- "Urgent: production issue with [feature]"

Kiro will:
1. Confirm branch creation or ask if needed
2. Create appropriate branch with proper naming
3. Make commits with standardized messages
4. Wait for your testing/completion keywords

### Keywords & Actions

| Keyword | Action | Description |
|---------|--------|-------------|
| `start testing` | Merge to `develop` | Move code to testing environment |
| `complete` | Merge to `main` | Deploy to production |
| `rollback` | Revert last merge | Undo recent changes |
| `hold` | Pause work | Stop current task, don't merge |
| `urgent fix` | Create `hotfix/*` | Critical production bug |

---

## Branch Naming Convention

### Features
```bash
feature/card-attendance
feature/parent-portal-redesign
feature/sms-notifications
```

### Bug Fixes
```bash
fix/payroll-calculation-error
fix/attendance-report-display
fix/login-validation
```

### Hotfixes
```bash
hotfix/critical-database-connection
hotfix/payment-gateway-failure
```

---

## Commit Message Format

Use conventional commit format:

```
<type>: <description>

[optional body]
[optional footer]
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `chore`: Maintenance (dependencies, config)
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `perf`: Performance improvements

### Examples
```bash
feat: Add card attendance feature with RFID support

fix: Resolve payroll calculation rounding error

chore: Update Laravel to 10.x

docs: Add API documentation for parent portal

refactor: Optimize database queries in attendance module
```

---

## Feature Flag Approach

### Concept
Instead of maintaining separate branches per school, we use ONE codebase with feature flags to control which features are visible to each school.

### Benefits
✅ Single source of truth  
✅ Easy bug fixes across all schools  
✅ No merge conflicts between school branches  
✅ Scalable for unlimited schools  
✅ Dynamic feature control via database  

### Implementation

#### 1. Database Structure
```sql
-- school_configs table
id | school_code | school_name | enabled_features | settings
1  | ykst        | YKST School | ["announcements","payroll","card_attendance"] | {...}
2  | abc         | ABC School  | ["announcements","fees"] | {...}
```

#### 2. Code Implementation
```php
// Check if feature is enabled
if (app(FeatureService::class)->isEnabled('card_attendance')) {
    // Show card attendance feature
}

// Protect routes
Route::middleware(['auth', 'feature:card_attendance'])->group(function () {
    Route::resource('card-attendance', CardAttendanceController::class);
});

// In Blade views
@if(app(FeatureService::class)->isEnabled('card_attendance'))
    <a href="{{ route('card-attendance.index') }}">Card Attendance</a>
@endif
```

#### 3. Feature Development Flow
```
1. Develop feature in feature/* branch (code for ALL schools)
2. Add feature flag checks in code
3. Merge to develop → test
4. Merge to main → deploy
5. Enable feature for specific schools via database/admin panel
```

---

## School Feature Matrix

Track which schools have which features enabled:

### Current Features

| Feature | YKST School | ABC School | XYZ School | Notes |
|---------|-------------|------------|------------|-------|
| Announcements | ✅ | ✅ | ✅ | Core feature |
| Payroll | ✅ | ❌ | ✅ | |
| Card Attendance | ✅ | ✅ | ❌ | RFID-based |
| Parent Portal | ✅ | ✅ | ✅ | Core feature |
| SMS Notifications | ✅ | ❌ | ❌ | Requires SMS gateway |
| Online Fees | ✅ | ✅ | ❌ | Payment gateway integrated |

### How to Enable/Disable Features

#### Option 1: Database Seeder
```php
// database/seeders/SchoolConfigSeeder.php
DB::table('school_configs')->updateOrInsert(
    ['school_code' => 'ykst'],
    [
        'enabled_features' => json_encode([
            'announcements',
            'payroll',
            'card_attendance',
            'parent_portal',
            'sms_notifications',
            'online_fees'
        ])
    ]
);
```

#### Option 2: Admin Panel (Recommended)
Create an admin interface to toggle features per school dynamically.

#### Option 3: Artisan Command
```bash
php artisan school:enable-feature ykst card_attendance
php artisan school:disable-feature abc payroll
```

---

## Detailed Workflows

### 1. New Feature Development

```bash
# Day starts - You say:
"I want to add card attendance feature for YKST and ABC schools"

# Kiro confirms:
"Creating feature/card-attendance branch?"

# You confirm:
"Yes" or "Create branch"

# Kiro executes:
git checkout develop
git pull origin develop
git checkout -b feature/card-attendance

# Development happens...
# Commits made with proper format:
git commit -m "feat: Add card attendance controller and routes"
git commit -m "feat: Add RFID card scanning interface"
git commit -m "feat: Add attendance report generation"

# You say:
"start testing"

# Kiro merges to develop:
git checkout develop
git merge feature/card-attendance
git push origin develop

# After testing, you say:
"complete"

# Kiro merges to main:
git checkout main
git merge develop
git push origin main

# Enable for specific schools (manual or via admin):
# YKST: enable card_attendance
# ABC: enable card_attendance
# XYZ: keep disabled
```

### 2. Bug Fix in Feature Branch

```bash
# You say:
"Fix the payroll calculation bug in feature/payroll"

# Kiro executes:
git checkout feature/payroll

# Fix applied:
git commit -m "fix: Correct overtime calculation in payroll"

# Continue feature development or merge when ready
```

### 3. Bug Fix in Develop Branch

```bash
# You say:
"There's a bug in the attendance report on develop"

# Kiro confirms:
"Creating fix/attendance-report-bug branch?"

# You confirm:
"Yes"

# Kiro executes:
git checkout develop
git pull origin develop
git checkout -b fix/attendance-report-bug

# Fix applied:
git commit -m "fix: Resolve date range filter in attendance report"

# You say:
"start testing"

# Kiro merges to develop:
git checkout develop
git merge fix/attendance-report-bug
git push origin develop

# You say:
"complete"

# Kiro merges to main:
git checkout main
git merge develop
git push origin main
```

### 4. Urgent Production Hotfix

```bash
# You say:
"Urgent fix: login is broken in production"

# Kiro immediately creates hotfix:
git checkout main
git pull origin main
git checkout -b hotfix/critical-login-bug

# Fix applied:
git commit -m "hotfix: Fix authentication token validation"

# You say:
"complete" (no testing phase for hotfixes)

# Kiro merges to main AND develop:
git checkout main
git merge hotfix/critical-login-bug
git push origin main

git checkout develop
git merge hotfix/critical-login-bug
git push origin develop

# Hotfix branch deleted:
git branch -d hotfix/critical-login-bug
```

### 5. Small Improvement

```bash
# You say:
"Improve the UI of the dashboard"

# Kiro confirms:
"Creating feature/dashboard-ui-improvement branch?"

# You confirm:
"Yes"

# Development happens:
git commit -m "feat: Enhance dashboard card layout"
git commit -m "feat: Add responsive design to dashboard"

# Follow normal feature workflow...
```

---

## Branch Lifecycle

### Feature Branch
```
Created from: develop
Lifespan: Until feature complete
Merged to: develop
Deleted: After merge to develop
```

### Fix Branch
```
Created from: develop
Lifespan: Until bug fixed
Merged to: develop
Deleted: After merge to develop
```

### Hotfix Branch
```
Created from: main
Lifespan: Until critical fix deployed
Merged to: main AND develop
Deleted: Immediately after merge
```

---

## Current Branch Status

### Active Branches
- `main` - Production
- `develop` - Testing/Integration
- `feature/ykst-school-setup` - (Keep for now, will delete later)

### Branch Protection Rules
- `main` - Requires pull request review
- `develop` - Requires passing tests
- Direct commits to `main` - ❌ Not allowed
- Direct commits to `develop` - ⚠️ Avoid, use feature branches

---

## Best Practices

### DO ✅
- Always pull latest before creating new branch
- Use descriptive branch and commit names
- Test thoroughly before saying "start testing"
- Keep commits atomic and focused
- Add feature flags for new features
- Update this document when adding new schools

### DON'T ❌
- Don't commit directly to `main`
- Don't create school-specific branches
- Don't merge without testing
- Don't skip feature flag implementation
- Don't leave branches unmerged for weeks

---

## Quick Reference Commands

```bash
# Start new feature
git checkout develop && git pull && git checkout -b feature/feature-name

# Start bug fix
git checkout develop && git pull && git checkout -b fix/bug-description

# Start hotfix
git checkout main && git pull && git checkout -b hotfix/critical-issue

# Merge to develop
git checkout develop && git merge feature/feature-name && git push

# Merge to main
git checkout main && git merge develop && git push

# Delete branch after merge
git branch -d feature/feature-name
git push origin --delete feature/feature-name
```

---

## Troubleshooting

### Merge Conflicts
```bash
# If conflicts occur during merge:
git status  # See conflicting files
# Resolve conflicts manually
git add .
git commit -m "chore: Resolve merge conflicts"
```

### Rollback Last Merge
```bash
# If you need to undo a merge:
git revert -m 1 HEAD
git push
```

### Abandoned Feature
```bash
# If feature is cancelled:
git checkout develop
git branch -D feature/abandoned-feature
git push origin --delete feature/abandoned-feature
```

---

## Notes

- This workflow uses **Feature Flag Approach** - all code in one branch, features controlled by database
- School-specific customization is done via configuration, NOT separate branches
- All schools run the same codebase for easier maintenance
- Feature visibility is controlled dynamically per school

---

**Last Updated:** February 17, 2026  
**Maintained By:** Development Team  
**Questions?** Ask Kiro at the start of each day!
