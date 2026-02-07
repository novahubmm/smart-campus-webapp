<?php

namespace Tests\Feature;

use App\Models\ActivityType;
use App\Models\FreePeriodActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FreePeriodActivityTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected ActivityType $activityType1;
    protected ActivityType $activityType2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a teacher user
        $this->teacher = User::factory()->create([
            'email' => 'teacher@test.com',
            'role' => 'teacher',
        ]);

        // Create activity types
        $this->activityType1 = ActivityType::create([
            'label' => 'Lesson Planning',
            'color' => '#4F46E5',
            'icon_svg' => '<svg></svg>',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->activityType2 = ActivityType::create([
            'label' => 'Grading Papers',
            'color' => '#EF4444',
            'icon_svg' => '<svg></svg>',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function teacher_can_get_activity_types()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->getJson('/api/v1/teacher/free-period/activity-types');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'activity_types' => [
                        '*' => ['id', 'label', 'color', 'icon_svg'],
                    ],
                ],
            ]);
    }

    /** @test */
    public function teacher_can_record_single_activity()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'activities' => [
                [
                    'activity_type' => $this->activityType1->id,
                    'notes' => 'Prepared lesson plans for Grade 10 Mathematics',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Activity recorded successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'teacher_id',
                    'date',
                    'start_time',
                    'end_time',
                    'duration_minutes',
                    'activities',
                ],
            ]);

        $this->assertDatabaseHas('free_period_activities', [
            'teacher_id' => $this->teacher->id,
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function teacher_can_record_multiple_activities()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'activities' => [
                [
                    'activity_type' => $this->activityType1->id,
                    'notes' => 'Lesson planning',
                ],
                [
                    'activity_type' => $this->activityType2->id,
                    'notes' => 'Grading papers',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseCount('free_period_activity_items', 2);
    }

    /** @test */
    public function cannot_record_activity_for_future_date()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'activities' => [
                [
                    'activity_type' => $this->activityType1->id,
                    'notes' => 'Test',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function cannot_record_activity_for_weekend()
    {
        Sanctum::actingAs($this->teacher);

        // Find next Saturday
        $saturday = now()->next(Carbon::SATURDAY);

        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => $saturday->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'activities' => [
                [
                    'activity_type' => $this->activityType1->id,
                    'notes' => 'Test',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function cannot_record_activity_with_invalid_duration()
    {
        Sanctum::actingAs($this->teacher);

        // Duration less than 15 minutes
        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '10:35', // Only 5 minutes
            'activities' => [
                [
                    'activity_type' => $this->activityType1->id,
                    'notes' => 'Test',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    /** @test */
    public function cannot_record_activity_with_time_overlap()
    {
        Sanctum::actingAs($this->teacher);

        // Create first activity
        $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'activities' => [
                [
                    'activity_type' => $this->activityType1->id,
                    'notes' => 'First activity',
                ],
            ],
        ]);

        // Try to create overlapping activity
        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'activities' => [
                [
                    'activity_type' => $this->activityType2->id,
                    'notes' => 'Overlapping activity',
                ],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Time slot already has a recorded activity',
                'error' => [
                    'code' => 'TIME_OVERLAP',
                ],
            ]);
    }

    /** @test */
    public function teacher_can_get_activity_history()
    {
        Sanctum::actingAs($this->teacher);

        // Create some activities
        $activity = FreePeriodActivity::create([
            'id' => FreePeriodActivity::generateId(now()->format('Y-m-d')),
            'teacher_id' => $this->teacher->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'duration_minutes' => 60,
        ]);

        $activity->activityItems()->create([
            'activity_type_id' => $this->activityType1->id,
            'notes' => 'Test activity',
        ]);

        $response = $this->getJson('/api/v1/teacher/free-period/activities');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'activities',
                    'stats' => [
                        'total_records',
                        'total_hours',
                        'most_common_activity',
                        'activity_breakdown',
                    ],
                ],
            ]);
    }

    /** @test */
    public function teacher_can_filter_activities_by_date_range()
    {
        Sanctum::actingAs($this->teacher);

        $startDate = now()->subDays(7)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->getJson("/api/v1/teacher/free-period/activities?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function validates_maximum_activities_per_record()
    {
        Sanctum::actingAs($this->teacher);

        $activities = [];
        for ($i = 0; $i < 6; $i++) { // More than 5
            $activities[] = [
                'activity_type' => $this->activityType1->id,
                'notes' => "Activity $i",
            ];
        }

        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'activities' => $activities,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['activities']);
    }

    /** @test */
    public function validates_activity_type_exists()
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/teacher/free-period/activities', [
            'date' => now()->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
            'activities' => [
                [
                    'activity_type' => 999, // Non-existent
                    'notes' => 'Test',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['activities.0.activity_type']);
    }
}
