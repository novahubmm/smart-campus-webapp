# Unified API Setup Complete ✅

## SmartCampus v1.0.0 - Unified Teacher & Guardian Mobile App API

**Date:** February 7, 2026  
**Status:** ✅ Ready for Testing  
**Location:** `smart-campus-webapp` directory

---

## 🎯 What's Been Implemented

### 1. **Unified Authentication System**
- Single login endpoint that works for both teachers and guardians
- Automatic role detection and appropriate response
- Token-based authentication with role information

**Endpoint:** `POST /api/v1/auth/login`

**Features:**
- ✅ Accepts email, phone, or NRC as login identifier
- ✅ Returns `user_type` (teacher/guardian) for app routing
- ✅ Includes user permissions and roles
- ✅ Token expiration management

### 2. **Unified Dashboard**
- Role-based dashboard that delegates to appropriate controllers
- Single endpoint returns different data based on user role

**Endpoints:**
- `GET /api/v1/dashboard` - Main dashboard
- `GET /api/v1/dashboard/today` - Today's classes/schedule
- `GET /api/v1/dashboard/stats` - Quick statistics

### 3. **Unified Notifications**
- Shared notification system for both roles
- Consistent API across teacher and guardian accounts

**Endpoints:**
- `GET /api/v1/notifications` - List notifications
- `GET /api/v1/notifications/unread-count` - Unread count
- `POST /api/v1/notifications/{id}/read` - Mark as read
- `POST /api/v1/notifications/mark-all-read` - Mark all as read
- `GET /api/v1/notifications/settings` - Get settings
- `PUT /api/v1/notifications/settings` - Update settings

### 4. **Profile Management**
- Unified profile endpoints
- Change password functionality

**Endpoints:**
- `GET /api/v1/auth/profile` - Get user profile
- `POST /api/v1/auth/change-password` - Change password
- `POST /api/v1/auth/logout` - Logout

### 5. **Device Management**
- FCM token registration for push notifications

**Endpoints:**
- `POST /api/v1/device-tokens` - Register device
- `DELETE /api/v1/device-tokens` - Remove device

---

## 📁 Files Created

### Controllers
```
smart-campus-webapp/app/Http/Controllers/Api/V1/
├── UnifiedAuthController.php
├── UnifiedDashboardController.php
└── UnifiedNotificationController.php
```

### Middleware
```
smart-campus-webapp/app/Http/Middleware/
└── RoleBasedAccess.php
```

### Configuration
```
smart-campus-webapp/
├── .env
├── .env.example
└── bootstrap/app.php (updated)
```

### Routes
```
smart-campus-webapp/routes/
└── api.php (updated with unified routes)
```

### Documentation
```
smart-campus-webapp/
├── UNIFIED_APP_IMPLEMENTATION_GUIDE.md
├── UNIFIED_APP_POSTMAN_COLLECTION.json
├── UNIFIED_API_SETUP_COMPLETE.md (this file)
└── test-unified-api.sh
```

---

## 🚀 How to Test

### Option 1: Using the Test Script

```bash
cd smart-campus-webapp

# Make sure the server is running
./start-school-site.sh

# In another terminal, run the test script
./test-unified-api.sh
```

### Option 2: Using Postman

1. Import `UNIFIED_APP_POSTMAN_COLLECTION.json` into Postman
2. Set the `base_url` variable to `http://localhost:8088/api/v1`
3. Test the "Unified Login" request with:
   - Teacher: `teacher1@smartcampusedu.com` / `password`
   - Guardian: `guardian1@smartcampusedu.com` / `password`

### Option 3: Using cURL

**Teacher Login:**
```bash
curl -X POST http://localhost:8088/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "teacher1@smartcampusedu.com",
    "password": "password",
    "device_name": "iPhone 15"
  }'
```

**Guardian Login:**
```bash
curl -X POST http://localhost:8088/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "guardian1@smartcampusedu.com",
    "password": "password",
    "device_name": "iPhone 15"
  }'
```

---

## 📱 Mobile App Integration

### Login Flow

```javascript
// 1. User enters credentials
const credentials = {
  login: email,
  password: password,
  device_name: deviceInfo.name,
  remember_me: rememberMe
};

// 2. Call unified login endpoint
const response = await api.post('/auth/login', credentials);

// 3. Check user type and route accordingly
if (response.data.success) {
  const { user, user_type, token } = response.data.data;
  
  // Store authentication data
  await AsyncStorage.setItem('token', token);
  await AsyncStorage.setItem('user_type', user_type);
  await AsyncStorage.setItem('user', JSON.stringify(user));
  
  // Navigate based on user type
  if (user_type === 'teacher') {
    navigation.navigate('TeacherDashboard');
  } else if (user_type === 'guardian') {
    navigation.navigate('GuardianDashboard');
  }
}
```

### API Service Setup

```javascript
class ApiService {
  constructor() {
    this.baseURL = 'https://your-domain.com/api/v1';
  }
  
  async initialize() {
    this.token = await AsyncStorage.getItem('token');
    this.userType = await AsyncStorage.getItem('user_type');
  }
  
  // Unified endpoints
  async getDashboard() {
    return this.get('/dashboard');
  }
  
  async getNotifications() {
    return this.get('/notifications');
  }
  
  // Role-specific endpoints
  async getTodayData() {
    if (this.userType === 'teacher') {
      return this.get('/teacher/today-classes');
    } else {
      return this.get('/guardian/today-schedule');
    }
  }
}
```

---

## 🔐 Security Features

### 1. Role-Based Access Control
- Middleware validates user roles on every request
- Prevents cross-role access
- Permission-based feature access

### 2. Token Management
- Sanctum-based authentication
- Configurable token expiration
- Secure token storage required on mobile

### 3. Input Validation
- All inputs validated using Laravel Form Requests
- SQL injection prevention via Eloquent ORM
- XSS protection on outputs

---

## 📊 API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    "user": { ... },
    "user_type": "teacher",
    "token": "...",
    "token_type": "Bearer",
    "expires_at": "2026-03-07T10:30:00.000000Z",
    "permissions": [...],
    "roles": [...]
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

---

## 🎨 Available Endpoints

### Unified Endpoints (Work for both roles)
```
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/profile
POST   /api/v1/auth/change-password

GET    /api/v1/dashboard
GET    /api/v1/dashboard/today
GET    /api/v1/dashboard/stats

GET    /api/v1/notifications
GET    /api/v1/notifications/unread-count
POST   /api/v1/notifications/{id}/read
POST   /api/v1/notifications/mark-all-read
GET    /api/v1/notifications/settings
PUT    /api/v1/notifications/settings

POST   /api/v1/device-tokens
DELETE /api/v1/device-tokens
```

### Teacher-Specific Endpoints
```
GET    /api/v1/teacher/today-classes
GET    /api/v1/teacher/classes
POST   /api/v1/teacher/attendance
POST   /api/v1/teacher/homework
... (see full list in api.php)
```

### Guardian-Specific Endpoints
```
GET    /api/v1/guardian/students
GET    /api/v1/guardian/attendance
GET    /api/v1/guardian/homework
POST   /api/v1/guardian/leave-requests
GET    /api/v1/guardian/fees
... (see full list in api.php)
```

---

## 🔧 Troubleshooting

### Issue: Route not found
**Solution:**
```bash
cd smart-campus-webapp
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Issue: Authentication fails
**Solution:**
- Check database has users with correct roles
- Verify password is correct (default: `password`)
- Check token is being sent in Authorization header

### Issue: CORS errors
**Solution:**
- Update `config/cors.php` to allow your mobile app domain
- Ensure `Accept: application/json` header is sent

---

## 📚 Additional Resources

1. **Implementation Guide:** `UNIFIED_APP_IMPLEMENTATION_GUIDE.md`
2. **Postman Collection:** `UNIFIED_APP_POSTMAN_COLLECTION.json`
3. **Laravel Backend Guide:** `LARAVEL_BACKEND_IMPLEMENTATION.md`
4. **API Documentation:** `API_DOCUMENTATION.md`

---

## ✅ Next Steps

1. **Test the API** using Postman or the test script
2. **Integrate with mobile app** using the provided examples
3. **Configure Firebase** for push notifications
4. **Set up production environment** with proper SSL
5. **Deploy to production** server

---

## 🎉 Summary

You now have a fully functional unified API that supports both teacher and guardian mobile apps with:

- ✅ Single login endpoint with role detection
- ✅ Role-based dashboard and features
- ✅ Unified notification system
- ✅ Secure authentication with Sanctum
- ✅ Complete documentation and examples
- ✅ Postman collection for testing
- ✅ Mobile app integration guide

The API is ready for mobile app development! 🚀

---

**Questions or Issues?**
Refer to the implementation guide or check the existing API documentation for more details.
