<?php

use App\Models\Batch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Timetable;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(\Database\Seeders\GradeCategorySeeder::class);
});

/**
 * Feature: timetable-versioning, Property 4: Edit Protection for Active Versions
 * For any timetable where is_active = true, edit operations SHALL be rejected.
 * Validates: Requirements 5.1, 5.2
 */
describe('Property 4: Edit Protection for Active Versions', function () {
    it('prevents editing active timetables', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create an active timetable
        $timetable = Timetable::factory()->active()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'created_by' => $admin->id,
        ]);

        // Attempt to access edit page
        $response = $this->actingAs($admin)->get(route('time-table.edit', $timetable));
        
        // Should redirect with error
        $response->assertRedirect(route('time-table.index'));
        $response->assertSessionHas('error');
    });

    it('allows editing inactive timetables', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create an inactive timetable
        $timetable = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'created_by' => $admin->id,
        ]);

        // Attempt to access edit page
        $response = $this->actingAs($admin)->get(route('time-table.edit', $timetable));
        
        // Should show edit page
        $response->assertStatus(200);
    });

    it('canEdit accessor returns false for active timetables', function () {
        $timetable = new Timetable(['is_active' => true]);
        expect($timetable->can_edit)->toBeFalse();
    });

    it('canEdit accessor returns true for inactive timetables', function () {
        $timetable = new Timetable(['is_active' => false]);
        expect($timetable->can_edit)->toBeTrue();
    });
});

/**
 * Feature: timetable-versioning, Property 5: Delete Protection for Active Versions
 * For any timetable where is_active = true, delete operations SHALL be rejected.
 * Validates: Requirements 6.1, 6.2
 */
describe('Property 5: Delete Protection for Active Versions', function () {
    it('prevents deleting active timetables', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create an active timetable
        $timetable = Timetable::factory()->active()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'created_by' => $admin->id,
        ]);

        // Attempt to delete
        $response = $this->actingAs($admin)->delete(route('time-table.destroy', $timetable));
        
        // Should redirect with error
        $response->assertSessionHas('error');
        
        // Timetable should still exist
        $this->assertDatabaseHas('timetables', ['id' => $timetable->id]);
    });

    it('allows deleting inactive timetables', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create an inactive timetable
        $timetable = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'created_by' => $admin->id,
        ]);

        // Attempt to delete
        $response = $this->actingAs($admin)->delete(route('time-table.destroy', $timetable));
        
        // Should succeed
        $response->assertSessionHas('status');
        
        // Timetable should be soft deleted
        $this->assertSoftDeleted('timetables', ['id' => $timetable->id]);
    });

    it('canDelete accessor returns false for active timetables', function () {
        $timetable = new Timetable(['is_active' => true]);
        expect($timetable->can_delete)->toBeFalse();
    });

    it('canDelete accessor returns true for inactive timetables', function () {
        $timetable = new Timetable(['is_active' => false]);
        expect($timetable->can_delete)->toBeTrue();
    });
});


/**
 * Feature: timetable-versioning, Property 8: API Returns Only Active Timetables
 * For any API request for timetable data, the response SHALL contain only timetables where is_active = true.
 * Validates: Requirements 8.1
 */
describe('Property 8: API Returns Only Active Timetables', function () {
    it('teacher dashboard only shows classes from active timetables', function () {
        // Create teacher user with profile
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        
        $teacherProfile = \App\Models\TeacherProfile::factory()->create([
            'user_id' => $teacher->id,
        ]);

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create an active timetable with periods
        $activeTimetable = Timetable::factory()->active()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
        ]);

        // Create an inactive timetable with periods
        $inactiveTimetable = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 2,
        ]);

        // Create periods for both timetables
        \App\Models\Period::factory()->create([
            'timetable_id' => $activeTimetable->id,
            'teacher_profile_id' => $teacherProfile->id,
            'day_of_week' => strtolower(now()->format('l')),
        ]);

        \App\Models\Period::factory()->create([
            'timetable_id' => $inactiveTimetable->id,
            'teacher_profile_id' => $teacherProfile->id,
            'day_of_week' => strtolower(now()->format('l')),
        ]);

        // Call the API
        $response = $this->actingAs($teacher)
            ->getJson('/api/v1/teacher/today-classes');

        $response->assertStatus(200);
        
        // Should only return classes from active timetable
        $data = $response->json('data.classes');
        
        // All returned items should have timetable_version matching active timetable
        foreach ($data as $item) {
            expect($item['timetable_version'])->toBe($activeTimetable->version);
        }
    });
});

/**
 * Feature: timetable-versioning, Property 9: API Includes Version Number
 * For any timetable returned by the API, the response SHALL include the version field.
 * Validates: Requirements 8.3
 */
describe('Property 9: API Includes Version Number', function () {
    it('API responses include timetable_version field', function () {
        // Create teacher user with profile
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        
        $teacherProfile = \App\Models\TeacherProfile::factory()->create([
            'user_id' => $teacher->id,
        ]);

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create an active timetable
        $timetable = Timetable::factory()->active()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 3,
        ]);

        // Create a period
        \App\Models\Period::factory()->create([
            'timetable_id' => $timetable->id,
            'teacher_profile_id' => $teacherProfile->id,
            'day_of_week' => strtolower(now()->format('l')),
        ]);

        // Call the API
        $response = $this->actingAs($teacher)
            ->getJson('/api/v1/teacher/today-classes');

        $response->assertStatus(200);
        
        $data = $response->json('data.classes');
        
        // Each item should have timetable_version field
        foreach ($data as $item) {
            expect($item)->toHaveKey('timetable_version');
            expect($item['timetable_version'])->toBe(3);
        }
    });
});


/**
 * Feature: timetable-versioning, Property 6: Version Numbers Preserved on Delete
 * For any set of timetables for a class, when one version is deleted, the remaining versions SHALL retain their original version numbers.
 * Validates: Requirements 6.4
 */
describe('Property 6: Version Numbers Preserved on Delete', function () {
    it('preserves version numbers when a timetable is deleted', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create three timetable versions
        $timetable1 = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 1,
        ]);

        $timetable2 = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 2,
        ]);

        $timetable3 = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 3,
        ]);

        // Delete version 2
        $this->actingAs($admin)->delete(route('time-table.destroy', $timetable2));

        // Verify version 2 is soft deleted
        $this->assertSoftDeleted('timetables', ['id' => $timetable2->id]);

        // Verify versions 1 and 3 still have their original version numbers
        $remainingTimetables = Timetable::where('class_id', $class->id)->get();
        
        expect($remainingTimetables)->toHaveCount(2);
        expect($remainingTimetables->pluck('version')->sort()->values()->toArray())->toBe([1, 3]);
    });
});


/**
 * Feature: timetable-versioning, Property 7: Duplicate Copies All Periods
 * For any timetable with N periods, when duplicated, the new timetable SHALL have exactly N periods with matching subject_id and teacher_profile_id values.
 * Validates: Requirements 7.1, 7.2
 */
describe('Property 7: Duplicate Copies All Periods', function () {
    it('duplicates all periods when copying a timetable', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create a timetable with periods
        $timetable = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 1,
        ]);

        // Create some periods with unique day/period combinations
        $periodData = [
            ['day_of_week' => 'monday', 'period_number' => 1],
            ['day_of_week' => 'monday', 'period_number' => 2],
            ['day_of_week' => 'tuesday', 'period_number' => 1],
            ['day_of_week' => 'tuesday', 'period_number' => 2],
            ['day_of_week' => 'wednesday', 'period_number' => 1],
        ];

        $periods = collect($periodData)->map(function ($data) use ($timetable) {
            return \App\Models\Period::factory()->create([
                'timetable_id' => $timetable->id,
                'day_of_week' => $data['day_of_week'],
                'period_number' => $data['period_number'],
            ]);
        });

        // Duplicate the timetable
        $response = $this->actingAs($admin)->post(route('time-table.duplicate', $timetable));

        $response->assertRedirect();

        // Find the new timetable
        $newTimetable = Timetable::where('class_id', $class->id)
            ->where('id', '!=', $timetable->id)
            ->first();

        expect($newTimetable)->not->toBeNull();
        expect($newTimetable->is_active)->toBeFalse();
        expect($newTimetable->version)->toBe(2);
        expect($newTimetable->periods)->toHaveCount(5);

        // Verify periods have matching subject_id and teacher_profile_id
        $originalPeriods = $timetable->periods->sortBy(['day_of_week', 'period_number']);
        $newPeriods = $newTimetable->periods->sortBy(['day_of_week', 'period_number']);

        foreach ($originalPeriods->values() as $index => $originalPeriod) {
            $newPeriod = $newPeriods->values()[$index];
            expect($newPeriod->subject_id)->toBe($originalPeriod->subject_id);
            expect($newPeriod->teacher_profile_id)->toBe($originalPeriod->teacher_profile_id);
            expect($newPeriod->day_of_week)->toBe($originalPeriod->day_of_week);
            expect($newPeriod->period_number)->toBe($originalPeriod->period_number);
        }
    });

    it('creates duplicated timetable as inactive', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create an active timetable
        $timetable = Timetable::factory()->active()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 1,
        ]);

        // Duplicate the timetable
        $this->actingAs($admin)->post(route('time-table.duplicate', $timetable));

        // Find the new timetable
        $newTimetable = Timetable::where('class_id', $class->id)
            ->where('id', '!=', $timetable->id)
            ->first();

        // New timetable should be inactive regardless of source
        expect($newTimetable->is_active)->toBeFalse();
    });

    it('assigns next available version number', function () {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create batch, grade, class hierarchy
        $batch = Batch::factory()->create();
        $grade = Grade::factory()->create(['batch_id' => $batch->id]);
        $class = SchoolClass::factory()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
        ]);

        // Create timetables with versions 1 and 3 (gap at 2)
        Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 1,
        ]);

        $timetable3 = Timetable::factory()->inactive()->create([
            'batch_id' => $batch->id,
            'grade_id' => $grade->id,
            'class_id' => $class->id,
            'version' => 3,
        ]);

        // Duplicate version 3
        $this->actingAs($admin)->post(route('time-table.duplicate', $timetable3));

        // New version should be 4 (max + 1), not 2 (filling gap)
        $newTimetable = Timetable::where('class_id', $class->id)
            ->orderByDesc('version')
            ->first();

        expect($newTimetable->version)->toBe(4);
    });
});
