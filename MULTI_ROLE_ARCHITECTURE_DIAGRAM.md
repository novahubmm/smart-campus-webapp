# Multi-Role Architecture Diagram

## System Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         SMART CAMPUS SYSTEM                         │
│                     Multi-Role User Support v2.0                    │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                          MOBILE APP LAYER                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────┐         ┌──────────────────┐                │
│  │  Unified Login   │         │  Role Switcher   │                │
│  │     Screen       │────────▶│   (Settings)     │                │
│  └──────────────────┘         └──────────────────┘                │
│           │                             │                           │
│           │                             │                           │
│           ▼                             ▼                           │
│  ┌─────────────────────────────────────────────┐                  │
│  │         AsyncStorage (Token Manager)        │                  │
│  ├─────────────────────────────────────────────┤                  │
│  │ @smartcampus_auth_token    → Teacher Token  │                  │
│  │ access_token               → Guardian Token │                  │
│  │ @smartcampus_active_role   → Current Role   │                  │
│  │ @smartcampus_available_roles → Role Array   │                  │
│  └─────────────────────────────────────────────┘                  │
│           │                             │                           │
│           ▼                             ▼                           │
│  ┌──────────────────┐         ┌──────────────────┐                │
│  │  Teacher Portal  │         │  Guardian Portal │                │
│  │   (TeacherApp)   │         │   (ParentApp)    │                │
│  └──────────────────┘         └──────────────────┘                │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  │ HTTPS/REST API
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                          BACKEND API LAYER                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │              UnifiedAuthController                          │  │
│  ├─────────────────────────────────────────────────────────────┤  │
│  │                                                             │  │
│  │  POST /api/v1/auth/login                                    │  │
│  │  ┌──────────────────────────────────────────────────────┐  │  │
│  │  │ 1. Validate credentials                              │  │  │
│  │  │ 2. Check user roles (teacher? guardian? both?)       │  │  │
│  │  │ 3. Generate appropriate response:                    │  │  │
│  │  │    - Single role → handleTeacherLogin() or           │  │  │
│  │  │                    handleGuardianLogin()             │  │  │
│  │  │    - Multi-role → handleMultiRoleLogin()             │  │  │
│  │  └──────────────────────────────────────────────────────┘  │  │
│  │                                                             │  │
│  │  GET /api/v1/auth/available-roles                           │  │
│  │  ┌──────────────────────────────────────────────────────┐  │  │
│  │  │ 1. Get authenticated user                            │  │  │
│  │  │ 2. Check all roles user has                          │  │  │
│  │  │ 3. Return role data with details                     │  │  │
│  │  └──────────────────────────────────────────────────────┘  │  │
│  │                                                             │  │
│  │  POST /api/v1/auth/switch-role                              │  │
│  │  ┌──────────────────────────────────────────────────────┐  │  │
│  │  │ 1. Verify user has requested role                    │  │  │
│  │  │ 2. Generate new token for target role                │  │  │
│  │  │ 3. Return role-specific profile data                 │  │  │
│  │  └──────────────────────────────────────────────────────┘  │  │
│  │                                                             │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                  │                                  │
│                                  ▼                                  │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │              Authentication Repositories                    │  │
│  ├─────────────────────────────────────────────────────────────┤  │
│  │  TeacherAuthRepository  │  GuardianAuthRepository           │  │
│  │  - createToken()        │  - createToken()                  │  │
│  │  - findTeacherByLogin() │  - findGuardianByLogin()          │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                  │                                  │
│                                  ▼                                  │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │                    Laravel Sanctum                          │  │
│  │                  (Token Management)                         │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                          DATABASE LAYER                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────┐    ┌──────────────────┐    ┌─────────────────┐  │
│  │    users     │    │ model_has_roles  │    │     roles       │  │
│  ├──────────────┤    ├──────────────────┤    ├─────────────────┤  │
│  │ id           │◀───│ model_id         │───▶│ id              │  │
│  │ name         │    │ role_id          │    │ name (teacher)  │  │
│  │ email        │    │ model_type       │    │ name (guardian) │  │
│  │ password     │    └──────────────────┘    └─────────────────┘  │
│  │ is_active    │                                                  │
│  └──────────────┘                                                  │
│         │                                                           │
│         ├──────────────────┬────────────────────┐                  │
│         ▼                  ▼                    ▼                  │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────┐          │
│  │teacher_      │   │guardian_     │   │personal_     │          │
│  │profiles      │   │profiles      │   │access_tokens │          │
│  ├──────────────┤   ├──────────────┤   ├──────────────┤          │
│  │ user_id      │   │ user_id      │   │ tokenable_id │          │
│  │ teacher_id   │   │ students     │   │ name         │          │
│  │ department   │   │ relationship │   │ token        │          │
│  └──────────────┘   └──────────────┘   │ abilities    │          │
│                                         └──────────────┘          │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Authentication Flow Diagram

### Single Role User Flow

```
┌──────────┐
│  User    │
└────┬─────┘
     │
     │ 1. Login (email + password)
     ▼
┌─────────────────┐
│  Mobile App     │
│  Login Screen   │
└────┬────────────┘
     │
     │ 2. POST /api/v1/auth/login
     ▼
┌─────────────────────────────────┐
│  Backend: UnifiedAuthController │
└────┬────────────────────────────┘
     │
     │ 3. Check roles
     │    hasRole('teacher')? → YES
     │    hasRole('guardian')? → NO
     ▼
┌─────────────────────────────────┐
│  handleTeacherLogin()           │
│  - Generate teacher token       │
│  - Load teacher profile         │
└────┬────────────────────────────┘
     │
     │ 4. Return response
     │    {
     │      user: {...},
     │      user_type: "teacher",
     │      token: "abc123..."
     │    }
     ▼
┌─────────────────────────────────┐
│  Mobile App                     │
│  - Store token                  │
│  - Navigate to Teacher Portal   │
└─────────────────────────────────┘
```

---

### Multi-Role User Flow

```
┌──────────┐
│  User    │
└────┬─────┘
     │
     │ 1. Login (email + password)
     ▼
┌─────────────────┐
│  Mobile App     │
│  Login Screen   │
└────┬────────────┘
     │
     │ 2. POST /api/v1/auth/login
     ▼
┌─────────────────────────────────┐
│  Backend: UnifiedAuthController │
└────┬────────────────────────────┘
     │
     │ 3. Check roles
     │    hasRole('teacher')? → YES
     │    hasRole('guardian')? → YES
     ▼
┌─────────────────────────────────┐
│  handleMultiRoleLogin()         │
│  - Generate teacher token       │
│  - Generate guardian token      │
│  - Load both profiles           │
└────┬────────────────────────────┘
     │
     │ 4. Return multi-role response
     │    {
     │      user: {...},
     │      user_data: {
     │        teacher: {...},
     │        guardian: {...}
     │      },
     │      user_type: "guardian",
     │      available_roles: ["teacher", "guardian"],
     │      tokens: {
     │        teacher: "abc123...",
     │        guardian: "def456..."
     │      }
     │    }
     ▼
┌─────────────────────────────────┐
│  Mobile App                     │
│  - Store both tokens            │
│  - Store available roles        │
│  - Navigate to Guardian Portal  │
│    (default)                    │
│  - Show "Switch Role" in        │
│    Settings                     │
└─────────────────────────────────┘
```

---

## Role Switching Flow

```
┌──────────────────────────────────┐
│  User in Guardian Portal         │
└────┬─────────────────────────────┘
     │
     │ 1. Navigate to Settings
     ▼
┌──────────────────────────────────┐
│  Settings Screen                 │
│  - Shows "Switch to Teacher"     │
└────┬─────────────────────────────┘
     │
     │ 2. User clicks "Switch"
     ▼
┌──────────────────────────────────┐
│  Confirmation Dialog             │
│  "Switch to Teacher Portal?"     │
└────┬─────────────────────────────┘
     │
     │ 3. User confirms
     ▼
┌──────────────────────────────────┐
│  Mobile App Logic                │
│  - Update active role storage    │
│  - Switch to teacher token       │
└────┬─────────────────────────────┘
     │
     │ 4. Navigate to Teacher Portal
     ▼
┌──────────────────────────────────┐
│  Teacher Portal                  │
│  - Uses teacher token            │
│  - Shows teacher features        │
│  - Settings shows "Switch to     │
│    Guardian"                     │
└──────────────────────────────────┘

Note: No API call needed if token is still valid!
      Only call /api/v1/auth/switch-role if token expired.
```

---

## Token Management Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    TOKEN LIFECYCLE                          │
└─────────────────────────────────────────────────────────────┘

Login (Multi-Role User)
    │
    ├─▶ Generate Teacher Token
    │   ├─ Store in: @smartcampus_auth_token
    │   ├─ Expires: 30 days
    │   └─ Abilities: teacher permissions
    │
    └─▶ Generate Guardian Token
        ├─ Store in: access_token
        ├─ Expires: 7-30 days (remember_me)
        └─ Abilities: guardian permissions

Role Switch (No Re-auth)
    │
    ├─▶ Token Valid?
    │   ├─ YES → Use existing token
    │   │        Update active role
    │   │        Navigate to portal
    │   │
    │   └─ NO  → Call /api/v1/auth/switch-role
    │            Get new token
    │            Update storage
    │            Navigate to portal

Logout
    │
    └─▶ Delete current token only
        (Other role token remains valid)
```

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    REQUEST FLOW                             │
└─────────────────────────────────────────────────────────────┘

Mobile App                Backend                  Database
    │                        │                         │
    │  POST /auth/login      │                         │
    ├───────────────────────▶│                         │
    │                        │  Query user + roles     │
    │                        ├────────────────────────▶│
    │                        │                         │
    │                        │◀────────────────────────┤
    │                        │  User with roles        │
    │                        │                         │
    │                        │  Check roles:           │
    │                        │  - teacher? ✓           │
    │                        │  - guardian? ✓          │
    │                        │                         │
    │                        │  Generate tokens        │
    │                        ├────────────────────────▶│
    │                        │  Store tokens           │
    │                        │                         │
    │                        │◀────────────────────────┤
    │                        │  Tokens created         │
    │                        │                         │
    │◀───────────────────────┤                         │
    │  Multi-role response   │                         │
    │  with both tokens      │                         │
    │                        │                         │
    │  Store tokens locally  │                         │
    │                        │                         │
    │  GET /dashboard        │                         │
    │  (with teacher token)  │                         │
    ├───────────────────────▶│                         │
    │                        │  Verify token           │
    │                        ├────────────────────────▶│
    │                        │                         │
    │                        │◀────────────────────────┤
    │                        │  Token valid            │
    │                        │                         │
    │                        │  Get teacher data       │
    │                        ├────────────────────────▶│
    │                        │                         │
    │                        │◀────────────────────────┤
    │                        │  Teacher dashboard      │
    │                        │                         │
    │◀───────────────────────┤                         │
    │  Teacher dashboard     │                         │
    │                        │                         │
```

---

## Security Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                          │
└─────────────────────────────────────────────────────────────┘

Layer 1: Authentication
    ├─ Email/Password validation
    ├─ Account active check
    └─ Rate limiting

Layer 2: Authorization
    ├─ Role verification (hasRole())
    ├─ Permission check (getAllPermissions())
    └─ Token validation (Sanctum)

Layer 3: Token Management
    ├─ Separate tokens per role
    ├─ Token expiration
    ├─ Token abilities/scopes
    └─ Secure storage (Keychain/Keystore)

Layer 4: API Security
    ├─ HTTPS only
    ├─ CORS configuration
    ├─ Input validation
    └─ SQL injection prevention

Layer 5: Data Protection
    ├─ Password hashing (bcrypt)
    ├─ Sensitive data encryption
    └─ PII protection
```

---

## Scalability Architecture

```
┌─────────────────────────────────────────────────────────────┐
│              FUTURE ROLE ADDITIONS                          │
└─────────────────────────────────────────────────────────────┘

Current Roles:
    ├─ Teacher
    └─ Guardian

Easy to Add:
    ├─ Admin
    ├─ Student
    ├─ Staff
    └─ Principal

Implementation:
    1. Add role to database
    2. Create profile table (if needed)
    3. Add to UnifiedAuthController logic
    4. Create mobile portal
    5. Done!

The architecture supports unlimited roles!
```

---

**Version:** 2.0.0  
**Last Updated:** February 6, 2026
