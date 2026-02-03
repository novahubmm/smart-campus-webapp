# FCM Testing Guide

This guide helps you test Firebase Cloud Messaging (FCM) notifications for Smart Campus.

## 🚀 Quick Start

### 1. Run the FCM Test Seeder
```bash
php artisan db:seed --class=FcmTestAnnouncementSeeder
```

This will:
- Create test users (staff and teachers) with mock FCM tokens
- Create 5 different test announcements
- Send FCM notifications immediately
- Create database notifications

### 2. Use the FCM Test Command

#### Test staff notifications:
```bash
php artisan fcm:test --type=staff --priority=high --count=1
```

#### Test teacher notifications:
```bash
php artisan fcm:test --type=teacher --priority=urgent --count=1
```

#### Test all users:
```bash
php artisan fcm:test --type=all --priority=medium --count=3
```

#### Available options:
- `--type`: `staff`, `teacher`, or `all`
- `--priority`: `low`, `medium`, `high`, or `urgent`
- `--count`: Number of test notifications (1-10)

## 📱 Testing Real FCM Notifications

### Prerequisites:
1. **Firebase Web App Config**: Update the real Firebase config in:
   - `public/firebase-messaging-sw.js`
   - `resources/js/firebase.js`

2. **Build Assets**: 
   ```bash
   npm run build
   ```

### Testing Steps:

1. **Open browser as staff user**
2. **Check browser console** for FCM token registration
3. **Run test command**:
   ```bash
   php artisan fcm:test --type=staff --priority=high
   ```
4. **Check for notifications**:
   - Browser push notification should appear
   - Notification badge should update
   - Database notification should be created

## 🔍 Debugging

### Check FCM Service Status:
```bash
php artisan tinker
$service = new \App\Services\FirebaseService();
// Should not throw errors
```

### Check User FCM Tokens:
```bash
php artisan tinker
\App\Models\User::role('staff')->whereNotNull('fcm_token')->count();
```

### Check Recent Notifications:
```bash
php artisan tinker
\App\Models\Notification::latest()->take(5)->get(['id', 'data', 'created_at']);
```

### View Logs:
```bash
tail -f storage/logs/laravel.log | grep FCM
```

## 📊 Test Scenarios

### Scenario 1: Staff Meeting Notification
```bash
php artisan fcm:test --type=staff --priority=medium --count=1
```

### Scenario 2: Urgent Teacher Alert
```bash
php artisan fcm:test --type=teacher --priority=urgent --count=1
```

### Scenario 3: School-wide Announcement
```bash
php artisan fcm:test --type=all --priority=high --count=1
```

### Scenario 4: Multiple Notifications
```bash
php artisan fcm:test --type=all --priority=medium --count=5
```

## 🛠️ Troubleshooting

### No FCM Tokens Found
- Users need to visit the site to register FCM tokens
- The test command will add mock tokens automatically
- Real tokens are generated when users grant notification permission

### FCM Service Errors
- Check `storage/app/firebase-credentials.json` exists
- Verify Firebase project ID in `.env`
- Check Firebase Console for Cloud Messaging setup

### No Push Notifications
- Update Firebase web app config with real values
- Check browser notification permissions
- Verify service worker registration

### Database Notifications Not Showing
- Check notification count API: `/staff/notifications/unread-count`
- Verify user roles and permissions
- Check notification table in database

## 📈 Performance Testing

### Test with Multiple Users:
```bash
# Create more test users first
php artisan tinker
for($i=1; $i<=20; $i++) {
    $user = \App\Models\User::factory()->create(['name' => "Test Staff $i"]);
    $user->assignRole('staff');
    $user->update(['fcm_token' => 'mock_token_' . $i]);
}

# Then test with many users
php artisan fcm:test --type=staff --count=1
```

### Monitor Server Resources:
```bash
# Check memory usage
free -h

# Check CPU usage
top

# Check Laravel logs
tail -f storage/logs/laravel.log
```

## ✅ Success Indicators

When FCM is working correctly, you should see:

1. **Browser Console**: FCM token registration messages
2. **Push Notifications**: Real-time browser notifications
3. **Badge Updates**: Notification count updates automatically
4. **Database Records**: New notifications in the database
5. **Logs**: Successful FCM send messages in Laravel logs

## 🎯 Next Steps

After successful testing:

1. **Update Firebase Config** with real values
2. **Deploy to production** server
3. **Test with real users**
4. **Monitor FCM delivery rates**
5. **Set up error alerting**