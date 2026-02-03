# Push Notifications for Admin

Nova Hub now supports **Web Push Notifications** to notify administrators instantly when new applications are submitted!

## ✅ Features Implemented

### **1. Automatic Notifications**

-   Admins automatically receive push notifications when new applications are submitted
-   Notifications work even when browser is in background
-   Notifications persist until dismissed

### **2. Smart Targeting**

-   Only users with `admin` role or `manage settings` permission receive notifications
-   Auto-subscribes eligible users on login
-   Works across multiple devices

### **3. Rich Notifications**

-   Displays student name and position title
-   Click to open application details
-   Action buttons: "Open" and "Dismiss"
-   Custom icon and badge

### **4. Subscription Management**

-   Automatic subscription for admins
-   Stores subscriptions per user per device
-   Removes expired/invalid subscriptions automatically

## 📁 Files Created

### Backend

-   `database/migrations/2025_11_13_192002_create_push_subscriptions_table.php` - Subscriptions storage
-   `app/Models/PushSubscription.php` - Subscription model
-   `app/Http/Controllers/PushNotificationController.php` - API endpoints
-   `app/Services/PushNotificationService.php` - Notification logic
-   `config/webpush.php` - VAPID configuration

### Frontend

-   `public/js/push-notifications.js` - Push notification manager
-   Updated `public/service-worker.js` - Handle push events
-   Updated `resources/views/layouts/app.blade.php` - Meta tags & scripts

### Configuration

-   `.env` - Added VAPID keys
-   `routes/api.php` - Push notification API routes

## 🔐 VAPID Keys

VAPID keys have been generated and added to your `.env` file:

```env
VAPID_PUBLIC_KEY=BKzOMopPX7cOqF-PI_RTYKo5NDRlCtIbLL6Uo9SjgcLhfM_mSIDxP4u9enevrNIjFzyYW-961SvOji_cYzx6gns
VAPID_PRIVATE_KEY=G2zPimfoaqQBmN2rseoAopQOe5R8X6snLnQeFcaXDLY
```

⚠️ **Important**: These keys are unique to your application. Keep the private key secret!

## 🚀 How It Works

### **Flow:**

1. **User Logs In as Admin**

    - System detects admin role/permission
    - Requests notification permission
    - Subscribes to push notifications

2. **Student Submits Application**

    - Application saved to database
    - `Application::created` event fires
    - `PushNotificationService` sends notifications to all subscribed admins

3. **Admin Receives Notification**
    - Browser shows notification (even if tab is closed)
    - Notification displays: "New application from {student} for {position}"
    - Click "Open" → Navigates to application details
    - Click "Dismiss" → Closes notification

## 🧪 Testing

### **1. Subscribe to Notifications**

Login as admin user, then open browser console:

```javascript
// Check subscription status
await pushManager.checkSubscription();

// Request permission and subscribe
await pushManager.requestPermission();

// Send test notification
await pushManager.sendTest();
```

### **2. Test via API**

```bash
# Get public key
curl http://127.0.0.1:8000/api/push/public-key

# Send test notification (requires authentication)
curl -X POST http://127.0.0.1:8000/api/push/test \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json"
```

### **3. Test Real Scenario**

1. Login as admin user
2. Grant notification permission when prompted
3. Create a new application (as student or via API)
4. You should receive a push notification immediately!

## 📱 Browser Support

✅ **Desktop:**

-   Chrome 42+ (Full support)
-   Firefox 44+ (Full support)
-   Edge 17+ (Full support)
-   Safari 16+ (macOS Ventura+)

✅ **Mobile:**

-   Chrome for Android (Full support)
-   Firefox for Android (Full support)
-   Samsung Internet 4+ (Full support)
-   Safari iOS 16.4+ (Limited - requires Add to Home Screen)

❌ **No Support:**

-   IE (all versions)
-   Safari < 16 (macOS/iOS)

## 🔧 API Endpoints

### `GET /api/push/public-key`

Get VAPID public key for subscription

-   **Auth**: None
-   **Response**: `{ publicKey: "..." }`

### `POST /api/push/subscribe`

Subscribe to push notifications

-   **Auth**: Required
-   **Body**: `{ endpoint, keys: { p256dh, auth } }`
-   **Response**: `{ success: true, message: "..." }`

### `POST /api/push/unsubscribe`

Unsubscribe from push notifications

-   **Auth**: Required
-   **Body**: `{ endpoint }`
-   **Response**: `{ success: true, message: "..." }`

### `POST /api/push/test`

Send test notification (admin only)

-   **Auth**: Required (`manage settings` permission)
-   **Response**: `{ success: true, message: "...", results: [...] }`

## 🎨 Customization

### **Change Notification Content**

Edit `app/Services/PushNotificationService.php`:

```php
public function notifyNewApplication($application): array
{
    $title = 'Your Custom Title';
    $body = 'Your custom message';
    $url = route('your.route');

    // ...
}
```

### **Add More Notification Types**

```php
// In PushNotificationService.php
public function notifyStatusChange($application): array
{
    $title = 'Application Status Updated';
    $body = "Application #{$application->id} status changed to {$application->status}";
    return $this->sendToAdmins($title, $body);
}
```

### **Customize Notification Actions**

Edit `public/service-worker.js`:

```javascript
actions: [
    { action: "open", title: "View Now" },
    { action: "later", title: "Remind Me" },
    { action: "dismiss", title: "Dismiss" },
];
```

## 🔔 Notification Permissions

### **Permission States:**

-   **`granted`** - User allowed notifications
-   **`denied`** - User blocked notifications
-   **`default`** - User hasn't decided yet

### **Handle Permission Denied:**

If user denies permission, they must manually enable it in browser settings:

**Chrome:**

1. Click lock icon in address bar
2. Click "Site settings"
3. Change "Notifications" to "Allow"

**Firefox:**

1. Click lock icon in address bar
2. Click "More Information"
3. Go to "Permissions" tab
4. Check "Allow" for Notifications

## 🐛 Troubleshooting

### **Notifications Not Working**

1. **Check subscription:**

    ```javascript
    pushManager.isSubscribed; // should be true
    ```

2. **Check permission:**

    ```javascript
    Notification.permission; // should be "granted"
    ```

3. **Check service worker:**

    - DevTools → Application → Service Workers
    - Should show "activated and running"

4. **Check VAPID keys:**
    ```bash
    php artisan config:clear
    # Verify keys are in .env
    ```

### **Subscription Fails**

-   Ensure HTTPS in production (or localhost for dev)
-   Check browser console for errors
-   Verify VAPID public key is correct

### **Notifications Not Sending**

-   Check logs: `storage/logs/laravel.log`
-   Verify subscriptions exist in database
-   Test with `/api/push/test` endpoint

## 📊 Database Schema

**push_subscriptions table:**

| Column           | Type      | Description                    |
| ---------------- | --------- | ------------------------------ |
| id               | bigint    | Primary key                    |
| user_id          | bigint    | Foreign key to users           |
| endpoint         | string    | Push service endpoint (unique) |
| public_key       | string    | P256DH key                     |
| auth_token       | string    | Auth secret                    |
| content_encoding | string    | Encoding type (aes128gcm)      |
| created_at       | timestamp | Subscription created           |
| updated_at       | timestamp | Last updated                   |

## 🔒 Security Notes

1. **VAPID Private Key**

    - Keep secret - never expose to frontend
    - Rotate periodically for security
    - Store securely in `.env`

2. **Subscription Validation**

    - Only authenticated users can subscribe
    - Subscriptions tied to user_id
    - Invalid subscriptions auto-removed

3. **HTTPS Required**
    - Production must use HTTPS
    - Service workers require secure context
    - Localhost exempt for development

## 🚀 Production Deployment

### **1. Verify VAPID Keys**

```bash
# Check keys are in .env
grep VAPID .env
```

### **2. Clear Caches**

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### **3. Test Thoroughly**

-   Test on different browsers
-   Test on mobile devices
-   Test background notifications
-   Test notification actions

### **4. Monitor Subscriptions**

```sql
-- Count active subscriptions
SELECT COUNT(*) FROM push_subscriptions;

-- Subscriptions per user
SELECT user_id, COUNT(*) as devices
FROM push_subscriptions
GROUP BY user_id;
```

## 📈 Future Enhancements

Potential improvements:

-   [ ] Notification preferences per user
-   [ ] Batch notifications to reduce API calls
-   [ ] Notification history/archive
-   [ ] Custom notification sounds
-   [ ] Scheduled notifications
-   [ ] Notification analytics
-   [ ] Multiple notification categories
-   [ ] Rich media (images, videos)

## 📚 Resources

-   [Web Push Protocol](https://developers.google.com/web/fundamentals/push-notifications)
-   [Notification API](https://developer.mozilla.org/en-US/docs/Web/API/Notifications_API)
-   [VAPID Specification](https://datatracker.ietf.org/doc/html/rfc8292)
-   [minishlink/web-push](https://github.com/web-push-libs/web-push-php)

---

**✅ Push notifications are fully functional!** Admins will now receive instant alerts for new applications.
