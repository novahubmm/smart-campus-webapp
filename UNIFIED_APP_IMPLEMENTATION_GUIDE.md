# Unified Teacher-Guardian App Implementation Guide

## Overview

This guide provides implementation details for the unified mobile app that supports both teachers and guardians using a single codebase and API structure.

## Key Features

### 1. **Unified Authentication**
- Single login endpoint that works for both teachers and guardians
- Role-based response with user type identification
- Automatic token management and role detection

### 2. **Role-Based UI Flow**
- App determines user interface based on `user_type` from login response
- Dynamic navigation and feature access
- Shared components with role-specific customizations

### 3. **Unified API Endpoints**
- Common endpoints for shared features (notifications, dashboard, profile)
- Role-specific endpoints for specialized features
- Consistent response format across all endpoints

---

## API Structure

### Base URL
```
https://your-domain.com/api/v1
```

### Authentication Flow

#### 1. Login Request
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "login": "user@example.com",
    "password": "password123",
    "device_name": "iPhone 15",
    "remember_me": true
}
```

#### 2. Login Response
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": "uuid",
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            // Role-specific profile data
        },
        "user_type": "teacher", // or "guardian"
        "token": "sanctum_token_here",
        "token_type": "Bearer",
        "expires_at": "2026-03-05T10:30:00.000000Z",
        "permissions": ["view_classes", "take_attendance"],
        "roles": ["teacher"]
    }
}
```

### Unified Endpoints

#### Dashboard
```http
GET /api/v1/dashboard
GET /api/v1/dashboard/today
GET /api/v1/dashboard/stats
```

#### Notifications
```http
GET /api/v1/notifications
GET /api/v1/notifications/unread-count
POST /api/v1/notifications/{id}/read
POST /api/v1/notifications/mark-all-read
GET /api/v1/notifications/settings
PUT /api/v1/notifications/settings
```

#### Profile Management
```http
GET /api/v1/auth/profile
POST /api/v1/auth/change-password
POST /api/v1/auth/logout
```

#### Device Management
```http
POST /api/v1/device-tokens
DELETE /api/v1/device-tokens
```

### Role-Specific Endpoints

#### Teacher Endpoints
```http
GET /api/v1/teacher/today-classes
GET /api/v1/teacher/classes
POST /api/v1/teacher/attendance
POST /api/v1/teacher/homework
GET /api/v1/teacher/students/{id}/profile
```

#### Guardian Endpoints
```http
GET /api/v1/guardian/students
GET /api/v1/guardian/attendance
GET /api/v1/guardian/homework
POST /api/v1/guardian/leave-requests
GET /api/v1/guardian/fees
```

---

## Mobile App Implementation

### 1. App Architecture

```
src/
├── components/
│   ├── common/           # Shared components
│   ├── teacher/          # Teacher-specific components
│   └── guardian/         # Guardian-specific components
├── screens/
│   ├── auth/            # Login/auth screens
│   ├── teacher/         # Teacher screens
│   ├── guardian/        # Guardian screens
│   └── shared/          # Shared screens
├── services/
│   ├── api.js           # API service layer
│   ├── auth.js          # Authentication service
│   └── storage.js       # Local storage service
├── navigation/
│   ├── TeacherNavigator.js
│   ├── GuardianNavigator.js
│   └── AppNavigator.js
└── utils/
    ├── constants.js
    └── helpers.js
```

### 2. Authentication Service

```javascript
// services/auth.js
class AuthService {
    async login(credentials) {
        const response = await api.post('/auth/login', credentials);
        
        if (response.data.success) {
            const { user, user_type, token } = response.data.data;
            
            // Store authentication data
            await AsyncStorage.setItem('token', token);
            await AsyncStorage.setItem('user_type', user_type);
            await AsyncStorage.setItem('user', JSON.stringify(user));
            
            return { user, user_type, token };
        }
        
        throw new Error(response.data.message);
    }
    
    async getUserType() {
        return await AsyncStorage.getItem('user_type');
    }
    
    async isTeacher() {
        const userType = await this.getUserType();
        return userType === 'teacher';
    }
    
    async isGuardian() {
        const userType = await this.getUserType();
        return userType === 'guardian';
    }
}
```

### 3. API Service Layer

```javascript
// services/api.js
class ApiService {
    constructor() {
        this.baseURL = 'https://your-domain.com/api/v1';
        this.token = null;
        this.userType = null;
    }
    
    async initialize() {
        this.token = await AsyncStorage.getItem('token');
        this.userType = await AsyncStorage.getItem('user_type');
    }
    
    // Unified endpoints
    async getDashboard() {
        return this.get('/dashboard');
    }
    
    async getNotifications(page = 1) {
        return this.get(`/notifications?page=${page}`);
    }
    
    // Role-specific endpoints
    async getTodayData() {
        if (this.userType === 'teacher') {
            return this.get('/teacher/today-classes');
        } else if (this.userType === 'guardian') {
            return this.get('/guardian/today-schedule');
        }
    }
    
    async getClasses() {
        if (this.userType === 'teacher') {
            return this.get('/teacher/classes');
        } else if (this.userType === 'guardian') {
            return this.get('/guardian/students');
        }
    }
}
```

### 4. Navigation Structure

```javascript
// navigation/AppNavigator.js
import { useAuth } from '../contexts/AuthContext';

export default function AppNavigator() {
    const { user, userType, isAuthenticated } = useAuth();
    
    if (!isAuthenticated) {
        return <AuthNavigator />;
    }
    
    switch (userType) {
        case 'teacher':
            return <TeacherNavigator />;
        case 'guardian':
            return <GuardianNavigator />;
        default:
            return <AuthNavigator />;
    }
}
```

### 5. Shared Components

```javascript
// components/common/Dashboard.js
import { useAuth } from '../../contexts/AuthContext';

export default function Dashboard() {
    const { userType } = useAuth();
    const [dashboardData, setDashboardData] = useState(null);
    
    useEffect(() => {
        loadDashboard();
    }, []);
    
    const loadDashboard = async () => {
        try {
            const response = await apiService.getDashboard();
            setDashboardData(response.data);
        } catch (error) {
            console.error('Failed to load dashboard:', error);
        }
    };
    
    return (
        <View>
            {userType === 'teacher' ? (
                <TeacherDashboard data={dashboardData} />
            ) : (
                <GuardianDashboard data={dashboardData} />
            )}
        </View>
    );
}
```

---

## Backend Implementation Details

### 1. Controller Structure

The unified controllers delegate to role-specific controllers:

```php
// UnifiedAuthController.php
public function login(LoginRequest $request): JsonResponse
{
    // Determine user role and delegate to appropriate handler
    if ($user->hasRole('teacher')) {
        return $this->handleTeacherLogin($user, $request);
    } elseif ($user->hasRole('guardian')) {
        return $this->handleGuardianLogin($user, $request);
    }
}
```

### 2. Middleware Usage

```php
// Role-based access control
Route::middleware(['auth:sanctum', 'role_based:teacher,guardian'])->group(function () {
    Route::get('/dashboard', [UnifiedDashboardController::class, 'index']);
});

// Teacher-only routes
Route::middleware(['auth:sanctum', 'role_based:teacher'])->group(function () {
    Route::get('/teacher/classes', [TeacherClassController::class, 'index']);
});
```

### 3. Response Format

All endpoints follow consistent response format:

```php
// Success Response
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    }
}

// Error Response
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors if any
    }
}
```

---

## Testing with Postman

### 1. Import Collection
1. Import `UNIFIED_APP_POSTMAN_COLLECTION.json`
2. Set `base_url` variable to your API URL
3. Test login with teacher credentials
4. Test login with guardian credentials

### 2. Test Flow
1. **Login** → Token and user_type saved automatically
2. **Dashboard** → Returns role-appropriate data
3. **Notifications** → Works for both roles
4. **Role-specific endpoints** → Use teacher/ or guardian/ prefixes

### 3. Environment Variables
- `base_url`: Your API base URL
- `token`: Auto-set after login
- `user_type`: Auto-set after login (teacher/guardian)

---

## Security Considerations

### 1. Token Management
- Tokens expire based on role (teachers: 30 days, guardians: 7-30 days)
- Automatic token refresh on app launch
- Secure token storage using Keychain/Keystore

### 2. Role Validation
- Server-side role validation on every request
- Middleware prevents cross-role access
- Permission-based feature access

### 3. Data Protection
- Sensitive data encryption
- API rate limiting
- Input validation and sanitization

---

## Deployment Checklist

### Backend
- [ ] Deploy unified controllers
- [ ] Register new middleware
- [ ] Update API routes
- [ ] Test all endpoints
- [ ] Configure CORS for mobile app
- [ ] Set up push notifications
- [ ] Configure rate limiting

### Mobile App
- [ ] Implement role-based navigation
- [ ] Add unified authentication
- [ ] Create shared components
- [ ] Test on both iOS and Android
- [ ] Configure push notifications
- [ ] Add offline support
- [ ] Implement error handling

### Testing
- [ ] Test teacher login flow
- [ ] Test guardian login flow
- [ ] Test role-specific features
- [ ] Test shared features
- [ ] Test push notifications
- [ ] Test offline functionality
- [ ] Performance testing

---

## Support & Maintenance

### Monitoring
- API response times
- Error rates by endpoint
- User authentication success rates
- Push notification delivery rates

### Logging
- Authentication attempts
- API usage by role
- Error tracking
- Performance metrics

### Updates
- Backward compatibility for mobile apps
- Gradual feature rollouts
- A/B testing for new features
- Regular security updates

---

**Last Updated:** February 3, 2026
**Version:** 1.0.0