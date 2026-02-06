# Multi-Role User Support - API Guide

## Overview

This guide explains how the Smart Campus API supports users with multiple roles (e.g., a user who is both a teacher and a guardian). The unified authentication system automatically detects available roles and provides appropriate tokens for seamless role switching.

---

## Key Features

✅ **Automatic Role Detection** - Login API detects all roles a user has  
✅ **Multiple Tokens** - Separate tokens for each role  
✅ **Seamless Role Switching** - Switch roles without re-authentication  
✅ **Backward Compatible** - Works with single-role users  
✅ **Secure** - Role verification on every request  

---

## Authentication Flow

### 1. Login Request

**Endpoint:** `POST /api/v1/auth/login`

```json
{
    "login": "user@example.com",
    "password": "password123",
    "device_name": "iPhone 15",
    "remember_me": true
}
```

### 2. Login Response - Single Role User

If user has only ONE role (teacher OR guardian):

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890"
        },
        "user_type": "teacher",
        "token": "1|abc123...",
        "token_type": "Bearer",
        "expires_at": "2026-03-05T10:30:00.000000Z",
        "permissions": ["view_classes", "take_attendance"],
        "roles": ["teacher"]
    }
}
```

### 3. Login Response - Multi-Role User

If user has MULTIPLE roles (teacher AND guardian):

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890"
            // Guardian profile data (default role)
        },
        "user_data": {
            "teacher": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "teacher_profile": {
                    "teacher_id": "TCH-001",
                    "department": {
                        "id": 1,
                        "name": "Mathematics"
                    },
                    "position": "Senior Teacher"
                }
            },
            "guardian": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "guardian_profile": {
                    "students": [
                        {
                            "id": 10,
                            "name": "Jane Doe",
                            "grade": "Grade 5",
                            "section": "A"
                        }
                    ]
                }
            }
        },
        "user_type": "guardian",
        "available_roles": ["teacher", "guardian"],
        "tokens": {
            "teacher": "2|xyz789...",
            "guardian": "3|def456..."
        },
        "token": "3|def456...",
        "token_type": "Bearer",
        "expires_at": "2026-03-05T10:30:00.000000Z",
        "permissions": ["view_classes", "take_attendance", "view_children"],
        "roles": ["teacher", "guardian"]
    }
}
```

**Key Fields for Multi-Role:**
- `available_roles`: Array of roles the user has
- `tokens`: Object with separate token for each role
- `user_data`: Object with profile data for each role
- `user_type`: Default role (guardian preferred)
- `token`: Default token (for backward compatibility)

---

## Mobile App Implementation

### 1. Handle Login Response

```typescript
interface LoginResponse {
    success: boolean;
    message: string;
    data: {
        user: any;
        user_data?: {
            teacher?: any;
            guardian?: any;
        };
        user_type: 'teacher' | 'guardian';
        available_roles?: string[];
        tokens?: {
            teacher?: string;
            guardian?: string;
        };
        token: string;
        token_type: string;
        expires_at: string;
        permissions: string[];
        roles: string[];
    };
}

async function handleLogin(credentials: LoginCredentials) {
    const response = await api.post<LoginResponse>('/api/v1/auth/login', credentials);
    
    if (response.data.success) {
        const { data } = response.data;
        
        // Check if user has multiple roles
        if (data.available_roles && data.available_roles.length > 1) {
            // Multi-role user
            await handleMultiRoleLogin(data);
        } else {
            // Single-role user
            await handleSingleRoleLogin(data);
        }
    }
}
```

### 2. Store Tokens for Multi-Role Users

```typescript
async function handleMultiRoleLogin(data: any) {
    // Store all tokens
    if (data.tokens?.teacher) {
        await AsyncStorage.setItem('@smartcampus_auth_token', data.tokens.teacher);
    }
    if (data.tokens?.guardian) {
        await AsyncStorage.setItem('access_token', data.tokens.guardian);
    }
    
    // Store available roles
    await AsyncStorage.setItem(
        '@smartcampus_available_roles', 
        JSON.stringify(data.available_roles)
    );
    
    // Store default role
    await AsyncStorage.setItem('@smartcampus_active_role', data.user_type);
    
    // Store user data for each role
    if (data.user_data) {
        await AsyncStorage.setItem(
            '@smartcampus_user_data',
            JSON.stringify(data.user_data)
        );
    }
}
```

### 3. Check Available Roles

**Endpoint:** `GET /api/v1/auth/available-roles`

```typescript
async function checkAvailableRoles() {
    const response = await api.get('/api/v1/auth/available-roles', {
        headers: {
            'Authorization': `Bearer ${currentToken}`
        }
    });
    
    return response.data;
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "available_roles": ["teacher", "guardian"],
        "role_data": {
            "teacher": {
                "type": "teacher",
                "data": {
                    "teacher_id": "TCH-001",
                    "department": "Mathematics",
                    "position": "Senior Teacher"
                }
            },
            "guardian": {
                "type": "guardian",
                "data": {
                    "students": [
                        {
                            "name": "Jane Doe",
                            "grade": "Grade 5",
                            "section": "A"
                        }
                    ],
                    "student_count": 1
                }
            }
        },
        "has_multiple_roles": true
    }
}
```

### 4. Switch Role

**Endpoint:** `POST /api/v1/auth/switch-role`

```typescript
async function switchRole(targetRole: 'teacher' | 'guardian') {
    const response = await api.post('/api/v1/auth/switch-role', {
        role: targetRole,
        device_name: 'iPhone 15'
    }, {
        headers: {
            'Authorization': `Bearer ${currentToken}`
        }
    });
    
    if (response.data.success) {
        const { token, user_type } = response.data.data;
        
        // Update stored token
        if (user_type === 'teacher') {
            await AsyncStorage.setItem('@smartcampus_auth_token', token);
        } else {
            await AsyncStorage.setItem('access_token', token);
        }
        
        // Update active role
        await AsyncStorage.setItem('@smartcampus_active_role', user_type);
        
        return response.data.data;
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Switched to teacher role successfully",
    "data": {
        "user": {
            // Teacher profile data
        },
        "user_type": "teacher",
        "token": "4|new_token_here...",
        "token_type": "Bearer",
        "expires_at": "2026-03-05T10:30:00.000000Z"
    }
}
```

---

## UI/UX Flow

### Flow 1: Single Role User
```
Login → Auto-detect role → Direct to portal
```

### Flow 2: Multi-Role User (First Time)
```
Login → Receive all tokens → Show default role portal
      → Settings shows "Switch to [Other Role]" option
```

### Flow 3: Role Switching
```
Settings → Click "Switch to Teacher/Guardian"
        → Confirm dialog
        → Call switch-role API (optional, if token expired)
        → Update active role in storage
        → Navigate to new portal
```

---

## Storage Keys

The mobile app uses these AsyncStorage keys:

| Key | Purpose | Example Value |
|-----|---------|---------------|
| `@smartcampus_auth_token` | Teacher token | `"2\|xyz789..."` |
| `access_token` | Guardian token | `"3\|def456..."` |
| `@smartcampus_active_role` | Current active role | `"teacher"` or `"guardian"` |
| `@smartcampus_available_roles` | Available roles array | `["teacher","guardian"]` |
| `@smartcampus_user_data` | User data for all roles | `{teacher: {...}, guardian: {...}}` |

---

## Security Considerations

### 1. Token Management
- Each role has its own token
- Tokens are validated on every API request
- Expired tokens require re-authentication or switch-role call

### 2. Role Verification
- Backend verifies user has requested role
- Middleware checks role permissions
- Cross-role access is prevented

### 3. Token Expiration
- Teacher tokens: 30 days
- Guardian tokens: 7-30 days (based on remember_me)
- Tokens can be refreshed via switch-role endpoint

---

## Testing

### Test Case 1: Single Role User
1. Login with teacher-only account
2. Verify `available_roles` is not present or has single role
3. Verify only one token is returned
4. Verify direct access to teacher portal

### Test Case 2: Multi-Role User
1. Login with account that has both roles
2. Verify `available_roles` contains both roles
3. Verify `tokens` object has both tokens
4. Verify `user_data` has data for both roles
5. Test role switching without logout

### Test Case 3: Role Switching
1. Login as multi-role user
2. Navigate to Settings
3. Click "Switch to [Other Role]"
4. Verify role switch happens without logout
5. Verify correct token is used for API calls
6. Switch back to original role

### Test Case 4: Available Roles Endpoint
1. Login as multi-role user
2. Call `/api/v1/auth/available-roles`
3. Verify response contains all roles
4. Verify role_data is populated correctly

---

## Error Handling

### Invalid Role Switch
```json
{
    "success": false,
    "message": "You don't have access to teacher role"
}
```

### Expired Token
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### Invalid Credentials
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

---

## Migration Guide

### For Existing Single-Role Apps

The API is backward compatible. Existing apps will continue to work without changes:

1. Single-role users get the same response format
2. `token` field is always present (default token)
3. `user_type` field is always present
4. New fields (`available_roles`, `tokens`, `user_data`) are optional

### For New Multi-Role Apps

1. Check for `available_roles` in login response
2. Store all tokens from `tokens` object
3. Use `user_data` to get role-specific profile data
4. Implement role switching UI
5. Use switch-role endpoint when needed

---

## Postman Collection

Import the updated `UNIFIED_APP_POSTMAN_COLLECTION.json` to test:

1. **Login (Single Role)** - Test with teacher-only account
2. **Login (Multi-Role)** - Test with account having both roles
3. **Available Roles** - Check available roles for current user
4. **Switch Role** - Switch from one role to another
5. **Profile** - Get profile for current role

---

## Support

For issues or questions:
- Check the implementation guide: `UNIFIED_APP_IMPLEMENTATION_GUIDE.md`
- Review role switching architecture: `SmartCampusv1.0.0/ROLE_SWITCHING_ARCHITECTURE.md`
- Test with Postman collection

---

**Last Updated:** February 6, 2026  
**Version:** 2.0.0  
**Status:** ✅ Production Ready
