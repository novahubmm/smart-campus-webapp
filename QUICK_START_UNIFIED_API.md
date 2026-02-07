# Quick Start: Unified API for Mobile App

## SmartCampus v1.0.0 - smart-campus-webapp

---

## ✅ Setup Complete!

The unified API for both Teacher and Guardian mobile apps is now ready in the **smart-campus-webapp** directory.

---

## 🚀 Start the Server

```bash
cd smart-campus-webapp
./start-school-site.sh
```

The server will start on: `http://localhost:8088`

---

## 🧪 Test the API

### Quick Test with cURL

**Test Teacher Login:**
```bash
curl -X POST http://localhost:8088/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "teacher1@smartcampusedu.com",
    "password": "password",
    "device_name": "Test Device"
  }'
```

**Test Guardian Login:**
```bash
curl -X POST http://localhost:8088/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "guardian1@smartcampusedu.com",
    "password": "password",
    "device_name": "Test Device"
  }'
```

### Expected Response

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "...",
      "name": "...",
      "email": "..."
    },
    "user_type": "teacher",  // or "guardian"
    "token": "1|...",
    "token_type": "Bearer",
    "expires_at": "2026-03-07T...",
    "permissions": [...],
    "roles": ["teacher"]
  }
}
```

---

## 📱 Key Endpoints

### Authentication
- `POST /api/v1/auth/login` - Unified login (teacher & guardian)
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/profile` - Get profile
- `POST /api/v1/auth/change-password` - Change password

### Dashboard
- `GET /api/v1/dashboard` - Main dashboard (role-based)
- `GET /api/v1/dashboard/today` - Today's data
- `GET /api/v1/dashboard/stats` - Statistics

### Notifications
- `GET /api/v1/notifications` - List notifications
- `GET /api/v1/notifications/unread-count` - Unread count
- `POST /api/v1/notifications/{id}/read` - Mark as read

### Device Management
- `POST /api/v1/device-tokens` - Register FCM token
- `DELETE /api/v1/device-tokens` - Remove FCM token

---

## 📖 Documentation

- **Complete Guide:** `UNIFIED_API_SETUP_COMPLETE.md`
- **Implementation Details:** `UNIFIED_APP_IMPLEMENTATION_GUIDE.md`
- **Postman Collection:** `UNIFIED_APP_POSTMAN_COLLECTION.json`
- **Backend Guide:** `LARAVEL_BACKEND_IMPLEMENTATION.md`

---

## 🎯 Mobile App Integration

### 1. Login Request
```javascript
const response = await fetch('http://your-domain.com/api/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    login: email,
    password: password,
    device_name: 'iPhone 15',
    remember_me: true
  })
});

const data = await response.json();
```

### 2. Store Token & User Type
```javascript
if (data.success) {
  await AsyncStorage.setItem('token', data.data.token);
  await AsyncStorage.setItem('user_type', data.data.user_type);
  
  // Navigate based on user type
  if (data.data.user_type === 'teacher') {
    navigation.navigate('TeacherDashboard');
  } else {
    navigation.navigate('GuardianDashboard');
  }
}
```

### 3. Make Authenticated Requests
```javascript
const token = await AsyncStorage.getItem('token');

const response = await fetch('http://your-domain.com/api/v1/dashboard', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
```

---

## 🔧 Troubleshooting

### Clear Cache
```bash
cd smart-campus-webapp
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Check Routes
```bash
php artisan route:list --path=api/v1/auth
```

### Test Database Connection
```bash
php artisan tinker
>>> App\Models\User::count()
```

---

## ✨ What's Different from Separate APIs?

### Before (Separate APIs)
- Teacher: `POST /api/v1/teacher/auth/login`
- Guardian: `POST /api/v1/guardian/auth/login`
- Two separate mobile apps or complex routing

### Now (Unified API)
- Both: `POST /api/v1/auth/login`
- Single mobile app with role-based UI
- Response includes `user_type` for routing

---

## 🎉 You're Ready!

The unified API is fully functional and ready for mobile app development. Start building your React Native or Flutter app using the endpoints above!

**Need help?** Check the complete documentation in `UNIFIED_API_SETUP_COMPLETE.md`
