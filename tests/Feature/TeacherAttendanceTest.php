<?php

namespace Tests\Feature;

use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a teacher user
        $this->teacher = User::factory()->create([
            'email' => 'teacher@test.com',
            'role' => 'teacher',
        ]);
    }

    /** @test */
    public function teacher_can_check_in_successfully()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/teacher/attendance/check-in', [
            'latitude' => 16.8661,
            'longitude' => 96.1951,
            'device_info' => 'iPhone 13 Pro',
            'app_version' => '1.0.0',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Checked in successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'teacher_id',
                    'date',
                    'check_in_time',
                    'check_in_timestamp',
                    'status',
                    'location',
                ],
            ]);

        $this->assertDatabaseHas('teacher_attendance', [
            'teacher_id' => $this->teacher->id,
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function teacher_cannot_check_in_twice_on_same_day()
    {
        Sanctum::actingAs($this->teacher);

        // First check-in
        $this->postJson('/api/v1/teacher/attendance/check-in');

        // Second check-in attempt
        $response = $this->postJson('/api/v1/teacher/attendance/check-in');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Already checked in today',
                'error' => [
                    'code' => 'ALREADY_CHECKED_IN',
                ],
            ]);
    }

    /** @test */
    public function teacher_can_check_out_successfully()
    {
        Sanctum::actingAs($this->teacher);

        // First check-in
        $this->postJson('/api/v1/teacher/attendance/check-in');

        // Then check-out
        $response = $this->postJson('/api/v1/teacher/attendance/check-out', [
            'latitude' => 16.8661,
            'longitude' => 96.1951,
            'notes' => 'Completed all tasks for today',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Checked out successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'teacher_id',
                    'date',
                    'check_in_time',
                    'check_out_time',
                    'working_hours',
                    'working_hours_decimal',
                    'status',
                ],
            ]);
    }

    /** @test */
    public function teacher_cannot_check_out_without_checking_in()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/teacher/attendance/check-out');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot check out without checking in first',
                'error' => [
                    'code' => 'NOT_CHECKED_IN',
                ],
            ]);
    }

    /** @test */
    public function teacher_cannot_check_out_twice()
    {
        Sanctum::actingAs($this->teacher);

        // Check-in and check-out
        $this->postJson('/api/v1/teacher/attendance/check-in');
        $this->postJson('/api/v1/teacher/attendance/check-out');

        // Try to check-out again
        $response = $this->postJson('/api/v1/teacher/attendance/check-out');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Already checked out today',
                'error' => [
                    'code' => 'ALREADY_CHECKED_OUT',
                ],
            ]);
    }

    /** @test */
    public function teacher_can_get_today_status_when_not_checked_in()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->getJson('/api/v1/teacher/attendance/today');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_checked_in' => false,
                    'status' => 'not_checked_in',
                ],
            ]);
    }

    /** @test */
    public function teacher_can_get_today_status_when_checked_in()
    {
        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/teacher/attendance/check-in');

        $response = $this->getJson('/api/v1/teacher/attendance/today');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_checked_in' => true,
                    'status' => 'checked_in',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'date',
                    'check_in_time',
                    'elapsed_time',
                ],
            ]);
    }

    /** @test */
    public function teacher_can_get_attendance_history()
    {
        Sanctum::actingAs($this->teacher);

        // Create some attendance records
        TeacherAttendance::factory()->count(5)->create([
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->getJson('/api/v1/teacher/my-attendance');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'month',
                    'records',
                    'stats' => [
                        'total_days',
                        'present_days',
                        'absent_days',
                        'leave_days',
                        'attendance_percentage',
                    ],
                ],
            ]);
    }

    /** @test */
    public function teacher_can_filter_attendance_by_month()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->getJson('/api/v1/teacher/my-attendance?month=current');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function check_in_validates_location_coordinates()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/teacher/attendance/check-in', [
            'latitude' => 999, // Invalid
            'longitude' => 999, // Invalid
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }
}
