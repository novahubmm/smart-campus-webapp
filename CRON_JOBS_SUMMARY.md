# Smart Campus Cron Jobs Summary

## Overview
This document provides a complete overview of all automated cron jobs implemented in the Smart Campus system.

## All Scheduled Jobs

### Daily Jobs (Midnight - 2:00 AM)

| Time | Job | Description | File |
|------|-----|-------------|------|
| 00:00 | Update Event Status | Auto-updates event statuses based on dates | `UpdateEventStatusJob.php` |
| 00:00 | Update Exam Status | Auto-updates exam statuses based on dates | `UpdateExamStatusJob.php` |
| 01:00 | Mark Absent Students | Marks students absent for missing attendance | `MarkAbsentStudentsJob.php` |
| 01:10 | Mark Absent Teachers | Marks teachers absent for missing attendance | `MarkAbsentTeachersJob.php` |
| 01:20 | Mark Absent Staff | Marks staff absent for missing attendance | `MarkAbsentStaffJob.php` |
| 02:00 | Mark Overdue Fees | Marks unpaid fees as overdue | `fees:mark-overdue` |

### Monthly Jobs

| Time | Job | Description | File |
|------|-----|-------------|------|
| 1st @ 00:00 | Generate Monthly Invoices | Creates monthly fee invoices | `MonthlyInvoiceGenerationJob.php` |
| 1st @ 01:00 | Generate Monthly Fees | Legacy monthly fee generation | `fees:generate-monthly` |

### Frequent Jobs

| Frequency | Job | Description | File |
|-----------|-----|-------------|------|
| Every Minute | Publish Scheduled Announcements | Publishes announcements at scheduled time | `announcements:publish-scheduled` |
| Daily @ 06:00 | Generate One-Time Invoices | Creates one-time fee invoices | `payment:generate-one-time-invoices` |

## Job Details

### 1. Event Status Updates
**Schedule**: Daily at midnight (00:00)
**Purpose**: Automatically update event statuses based on current date

**Status Logic**:
- `upcoming`: Today < Start Date
- `ongoing`: Start Date ≤ Today ≤ End Date
- `completed`: Today > End Date
- `result`: Manual only (never auto-updated)

**Manual Command**: `php artisan events:update-status`

### 2. Exam Status Updates
**Schedule**: Daily at midnight (00:00)
**Purpose**: Automatically update exam statuses based on current date

**Status Logic**:
- `upcoming`: Today < Start Date
- `ongoing`: Start Date ≤ Today ≤ End Date
- `completed`: Today > End Date

**Manual Command**: `php artisan exams:update-status`

### 3. Student Absent Marking
**Schedule**: Daily at 1:00 AM
**Purpose**: Mark students absent for periods with no attendance record

**Processing**:
- Current month only
- Up to yesterday (not today)
- Skips weekends
- Per period (multiple per day)
- Only non-break periods

**Manual Command**: `php artisan attendance:mark-absent`

### 4. Teacher Absent Marking
**Schedule**: Daily at 1:10 AM
**Purpose**: Mark teachers absent for dates with no attendance record

**Processing**:
- Current month only
- Up to yesterday (not today)
- Skips weekends
- One record per day
- Generates unique attendance ID

**Manual Command**: `php artisan attendance:mark-absent-teachers`

### 5. Staff Absent Marking
**Schedule**: Daily at 1:20 AM
**Purpose**: Mark staff absent for dates with no attendance record

**Processing**:
- Current month only
- Up to yesterday (not today)
- Skips weekends
- One record per day

**Manual Command**: `php artisan attendance:mark-absent-staff`

### 6. Monthly Invoice Generation
**Schedule**: 1st of every month at midnight (00:00)
**Purpose**: Generate monthly fee invoices for all active students

**Manual Command**: Not available (job only)

### 7. Mark Overdue Fees
**Schedule**: Daily at 2:00 AM
**Purpose**: Mark unpaid fees as overdue based on due date

**Manual Command**: `php artisan fees:mark-overdue`

### 8. Generate Monthly Fees
**Schedule**: 1st of every month at 1:00 AM
**Purpose**: Legacy monthly fee generation

**Manual Command**: `php artisan fees:generate-monthly`

### 9. Publish Scheduled Announcements
**Schedule**: Every minute
**Purpose**: Publish announcements that are scheduled for the current time

**Manual Command**: `php artisan announcements:publish-scheduled`

### 10. Generate One-Time Invoices
**Schedule**: Daily at 6:00 AM
**Purpose**: Generate invoices for one-time fees

**Manual Command**: `php artisan payment:generate-one-time-invoices`

## Configuration File

All jobs are configured in: `routes/console.php`

```php
// Event & Exam Status Updates (00:00)
Schedule::job(new \App\Jobs\UpdateEventStatusJob())->dailyAt('00:00')->withoutOverlapping();
Schedule::job(new \App\Jobs\UpdateExamStatusJob())->dailyAt('00:00')->withoutOverlapping();

// Attendance Absent Marking (01:00 - 01:20)
Schedule::job(new \App\Jobs\MarkAbsentStudentsJob())->dailyAt('01:00')->withoutOverlapping();
Schedule::job(new \App\Jobs\MarkAbsentTeachersJob())->dailyAt('01:10')->withoutOverlapping();
Schedule::job(new \App\Jobs\MarkAbsentStaffJob())->dailyAt('01:20')->withoutOverlapping();

// Fee Management
Schedule::command('fees:generate-monthly')->monthlyOn(1, '01:00')->withoutOverlapping()->runInBackground();
Schedule::command('fees:mark-overdue')->dailyAt('02:00')->withoutOverlapping()->runInBackground();
Schedule::job(new \App\Jobs\PaymentSystem\MonthlyInvoiceGenerationJob())->monthlyOn(1, '00:00')->withoutOverlapping();
Schedule::command('payment:generate-one-time-invoices')->dailyAt('06:00')->withoutOverlapping()->runInBackground();

// Announcements
Schedule::command('announcements:publish-scheduled')->everyMinute()->withoutOverlapping()->runInBackground();
```

## Setup & Monitoring

### Enable Scheduler
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Test All Jobs Manually
```bash
# Event & Exam Status
php artisan events:update-status
php artisan exams:update-status

# Attendance
php artisan attendance:mark-absent
php artisan attendance:mark-absent-teachers
php artisan attendance:mark-absent-staff

# Fees
php artisan fees:generate-monthly
php artisan fees:mark-overdue
php artisan payment:generate-one-time-invoices

# Announcements
php artisan announcements:publish-scheduled
```

### Monitor Logs
```bash
# View all scheduled job activity
tail -f storage/logs/laravel.log | grep -E "(UpdateEvent|UpdateExam|MarkAbsent|Invoice|Fee)"

# View specific job
tail -f storage/logs/laravel.log | grep "MarkAbsentStudentsJob"

# Check for errors
grep "Failed" storage/logs/laravel.log
```

### Verify Scheduler is Running
```bash
# Check if scheduler is configured
php artisan schedule:list

# Test scheduler (runs all due jobs)
php artisan schedule:run

# Run scheduler with verbose output
php artisan schedule:run -v
```

## Job Dependencies

### Database Tables Used
- `events` - Event status updates
- `exams` - Exam status updates
- `student_attendance` - Student absent marking
- `teacher_attendance` - Teacher absent marking
- `staff_attendance` - Staff absent marking
- `invoices` - Invoice generation
- `fees` - Fee management
- `announcements` - Announcement publishing

### Required Models
- Event, Exam
- StudentProfile, TeacherProfile, StaffProfile
- StudentAttendance, TeacherAttendance, StaffAttendance
- Period (for student attendance)
- Invoice, Fee
- Announcement

## Performance Considerations

### Peak Times
- **00:00-02:00**: Heavy processing (status updates, attendance marking, fees)
- **Every Minute**: Light processing (announcements)
- **1st of Month**: Additional heavy processing (invoice generation)

### Optimization Tips
1. Jobs are staggered to avoid conflicts
2. `withoutOverlapping()` prevents concurrent runs
3. `runInBackground()` for commands that can run async
4. Attendance jobs process current month only
5. Status updates use bulk updates where possible

### Expected Load
For a school with 500 students, 50 teachers, 30 staff:
- **Daily**: ~8,000 student attendance records, ~100 teacher records, ~60 staff records
- **Monthly**: ~500 invoices on 1st of month
- **Continuous**: Announcement checks every minute

## Troubleshooting

### Jobs Not Running
1. Check crontab is configured
2. Verify scheduler is enabled: `php artisan schedule:list`
3. Check Laravel logs for errors
4. Ensure queue workers are running (if using queues)

### Jobs Running Multiple Times
1. Check for duplicate crontab entries
2. Verify `withoutOverlapping()` is set
3. Check for multiple application instances

### Performance Issues
1. Review job timing (stagger if needed)
2. Check database indexes
3. Monitor server resources during peak times
4. Consider queue workers for heavy jobs

### Data Issues
1. Verify active status of users
2. Check date ranges being processed
3. Review unique constraints
4. Check for missing relationships (periods, profiles, etc.)

## Documentation References

- Event/Exam Status: `EVENT_EXAM_STATUS_IMPLEMENTATION.md`
- Attendance Marking: `ATTENDANCE_AUTO_ABSENT_IMPLEMENTATION.md`
- Payment System: `PAYMENT_API_SPECIFICATION.md`

## Maintenance

### Regular Checks
- Weekly: Review logs for errors
- Monthly: Verify invoice generation on 1st
- Quarterly: Review job timing and performance
- Annually: Audit all automated processes

### Updates
When modifying jobs:
1. Update `routes/console.php`
2. Test manually before deploying
3. Update documentation
4. Monitor logs after deployment
5. Verify with stakeholders
