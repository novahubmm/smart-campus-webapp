# Automatic Absent Marking for Attendance (Students, Teachers, Staff)

## Overview
Implemented automatic marking as absent for attendance records where no attendance was recorded. The system only processes the current month and runs daily for all three user types: Students, Teachers, and Staff.

## How It Works

### Common Logic (All Types)
1. Runs daily (staggered times to avoid conflicts)
2. Processes dates from the start of the current month up to yesterday
3. Skips today (attendance might still be taken)
4. Skips weekends (Saturday and Sunday)
5. For each school day:
   - Gets all active users of that type
   - Checks if attendance record exists
   - Creates 'absent' record if no attendance was recorded

### Student Attendance (1:00 AM)
- Processes by period (multiple records per day)
- Checks each student-period-date combination
- Only processes non-break periods
- Matches periods by day_of_week

### Teacher Attendance (1:10 AM)
- One record per teacher per day
- Uses teacher_id from user relationship
- Generates unique attendance ID
- Sets working_hours_decimal to 0

### Staff Attendance (1:20 AM)
- One record per staff member per day
- Uses staff_id from staff profile
- Simpler structure (no periods or check-in/out times)

## Attendance Record Details

### Student Attendance
When auto-marking absent:
- `status`: 'absent'
- `remark`: 'Auto-marked absent (no attendance recorded)'
- `marked_by`: null (indicates system-generated)
- `collect_time`: null
- `period_number`: From the period
- `period_id`: The period being marked

### Teacher Attendance
When auto-marking absent:
- `id`: Generated using `TeacherAttendance::generateId($date)`
- `status`: 'absent'
- `remarks`: 'Auto-marked absent (no attendance recorded)'
- `day_of_week`: Day name (Monday, Tuesday, etc.)
- `check_in_time`: null
- `check_out_time`: null
- `check_in_timestamp`: null
- `check_out_timestamp`: null
- `working_hours_decimal`: 0
- `leave_type`: null

### Staff Attendance
When auto-marking absent:
- `status`: 'absent'
- `remark`: 'Auto-marked absent (no attendance recorded)'
- `marked_by`: null (indicates system-generated)
- `start_time`: null
- `end_time`: null

## Files Created

### Jobs
1. `app/Jobs/MarkAbsentStudentsJob.php`
   - Processes student attendance by period
   - Handles period-based attendance logic
   - Logs detailed information about processing

2. `app/Jobs/MarkAbsentTeachersJob.php`
   - Processes teacher attendance (one per day)
   - Generates unique attendance IDs
   - Handles teacher-specific fields

3. `app/Jobs/MarkAbsentStaffJob.php`
   - Processes staff attendance (one per day)
   - Simpler structure than students/teachers
   - Handles staff-specific fields

### Console Commands
1. `app/Console/Commands/MarkAbsentStudentsCommand.php`
   - Command: `php artisan attendance:mark-absent`
   - Manual trigger for student attendance

2. `app/Console/Commands/MarkAbsentTeachersCommand.php`
   - Command: `php artisan attendance:mark-absent-teachers`
   - Manual trigger for teacher attendance

3. `app/Console/Commands/MarkAbsentStaffCommand.php`
   - Command: `php artisan attendance:mark-absent-staff`
   - Manual trigger for staff attendance

### Scheduled Tasks
Updated `routes/console.php`:
```php
// Mark absent students for missing attendance records daily at 1:00 AM
Schedule::job(new \App\Jobs\MarkAbsentStudentsJob())
    ->dailyAt('01:00')
    ->withoutOverlapping();

// Mark absent teachers for missing attendance records daily at 1:10 AM
Schedule::job(new \App\Jobs\MarkAbsentTeachersJob())
    ->dailyAt('01:10')
    ->withoutOverlapping();

// Mark absent staff for missing attendance records daily at 1:20 AM
Schedule::job(new \App\Jobs\MarkAbsentStaffJob())
    ->dailyAt('01:20')
    ->withoutOverlapping();
```

## Usage

### Manual Execution (Testing)
```bash
cd smart-campus-webapp

# Mark absent students
php artisan attendance:mark-absent

# Mark absent teachers
php artisan attendance:mark-absent-teachers

# Mark absent staff
php artisan attendance:mark-absent-staff
```

### Automatic Execution
The jobs run automatically at staggered times every day when Laravel's scheduler is running:
```bash
# Make sure this is in your crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Processing Rules

### Date Range
- **Start**: First day of current month
- **End**: Yesterday (not today)
- **Reason**: Attendance for today might still be taken

### Days Processed
- Monday through Friday only
- Skips Saturday (6) and Sunday (0)

### Users Included

**Students:**
- `student_profiles.status = 'active'`
- `users.is_active = true`

**Teachers:**
- `teacher_profiles.status = 'active'`
- `users.is_active = true`

**Staff:**
- `staff_profiles.status = 'active'`
- `users.is_active = true`

### Periods Included (Students Only)
- All periods where `is_break = false`
- Matched by `day_of_week` to the date being processed

### Duplicate Prevention
- **Students**: Checks for existing record with `(student_id, date, period_id)`
- **Teachers**: Checks for existing record with `(teacher_id, date)`
- **Staff**: Checks for existing record with `(staff_id, date)`
- All use unique constraints to prevent duplicates
- Skips if record already exists (any status)

## Logging

All jobs log comprehensive information:

### Success Logs

**Students:**
```
MarkAbsentStudentsJob completed
- marked_absent_count: Number of new absent records created
- processed_dates: Array of dates processed
- active_students_count: Number of active students
- periods_count: Number of periods found
```

**Teachers:**
```
MarkAbsentTeachersJob completed
- marked_absent_count: Number of new absent records created
- processed_dates: Array of dates processed
- active_teachers_count: Number of active teachers
```

**Staff:**
```
MarkAbsentStaffJob completed
- marked_absent_count: Number of new absent records created
- processed_dates: Array of dates processed
- active_staff_count: Number of active staff
```

### Error Logs
Individual record creation failures are logged with:
- User ID (student_id, teacher_id, or staff_id)
- Date
- Period ID (students only)
- Error message

### Info Logs
- When no active users found
- When no periods found (students only)
- When yesterday is before current month

## Example Scenarios

### Scenario 1: Mid-Month Run
- Today: February 15, 2026
- Processes: February 1-14, 2026
- Skips: February 15 (today) and future dates

### Scenario 2: First Day of Month
- Today: March 1, 2026
- Processes: February 1-28, 2026 (if yesterday was last day of Feb)
- Or: Nothing if it's the first day and yesterday was in previous month

### Scenario 3: Weekend Skip
- Processes: Mon Feb 10, Tue Feb 11, Wed Feb 12, Thu Feb 13, Fri Feb 14
- Skips: Sat Feb 8, Sun Feb 9, Sat Feb 15, Sun Feb 16

## Database Impact

### Performance Considerations
- Batch processing by date
- Individual record creation (not bulk insert) for error handling
- Uses database unique constraints to prevent duplicates
- Processes only current month (limited scope)
- Jobs run at staggered times (1:00, 1:10, 1:20) to avoid conflicts

### Expected Volume
For a school with:
- 500 students, 8 periods/day, 20 school days/month, 10% missing = ~8,000 records/month
- 50 teachers, 20 school days/month, 10% missing = ~100 records/month
- 30 staff, 20 school days/month, 10% missing = ~60 records/month

**Total**: ~8,160 absent records per month

## Important Notes

1. **Current Month Only**: Only processes the current month, not historical data
2. **Yesterday Cutoff**: Does not mark today as absent (attendance might still be taken)
3. **Weekend Skip**: Automatically skips Saturday and Sunday
4. **System Generated**: Records have `marked_by = null` (or no marked_by field) to indicate auto-generation
5. **Idempotent**: Safe to run multiple times (checks for existing records)
6. **Error Resilient**: Individual failures don't stop the entire process
7. **Active Users Only**: Only processes users with active status
8. **Staggered Execution**: Jobs run at different times to avoid database conflicts
9. **Period-Based (Students)**: Students have multiple records per day (one per period)
10. **Day-Based (Teachers/Staff)**: Teachers and staff have one record per day

## Comparison Table

| Feature | Students | Teachers | Staff |
|---------|----------|----------|-------|
| Records per day | Multiple (per period) | One | One |
| Run time | 1:00 AM | 1:10 AM | 1:20 AM |
| Unique constraint | student_id, date, period_id | teacher_id, date | staff_id, date |
| ID generation | UUID | Custom (att_YYYYMMDD_###) | UUID |
| Period-based | Yes | No | No |
| Check-in/out | No | Yes (null) | Yes (null) |
| Working hours | No | Yes (0) | No |

## Monitoring

Check logs for all jobs:
```bash
# View recent job executions
tail -f storage/logs/laravel.log | grep "MarkAbsent"

# Check for student attendance
grep "MarkAbsentStudentsJob" storage/logs/laravel.log

# Check for teacher attendance
grep "MarkAbsentTeachersJob" storage/logs/laravel.log

# Check for staff attendance
grep "MarkAbsentStaffJob" storage/logs/laravel.log

# Check for errors
grep "MarkAbsent.*Failed" storage/logs/laravel.log
```

## Troubleshooting

### No Records Created
- Check if there are active users of that type
- Verify dates are in current month and before today
- Check if attendance records already exist
- For students: Verify periods exist with correct day_of_week

### Too Many Records Created
- For students: Verify period configuration (is_break flag)
- Check user active status
- Review day_of_week mapping

### Duplicate Key Errors
- Should not occur due to existence check
- If occurs, check unique constraints:
  - Students: `(student_id, date, period_id)`
  - Teachers: `(teacher_id, date)`
  - Staff: `(staff_id, date)`

### Job Timing Conflicts
- Jobs are staggered (1:00, 1:10, 1:20) to avoid conflicts
- If conflicts occur, adjust timing in `routes/console.php`
