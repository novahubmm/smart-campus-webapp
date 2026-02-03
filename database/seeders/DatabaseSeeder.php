<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            // AdminUserSeeder::class,
            SettingSeeder::class,
            RuleCategorySeeder::class,
            SchoolRuleSeeder::class,
            // DepartmentSeeder::class,
            // KeyContactSeeder::class,
            FacilitySeeder::class,
            GradeCategorySeeder::class,
            SubjectTypeSeeder::class,
            // EventSeeder::class,
            ExamTypeSeeder::class,
            AnnouncementTypeSeeder::class,
            DemoReadySeeder::class,
            // ClassRecordsSeeder::class,
        ]);
    }
}
