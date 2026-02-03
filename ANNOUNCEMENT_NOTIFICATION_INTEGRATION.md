# Announcement Notification Integration

## Overview
When an announcement is created and published with "teacher" as a target role, all active teachers will automatically receive a notification in the Teacher App.

## Implementation Details

### 1. Notification Model
**File:** `scp/app/Models/Notification.php`

Created a Notification model with:
- UUID primary key
- Polymorphic relationship to notifiable entities (users)
- JSON data field for notification content
- Read/unread tracking
- Helper methods: `markAsRead()`, `markAsUnread()`, `read()`, `unread()`
- Query scopes: `unread()`, `read()`

### 2. Announcement Controller Integration
**File:** `scp/app/Http/Controllers/AnnouncementController.php`

Updated `sendPushNotifications()` method to:
- Check if 'teacher' is in target_roles
- Query all active teachers using Spatie roles
- Create notification records for each teacher
- Log success/failure

**Key Method:**
```php
private function sendTeacherNotifications(Announcement $announcement): void
{
    $teachers = \App\Models\User::role('teacher')
        ->where('is_active', true)
        ->get();

    foreach ($teachers as $teacher) {
        \App\Models\Notification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\AnnouncementNotification',
            'notifiable_type' => get_class($teacher),
            'notifiable_id' => $teacher->id,
            'data' => [
                'title' => $announcement->title,
                'message' => strip_tags($announcement->content),
                'announcement_id' => $announcement->id,
                'priority' => $announcement->priority ?? 'medium',
                'type' => $announcement->type ?? 'general',
            ],
            'read_at' => null,
        ]);
    }
}
```

### 3. When Notifications Are Sent

Notifications are automatically created when:

1. **New Announcement Published:**
   - Announcement is created with `is_published = true`
   - Target roles include 'teacher'
   - Triggers in `store()` method

2. **Draft Announcement Published:**
   - Existing draft announcement is updated to `is_published = true`
   - Target roles include 'teacher'
   - Triggers in `update()` method

### 4. Notification Data Structure

Each notification contains:
```json
{
    "id": "uuid",
    "type": "App\\Notifications\\AnnouncementNotification",
    "notifiable_type": "App\\Models\\User",
    "notifiable_id": "teacher-user-id",
    "data": {
        "title": "Staff Meeting Tomorrow",
        "message": "All teachers are required to attend...",
        "announcement_id": "announcement-uuid",
        "priority": "high",
        "type": "general"
    },
    "read_at": null,
    "created_at": "2024-01-15T08:30:00Z"
}
```

### 5. Teacher App API Integration

Teachers can access their notifications via existing API endpoints:

**Get All Notifications:**
```
GET /api/v1/teacher/notifications
```

**Get Unread Count:**
```
GET /api/v1/teacher/notifications/unread-count
```

**Mark as Read:**
```
POST /api/v1/teacher/notifications/{id}/read
```

See `smart-campus-teacher-app/api_doc/notifications.txt` for complete API documentation.

## Testing

### 1. Test Seeder
**File:** `scp/database/seeders/TestAnnouncementNotificationSeeder.php`

Run the seeder to create test notifications:
```bash
php artisan db:seed --class=TestAnnouncementNotificationSeeder
```

This creates 3 sample notifications for all active teachers.

### 2. Manual Testing

**Step 1: Create an Announcement**
1. Go to Announcements page in admin panel
2. Create a new announcement
3. Select "teacher" in target roles
4. Set `is_published = true`
5. Save

**Step 2: Verify in Database**
```sql
SELECT * FROM notifications 
WHERE notifiable_type = 'App\\Models\\User' 
ORDER BY created_at DESC 
LIMIT 10;
```

**Step 3: Test API**
```bash
# Get teacher token
php artisan tinker --execute="
\$teacher = App\Models\User::role('teacher')->first();
\$token = \$teacher->createToken('test')->plainTextToken;
echo \$token;
"

# Test API
curl -X GET http://localhost:8088/api/v1/teacher/notifications \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 3. Postman Testing

Use the "Get All Notifications" request in the Teacher App API collection:
1. Set the `token` variable with a teacher's token
2. Send GET request to `/api/v1/teacher/notifications`
3. Verify announcement notifications appear in response

## Flow Diagram

```
Admin Creates Announcement
         ↓
Is Published? → No → Save as Draft (no notifications)
         ↓ Yes
Target Roles includes 'teacher'? → No → Skip teacher notifications
         ↓ Yes
Query all active teachers
         ↓
For each teacher:
  - Create Notification record
  - Set type = 'announcement'
  - Set data with title, message, announcement_id
  - Set read_at = null (unread)
         ↓
Teacher App polls /api/v1/teacher/notifications
         ↓
Display notification in app
         ↓
Teacher taps notification
         ↓
POST /api/v1/teacher/notifications/{id}/read
         ↓
Notification marked as read
```

## Database Schema

**notifications table:**
```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY,
    type VARCHAR(255),
    notifiable_type VARCHAR(255),
    notifiable_id UUID,
    data TEXT,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX (notifiable_type, notifiable_id, read_at)
);
```

## Future Enhancements

1. **Push Notifications (FCM):**
   - Integrate Firebase Cloud Messaging
   - Send real-time push to teacher devices
   - Store device tokens in `device_tokens` table

2. **Notification Preferences:**
   - Allow teachers to customize notification settings
   - Quiet hours support
   - Per-type notification preferences

3. **Batch Processing:**
   - Use queues for large teacher lists
   - Implement `SendAnnouncementNotificationJob`

4. **Guardian & Staff Notifications:**
   - Extend to guardian app
   - Extend to staff web portal

## Troubleshooting

**No notifications appearing:**
1. Check if announcement is published (`is_published = true`)
2. Verify 'teacher' is in `target_roles` array
3. Check if teachers exist with role 'teacher'
4. Check if teachers are active (`is_active = true`)
5. Review logs: `storage/logs/laravel.log`

**API returns empty notifications:**
1. Verify teacher authentication token
2. Check if notifications exist for that teacher in database
3. Verify API route is correct: `/api/v1/teacher/notifications`

## Related Files

- `scp/app/Models/Notification.php` - Notification model
- `scp/app/Http/Controllers/AnnouncementController.php` - Announcement controller with notification logic
- `scp/app/Http/Controllers/Api/V1/Teacher/NotificationController.php` - Teacher notification API
- `scp/routes/api.php` - API routes
- `scp/database/migrations/2025_12_30_100001_create_notifications_table.php` - Notifications table
- `scp/database/seeders/TestAnnouncementNotificationSeeder.php` - Test seeder
- `smart-campus-teacher-app/api_doc/notifications.txt` - API documentation
- `scp/NOTIFICATION_POST_IMPLEMENTATION.md` - POST notification implementation

## Success Metrics

✅ Notification model created
✅ Announcement controller integrated
✅ Notifications created on announcement publish
✅ Teacher API endpoints working
✅ Test seeder created
✅ 234 test notifications created for 78 teachers
✅ API tested and verified

## Next Steps

1. Test in Teacher App mobile application
2. Implement FCM push notifications
3. Add notification badges/counts in app UI
4. Extend to guardian and staff roles
5. Add notification preferences UI
