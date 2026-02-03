<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\AnnouncementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $targetRoles = [
            ['staff'],
            ['teacher'],
            ['staff', 'teacher'],
        ];

        return [
            'id' => $this->faker->uuid(),
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(3, true),
            'priority' => $this->faker->randomElement($priorities),
            'target_roles' => $this->faker->randomElement($targetRoles),
            'location' => $this->faker->optional(0.7)->address(),
            'is_published' => true,
            'status' => 'active',
            'publish_date' => now(),
            'created_by' => function () {
                // Get a random admin user or create one
                $adminUser = User::role('admin')->inRandomOrder()->first();
                if ($adminUser) {
                    return $adminUser->id;
                }
                
                // Create a new admin user if none exists
                $newAdmin = User::factory()->create([
                    'name' => 'Test Admin',
                    'email' => 'admin@smartcampus.test',
                ]);
                $newAdmin->assignRole('admin');
                return $newAdmin->id;
            },
            'announcement_type_id' => function () {
                // Get a random announcement type or create a default one
                return AnnouncementType::inRandomOrder()->first()?->id 
                    ?? AnnouncementType::create([
                        'id' => \Illuminate\Support\Str::uuid(),
                        'name' => 'General',
                        'color' => '#3B82F6',
                        'icon' => '<i class="fas fa-bullhorn"></i>',
                        'is_active' => true,
                    ])->id;
            },
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create announcement for staff only
     */
    public function forStaff(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Staff Announcement: ' . $this->faker->sentence(4),
            'target_roles' => ['staff'],
            'content' => 'This is a test announcement specifically for staff members. ' . $this->faker->paragraphs(2, true),
        ]);
    }

    /**
     * Create announcement for teachers only
     */
    public function forTeachers(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Teacher Announcement: ' . $this->faker->sentence(4),
            'target_roles' => ['teacher'],
            'content' => 'This is a test announcement specifically for teachers. ' . $this->faker->paragraphs(2, true),
        ]);
    }

    /**
     * Create announcement for both staff and teachers
     */
    public function forAll(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'All Staff Announcement: ' . $this->faker->sentence(4),
            'target_roles' => ['staff', 'teacher'],
            'content' => 'This is a test announcement for all staff and teachers. ' . $this->faker->paragraphs(2, true),
        ]);
    }

    /**
     * Create urgent announcement
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'URGENT: ' . $this->faker->sentence(4),
            'priority' => 'urgent',
            'content' => 'This is an urgent announcement that requires immediate attention. ' . $this->faker->paragraphs(1, true),
        ]);
    }

    /**
     * Create high priority announcement
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Important: ' . $this->faker->sentence(4),
            'priority' => 'high',
            'content' => 'This is a high priority announcement. ' . $this->faker->paragraphs(2, true),
        ]);
    }
}