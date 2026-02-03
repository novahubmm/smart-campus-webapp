# Scheduled Announcements Setup Guide

This guide explains how to set up automatic publishing of scheduled announcements, especially for shared hosting environments.

## How Scheduled Announcements Work

1. **Create Announcement**: Admin creates an announcement with a future `publish_date`
2. **Save as Draft**: The announcement is saved with `is_published = false`
3. **Automatic Publishing**: When the `publish_date` is reached, the system automatically:
   - Sets `is_published = true`
   - Sends notifications to target roles (teachers, staff, etc.)

## Setup Options

### Option 1: Server with Cron Support (VPS/Dedicated)

If your server supports cron jobs, add this to your crontab:

```bash
# Edit crontab
crontab -e

# Add this line to run Laravel scheduler every minute
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

The system will automatically check for scheduled announcements every minute.

### Option 2: Shared Hosting (No Cron Access)

For shared hosting providers that don't allow cron jobs, use external cron services:

#### Step 1: Set Security Token

1. Edit your `.env` file:
```env
SCHEDULED_ANNOUNCEMENT_TOKEN=your-very-secure-random-token-here
```

2. Generate a secure token:
```bash
php artisan tinker
echo Str::random(32);
```

#### Step 2: Use External Cron Service

Use services like:
- **cron-job.org** (Free)
- **EasyCron** (Free tier available)
- **Cronhub** (Free tier available)

Set up a cron job to call:
```
POST https://your-domain.com/api/scheduled-announcements/publish
Header: X-Trigger-Token: your-secure-token-here
```

Or using GET with token in URL:
```
GET https://your-domain.com/api/scheduled-announcements/publish?token=your-secure-token-here
```

**Recommended frequency**: Every 5-15 minutes

#### Step 3: Test the Setup

Check status:
```bash
curl -H "X-Trigger-Token: your-secure-token-here" \
     https://your-domain.com/api/scheduled-announcements/status
```

Trigger manually:
```bash
curl -X POST -H "X-Trigger-Token: your-secure-token-here" \
     https://your-domain.com/api/scheduled-announcements/publish
```

### Option 3: Manual Command (Development/Testing)

Run manually via command line:

```bash
# Check what would be published (dry run)
php artisan announcements:publish-scheduled --dry-run

# Actually publish scheduled announcements
php artisan announcements:publish-scheduled
```

## Security Considerations

1. **Change the default token** in production
2. **Use HTTPS** for external cron calls
3. **Monitor logs** for unauthorized access attempts
4. **Rotate tokens** periodically

## Monitoring

### Check Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log | grep "scheduled announcement"
```

### API Status Endpoint

The status endpoint shows:
- Announcements due for publishing
- Upcoming scheduled announcements
- Days overdue/until publication

```bash
curl -H "X-Trigger-Token: your-token" \
     https://your-domain.com/api/scheduled-announcements/status
```

## Troubleshooting

### Common Issues

1. **Announcements not publishing**
   - Check if cron job is running
   - Verify token is correct
   - Check Laravel logs for errors

2. **Notifications not sent**
   - Verify target roles are set
   - Check if users exist with those roles
   - Review notification logs

3. **External cron not working**
   - Test the endpoint manually
   - Check if your hosting provider blocks external requests
   - Verify SSL certificate is valid

### Debug Commands

```bash
# List scheduled announcements
php artisan tinker
App\Models\Announcement::where('is_published', false)->whereNotNull('publish_date')->get(['title', 'publish_date']);

# Check user roles
App\Models\User::role('teacher')->count();
App\Models\User::role('staff')->count();
```

## Best Practices

1. **Test in staging** before deploying to production
2. **Set reasonable frequencies** (every 5-15 minutes is usually sufficient)
3. **Monitor system resources** if using frequent checks
4. **Have a backup plan** for critical announcements
5. **Document your setup** for team members

## Example Workflow

1. Admin creates announcement for "Staff Meeting Tomorrow"
2. Sets publish_date to tomorrow at 8:00 AM
3. Announcement is saved as draft
4. External cron service calls the API every 10 minutes
5. At 8:00 AM, the system:
   - Publishes the announcement
   - Sends notifications to all teachers and staff
   - Logs the action

This ensures announcements are delivered on time, even on shared hosting platforms.