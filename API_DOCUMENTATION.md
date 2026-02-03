# Nova Hub API Documentation for Flutter App

> Legacy notice: This document reflects the starter's sample endpoints (auth, students, positions/applications). It will be replaced with school-management APIs (academic data, users, schedules, attendance, fees/payments, events, announcements) as those modules are implemented.  
> Current active endpoints: `/api/v1/login`, `/api/v1/logout`, `/api/v1/profile` (no self-registration; admins create users).

Base URL: `http://127.0.0.1:8000/api` (Development)  
Production: `https://yourdomain.com/api`

## 📋 Table of Contents
- [Authentication](#authentication)
- [Students](#students)
- [Positions](#positions)
- [Applications](#applications)
- [Career Applications (Public)](#career-applications)
- [Push Notifications](#push-notifications)
- [Response Format](#response-format)
- [Error Handling](#error-handling)

---

## 🔐 Authentication

### Register
**POST** `/v1/register`

**Headers:** `Content-Type: application/json`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "roles": ["user"]
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  }
}
```

---

### Login
**POST** `/v1/login`

**Headers:** `Content-Type: application/json`

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "roles": ["admin"],
      "permissions": ["view students", "create students", ...]
    },
    "token": "2|xyz123..."
  }
}
```

---

### Logout
**POST** `/v1/logout`

**Headers:**
- `Content-Type: application/json`
- `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Get Profile
**GET** `/v1/profile`

**Headers:**
- `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "roles": ["admin"],
    "permissions": ["view students", "create students"]
  }
}
```

---

## 👨‍🎓 Students

### List Students
**GET** `/v1/students`

**Headers:**
- `Authorization: Bearer {token}`

**Query Parameters:**
- `search` (optional): Search by name, email, phone
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15)

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "date_of_birth": "2000-01-01",
      "address": "123 Main St",
      "created_at": "2024-11-01T12:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 68
  }
}
```

---

### Create Student
**POST** `/v1/students`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+1234567890",
  "date_of_birth": "2001-05-15",
  "address": "456 Oak Ave"
}
```

**Response:** `201 Created`

---

### Get Student
**GET** `/v1/students/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Response:** `200 OK`

---

### Update Student
**PUT** `/v1/students/{id}`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:** (all fields optional)
```json
{
  "name": "Jane Doe",
  "phone": "+0987654321"
}
```

**Response:** `200 OK`

---

### Delete Student
**DELETE** `/v1/students/{id}`

**Headers:**
- `Authorization: Bearer {token}`

**Response:** `200 OK`

---

## 💼 Positions

### List Positions
**GET** `/v1/positions`

**Headers:**
- `Authorization: Bearer {token}`

**Query Parameters:**
- `search` (optional): Search by title, description
- `status` (optional): Filter by status (open/closed)
- `page`, `per_page`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Senior Flutter Developer",
      "description": "We are looking for...",
      "requirements": "3+ years experience...",
      "location": "Remote",
      "type": "full-time",
      "salary_range": "$80k - $120k",
      "status": "open",
      "created_at": "2024-11-01T12:00:00Z"
    }
  ],
  "meta": { ... }
}
```

---

### Create Position
**POST** `/v1/positions`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "title": "Flutter Developer",
  "description": "Job description...",
  "requirements": "Requirements...",
  "location": "Remote",
  "type": "full-time",
  "salary_range": "$70k - $100k",
  "status": "open"
}
```

**Response:** `201 Created`

---

## 📝 Applications

### List Applications (Admin)
**GET** `/v1/applications`

**Headers:**
- `Authorization: Bearer {token}`

**Query Parameters:**
- `status` (optional): pending/reviewed/accepted/rejected
- `position_id` (optional)
- `student_id` (optional)
- `search` (optional)

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "position": {
        "id": 2,
        "title": "Flutter Developer"
      },
      "status": "pending",
      "notes": "Looking forward to this opportunity",
      "created_at": "2024-11-14T10:30:00Z",
      "created_at_human": "2 hours ago"
    }
  ],
  "meta": { ... }
}
```

---

### Get My Applications
**GET** `/v1/applications/my-applications`

**Headers:**
- `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "position": {
        "id": 2,
        "title": "Flutter Developer",
        "location": "Remote"
      },
      "status": "pending",
      "notes": "...",
      "created_at": "2024-11-14T10:30:00Z"
    }
  ]
}
```

---

### Submit Application
**POST** `/v1/applications/apply`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "student_id": 5,
  "position_id": 2,
  "notes": "I am very interested in this position..."
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Application submitted successfully",
  "data": { ... }
}
```

**Error Response:** `409 Conflict` (if already applied)
```json
{
  "success": false,
  "message": "You have already applied for this position"
}
```

---

### Update Application Status (Admin)
**PATCH** `/v1/applications/{id}/status`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "status": "accepted",
  "notes": "Congratulations! We'd like to schedule an interview."
}
```

**Response:** `200 OK`

---

## 🌐 Career Applications (Public - No Auth Required)

### List Open Positions
**GET** `/careers`

**Headers:** None required

**Query Parameters:**
- `search` (optional)
- `type` (optional): full-time/part-time/contract
- `page`, `per_page`

**Response:** `200 OK`

---

### Submit Career Application (Guest)
**POST** `/careers/apply`

**Headers:**
- `Content-Type: multipart/form-data`

**Request Body (Form Data):**
```
full_name: John Doe
email: john@example.com
phone: +1234567890
position: Flutter Developer
cover_letter: I am applying for...
cv: [FILE] (PDF, DOC, DOCX - max 5MB)
linkedin: https://linkedin.com/in/johndoe (optional)
portfolio: https://johndoe.com (optional)
years_of_experience: 5 (optional)
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Your application has been submitted successfully. We will contact you soon!",
  "data": {
    "id": 10,
    "full_name": "John Doe",
    "email": "john@example.com",
    "position": "Flutter Developer",
    "status": "pending",
    "cv_url": "http://127.0.0.1:8000/storage/cvs/1699999999_resume.pdf"
  }
}
```

---

### Check Application Status
**GET** `/careers/status/{id}?email=john@example.com`

**Query Parameters:**
- `email` (required): Email used in application

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 10,
    "full_name": "John Doe",
    "position": "Flutter Developer",
    "status": "pending",
    "created_at": "2024-11-14T10:30:00Z"
  }
}
```

---

## 🔔 Push Notifications

### Get VAPID Public Key
**GET** `/push/public-key`

**Headers:** None required

**Response:** `200 OK`
```json
{
  "success": true,
  "public_key": "BKzOMopPX7cOqF-PI_RTYKo5NDRlCtIbLL6Uo9SjgcLh..."
}
```

---

### Subscribe to Push Notifications
**POST** `/push/subscribe`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "endpoint": "https://fcm.googleapis.com/fcm/send/...",
  "keys": {
    "p256dh": "public_key_here",
    "auth": "auth_secret_here"
  }
}
```

**Response:** `200 OK`

---

### Unsubscribe
**POST** `/push/unsubscribe`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "endpoint": "https://fcm.googleapis.com/fcm/send/..."
}
```

**Response:** `200 OK`

---

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "meta": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail"]
  }
}
```

---

## ❌ Error Handling

### HTTP Status Codes
- `200 OK`: Success
- `201 Created`: Resource created
- `400 Bad Request`: Invalid input
- `401 Unauthorized`: Missing or invalid token
- `403 Forbidden`: No permission
- `404 Not Found`: Resource not found
- `409 Conflict`: Duplicate entry
- `422 Unprocessable Entity`: Validation failed
- `500 Internal Server Error`: Server error

### Common Errors

**Authentication Error:**
```json
{
  "message": "Unauthenticated."
}
```

**Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

**Permission Error:**
```json
{
  "message": "This action is unauthorized."
}
```

---

## 🔒 Security

### Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz
```

### Token Management
- Tokens are created on login/register
- Store securely in Flutter using `flutter_secure_storage`
- Include in all authenticated requests
- Invalidate on logout

### CORS
CORS is enabled for the following:
- Origins: `*` (configure for production)
- Methods: `GET, POST, PUT, PATCH, DELETE, OPTIONS`
- Headers: `Content-Type, Authorization, Accept`

---

## 📱 Flutter Integration Example

```dart
// DioClient with auto token injection
final dio = Dio(BaseOptions(
  baseUrl: 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
));

// Login
final response = await dio.post('/v1/login', data: {
  'email': 'admin@example.com',
  'password': 'password',
});

final token = response.data['data']['token'];

// Save token
await storage.write(key: 'auth_token', value: token);

// Authenticated request
dio.options.headers['Authorization'] = 'Bearer $token';
final students = await dio.get('/v1/students');

// Upload file
final formData = FormData.fromMap({
  'full_name': 'John Doe',
  'email': 'john@example.com',
  'cv': await MultipartFile.fromFile(cvPath),
});
await dio.post('/careers/apply', data: formData);
```

---

## 🧪 Testing

### Postman Collection
Import the provided Postman collection for easy API testing.

### Test Credentials
- **Admin**: admin@example.com / password
- **User**: user@example.com / password

---

**Built with ❤️ by Nova Hub Team**
