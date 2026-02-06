# Multi-Role API - Quick Reference Card

## 🚀 Quick Start

### 1. Login (Multi-Role User)
```bash
POST /api/v1/auth/login
```
```json
{
    "login": "user@example.com",
    "password": "password123",
    "device_name": "iPhone 15"
}
```

**Response includes:**
- `available_roles`: `["teacher", "guardian"]`
- `tokens`: `{ teacher: "...", guardian: "..." }`
- `user_data`: `{ teacher: {...}, guardian: {...} }`

---

### 2. Check Available Roles
```bash
GET /api/v1/auth/available-roles
Authorization: Bearer {token}
```

**Response:**
```json
{
    "available_roles": ["teacher", "guardian"],
    "role_data": {
        "teacher": { "type": "teacher", "data": {...} },
        "guardian": { "type": "guardian", "data": {...} }
    },
    "has_multiple_roles": true
}
```

---

### 3. Switch Role
```bash
POST /api/v1/auth/switch-role
Authorization: Bearer {current_token}
```
```json
{
    "role": "teacher",
    "device_name": "iPhone 15"
}
```

**Response:**
```json
{
    "user": { /* teacher profile */ },
    "user_type": "teacher",
    "token": "new_token_here",
    "expires_at": "2026-03-05T10:30:00.000000Z"
}
```

---

## 📱 Mobile App Storage Keys

| Key | Purpose | Example |
|-----|---------|---------|
| `@smartcampus_auth_token` | Teacher token | `"2\|xyz..."` |
| `access_token` | Guardian token | `"3\|def..."` |
| `@smartcampus_active_role` | Current role | `"teacher"` |
| `@smartcampus_available_roles` | Available roles | `["teacher","guardian"]` |

---

## 🔑 Response Fields

### Single Role Response
```typescript
{
    user: UserProfile;
    user_type: "teacher" | "guardian";
    token: string;
    token_type: "Bearer";
    expires_at: string;
    permissions: string[];
    roles: string[];
}
```

### Multi-Role Response
```typescript
{
    user: UserProfile;              // Default role profile
    user_data: {                    // All role profiles
        teacher?: TeacherProfile;
        guardian?: GuardianProfile;
    };
    user_type: "teacher" | "guardian";
    available_roles: string[];      // ["teacher", "guardian"]
    tokens: {                       // Separate tokens
        teacher?: string;
        guardian?: string;
    };
    token: string;                  // Default token
    token_type: "Bearer";
    expires_at: string;
    permissions: string[];
    roles: string[];
}
```

---

## 🧪 Testing Commands

### Create Multi-Role Test User
```sql
-- Add both roles to a user
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', 1
FROM roles r
WHERE r.name IN ('teacher', 'guardian');
```

### Verify User Roles
```sql
SELECT u.email, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.id = 1;
```

---

## 🔄 User Flow

```
┌─────────────────────────────────────────────────┐
│ 1. User Login                                   │
│    POST /api/v1/auth/login                      │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│ 2. Backend Checks Roles                         │
│    - Single role? → Standard response           │
│    - Multiple roles? → Multi-role response      │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│ 3. Mobile App Stores Tokens                     │
│    - Teacher token → @smartcampus_auth_token    │
│    - Guardian token → access_token              │
│    - Active role → @smartcampus_active_role     │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│ 4. User Switches Role (Optional)                │
│    - Settings → Switch Role                     │
│    - Update active role in storage              │
│    - Navigate to new portal                     │
└─────────────────────────────────────────────────┘
```

---

## ⚡ Common Operations

### Check if User Has Multiple Roles
```typescript
const hasMultipleRoles = response.data.available_roles?.length > 1;
```

### Get Token for Specific Role
```typescript
const teacherToken = response.data.tokens?.teacher;
const guardianToken = response.data.tokens?.guardian;
```

### Determine Default Role
```typescript
const defaultRole = response.data.user_type; // "guardian" preferred
```

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| User has multiple roles but API returns single role | Check database: `SELECT * FROM model_has_roles WHERE model_id = ?` |
| Switch role returns 403 | Verify user has target role in database |
| Token expired | Call `/api/v1/auth/switch-role` to get new token |
| Mobile app doesn't show switch option | Check if `available_roles` is in login response |

---

## 📚 Documentation Files

- **Complete Guide:** `MULTI_ROLE_API_GUIDE.md`
- **Checklist:** `MULTI_ROLE_IMPLEMENTATION_CHECKLIST.md`
- **Summary:** `MULTI_ROLE_UPDATE_SUMMARY.md`
- **Postman:** `Multi_Role_API.postman_collection.json`

---

## 🎯 Key Points

✅ **Backward Compatible** - Single-role users work as before  
✅ **Automatic Detection** - No manual role selection needed  
✅ **Separate Tokens** - Each role has its own token  
✅ **Seamless Switching** - No logout required  
✅ **Secure** - Role verification on every request  

---

**Version:** 2.0.0  
**Last Updated:** February 6, 2026
