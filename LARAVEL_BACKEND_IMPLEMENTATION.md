# Laravel Backend Implementation Guide

## Overview

This guide provides step-by-step instructions for implementing the Parent Mobile App API endpoints in your Laravel backend.

## Prerequisites

- Laravel 10.x or higher
- Laravel Sanctum for API authentication
- MySQL/PostgreSQL database
- Firebase Cloud Messaging (FCM) for push notifications

---

## 1. Database Setup

### Migration Files

**Create Parent-Student Relationship:**
```bash
php artisan make:migration create_parent_student_table
```

```php
// database/migrations/xxxx_create_parent_student_table.php
public function up()
{
    Schema::create('parent_student', function (Blueprint $table) {
        $table->id();
        $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
        $table->string('relationship')->default('parent'); // parent, guardian
        $table->boolean('is_primary')->default(false);
        $table->timestamps();
        
        $table->unique(['parent_id', 'student_id']);
    });
}
```

**Create Device Tokens Table:**
```bash
php artisan make:migration create_device_tokens_table
```

```php
public function up()
{
    Schema::create('device_tokens', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('device_token')->unique();
        $table->enum('device_type', ['android', 'ios']);
        $table->string('device_name')->nullable();
        $table->string('app_version')->nullable();
        $table->timestamp('last_used_at')->nullable();
        $table->timestamps();
    });
}
```



**Create Notifications Table:**
```bash
php artisan make:migration create_notifications_table
```

```php
public function up()
{
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('body');
        $table->string('type')->default('general'); // homework, attendance, announcement, etc.
        $table->json('data')->nullable();
        $table->boolean('is_read')->default(false);
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
        
        $table->index(['user_id', 'is_read']);
    });
}
```

---

## 2. Models

### User Model

```php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 
        'avatar', 'nrc', 'address'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function children()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('relationship', 'is_primary')
            ->withTimestamps();
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Accessors
    public function getAvatarUrlAttribute()
    {
        return $this->avatar 
            ? asset('storage/' . $this->avatar)
            : asset('images/default-avatar.png');
    }
}
```

### Student Model

```php
// app/Models/Student.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name', 'roll_number', 'grade_id', 'class_id', 
        'section', 'date_of_birth', 'gender', 'blood_group',
        'admission_date', 'avatar', 'address', 'emergency_contact'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('relationship', 'is_primary')
            ->withTimestamps();
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function homework()
    {
        return $this->belongsToMany(Homework::class, 'homework_submissions')
            ->withPivot('status', 'submitted_date', 'grade', 'feedback')
            ->withTimestamps();
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar 
            ? asset('storage/' . $this->avatar)
            : asset('images/default-student.png');
    }
}
```

---

## 3. Controllers

### ParentDashboardController

```php
// app/Http/Controllers/Api/Parent/DashboardController.php
namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user();
        $children = $parent->children()
            ->with(['grade', 'class'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'children' => $children->map(function($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'grade' => $child->grade->name,
                        'class' => $child->class->name,
                        'avatar' => $child->avatar_url,
                        'roll_number' => $child->roll_number,
                    ];
                }),
                'quick_stats' => $this->getQuickStats($children),
                'recent_activities' => $this->getRecentActivities($children),
            ]
        ]);
    }

    private function getQuickStats($children)
    {
        $studentIds = $children->pluck('id');
        
        // Calculate attendance rate
        $attendanceRate = \DB::table('attendance_records')
            ->whereIn('student_id', $studentIds)
            ->whereMonth('date', Carbon::now()->month)
            ->avg(\DB::raw('CASE WHEN status = "present" THEN 100 ELSE 0 END'));

        // Count pending homework
        $pendingHomework = \DB::table('homework_submissions')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'pending')
            ->count();

        // Count upcoming exams
        $upcomingExams = \DB::table('exams')
            ->where('start_date', '>=', Carbon::now())
            ->where('start_date', '<=', Carbon::now()->addDays(30))
            ->count();

        // Count unread announcements
        $unreadAnnouncements = \DB::table('announcements')
            ->whereNotExists(function($query) use ($studentIds) {
                $query->select(\DB::raw(1))
                    ->from('announcement_reads')
                    ->whereColumn('announcement_reads.announcement_id', 'announcements.id')
                    ->whereIn('announcement_reads.student_id', $studentIds);
            })
            ->count();

        return [
            'attendance_rate' => round($attendanceRate, 1),
            'pending_homework' => $pendingHomework,
            'upcoming_exams' => $upcomingExams,
            'unread_announcements' => $unreadAnnouncements,
        ];
    }

    private function getRecentActivities($children)
    {
        // Implement based on your requirements
        return [];
    }
}
```



### StudentController

```php
// app/Http/Controllers/Api/Parent/StudentController.php
namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user();
        $students = $parent->children()->with(['grade', 'class'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'students' => $students->map(function($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'grade' => $student->grade->name,
                        'class' => $student->class->name,
                        'section' => $student->section,
                        'roll_number' => $student->roll_number,
                        'avatar' => $student->avatar_url,
                        'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
                        'gender' => $student->gender,
                        'blood_group' => $student->blood_group,
                        'admission_date' => $student->admission_date->format('Y-m-d'),
                    ];
                })
            ]
        ]);
    }

    public function show(Request $request, $studentId)
    {
        $student = Student::with(['grade', 'class', 'class.teacher'])->findOrFail($studentId);
        
        // Verify parent owns this student
        if (!$request->user()->children->contains($student)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'grade' => $student->grade->name,
                    'class' => $student->class->name,
                    'section' => $student->section,
                    'roll_number' => $student->roll_number,
                    'avatar' => $student->avatar_url,
                    'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
                    'gender' => $student->gender,
                    'blood_group' => $student->blood_group,
                    'admission_date' => $student->admission_date->format('Y-m-d'),
                    'address' => $student->address,
                    'emergency_contact' => $student->emergency_contact,
                    'class_teacher' => [
                        'id' => $student->class->teacher->id,
                        'name' => $student->class->teacher->name,
                        'phone' => $student->class->teacher->phone,
                        'email' => $student->class->teacher->email,
                    ]
                ]
            ]
        ]);
    }
}
```

---

## 4. Routes

```php
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Parent\DashboardController;
use App\Http\Controllers\Api\Parent\StudentController;
use App\Http\Controllers\Api\Parent\AttendanceController;
use App\Http\Controllers\Api\Parent\HomeworkController;
use App\Http\Controllers\Api\Parent\AnnouncementController;
use App\Http\Controllers\Api\Parent\LeaveRequestController;
use App\Http\Controllers\Api\Parent\FeeController;
use App\Http\Controllers\Api\Parent\NotificationController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    
    // Parent routes
    Route::prefix('parent')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        
        // Students
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/{student}', [StudentController::class, 'show']);
        
        // Attendance
        Route::get('/students/{student}/attendance', [AttendanceController::class, 'index']);
        
        // Grades & Exams
        Route::get('/students/{student}/grades', [GradeController::class, 'index']);
        Route::get('/students/{student}/exams', [ExamController::class, 'index']);
        
        // Homework
        Route::get('/students/{student}/homework', [HomeworkController::class, 'index']);
        Route::get('/students/{student}/homework/{homework}', [HomeworkController::class, 'show']);
        
        // Announcements
        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements/{announcement}/read', [AnnouncementController::class, 'markAsRead']);
        
        // Leave Requests
        Route::get('/students/{student}/leave-requests', [LeaveRequestController::class, 'index']);
        Route::post('/students/{student}/leave-requests', [LeaveRequestController::class, 'store']);
        Route::get('/students/{student}/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show']);
        
        // Fees
        Route::get('/students/{student}/fees', [FeeController::class, 'index']);
        Route::get('/students/{student}/payments', [FeeController::class, 'payments']);
        
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/device-token', [NotificationController::class, 'registerDeviceToken']);
        Route::delete('/device-token', [NotificationController::class, 'deleteDeviceToken']);
    });
});
```

---

## 5. Middleware

### Verify Parent Access

```php
// app/Http/Middleware/VerifyParentAccess.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyParentAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if ($user->role !== 'parent') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Parent access required.'
            ], 403);
        }

        // If route has student parameter, verify parent owns the student
        if ($request->route('student')) {
            $studentId = $request->route('student');
            if (!$user->children()->where('students.id', $studentId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have access to this student.'
                ], 403);
            }
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:
```php
protected $middlewareAliases = [
    // ...
    'parent' => \App\Http\Middleware\VerifyParentAccess::class,
];
```

Apply to routes:
```php
Route::prefix('parent')->middleware(['auth:sanctum', 'parent'])->group(function () {
    // routes...
});
```

---

## 6. Push Notifications Service

```php
// app/Services/PushNotificationService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\User;

class PushNotificationService
{
    protected $serverKey;

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key');
    }

    public function sendToUser($userId, $title, $body, $data = [])
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $deviceTokens = $user->deviceTokens()->pluck('device_token');

        foreach ($deviceTokens as $token) {
            $this->sendToDevice($token, $title, $body, $data);
        }

        // Save notification to database
        $user->notifications()->create([
            'title' => $title,
            'body' => $body,
            'type' => $data['type'] ?? 'general',
            'data' => json_encode($data),
            'is_read' => false,
        ]);

        return true;
    }

    public function sendToDevice($deviceToken, $title, $body, $data = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => 1,
            ],
            'data' => $data,
            'priority' => 'high',
        ]);

        return $response->successful();
    }
}
```



---

## 7. Configuration

### config/services.php

```php
return [
    // ...
    
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
    ],
];
```

### .env

```env
FCM_SERVER_KEY=your_firebase_server_key_here
```

---

## 8. Testing with Postman

### Step 1: Import Collection

1. Open Postman
2. Click "Import"
3. Select the `POSTMAN_COLLECTION.json` file
4. Collection will be imported with all endpoints

### Step 2: Set Variables

1. Click on the collection
2. Go to "Variables" tab
3. Set `base_url` to your API URL (e.g., `https://your-domain.com/api`)
4. `token` will be auto-set after login

### Step 3: Test Login

1. Open "Authentication" → "Login (Parent)"
2. Update the request body with valid credentials
3. Send request
4. Token will be automatically saved to collection variables

### Step 4: Test Other Endpoints

All other endpoints will use the saved token automatically.

---

## 9. Common Issues & Solutions

### Issue 1: CORS Errors

**Solution:** Add CORS middleware in Laravel

```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

### Issue 2: Token Not Working

**Solution:** Ensure Sanctum is properly configured

```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### Issue 3: Push Notifications Not Sending

**Solution:** Verify FCM server key

1. Go to Firebase Console
2. Project Settings → Cloud Messaging
3. Copy Server Key
4. Update `.env` file

---

## 10. Security Best Practices

### 1. Rate Limiting

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        'throttle:60,1', // 60 requests per minute
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

### 2. Input Validation

Always validate input:
```php
$validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|min:6',
]);
```

### 3. SQL Injection Prevention

Use Eloquent ORM or parameter binding:
```php
// Good
User::where('email', $email)->first();

// Bad
DB::select("SELECT * FROM users WHERE email = '$email'");
```

### 4. XSS Prevention

Sanitize output:
```php
return response()->json([
    'message' => e($userInput) // Escapes HTML
]);
```

---

## 11. Deployment Checklist

- [ ] Update `.env` with production values
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up SSL certificate (HTTPS)
- [ ] Configure Firebase with production credentials
- [ ] Set up proper backup system
- [ ] Configure logging and monitoring
- [ ] Test all API endpoints
- [ ] Set up rate limiting
- [ ] Configure CORS properly
- [ ] Set up queue workers for notifications
- [ ] Test push notifications
- [ ] Set up error tracking (Sentry, Bugsnag)

---

## 12. Support & Resources

### Laravel Documentation
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [API Resources](https://laravel.com/docs/eloquent-resources)
- [Validation](https://laravel.com/docs/validation)

### Firebase Documentation
- [FCM HTTP Protocol](https://firebase.google.com/docs/cloud-messaging/http-server-ref)
- [FCM Setup](https://firebase.google.com/docs/cloud-messaging/android/client)

### Mobile App Documentation
- See `PARENT_API_INTEGRATION_GUIDE.md` for mobile app integration
- See `ROLE_BASED_LOGIN_GUIDE.md` for role-based routing

---

**Last Updated:** February 3, 2026
**Version:** 1.0.0

