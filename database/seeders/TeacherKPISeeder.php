<?php

namespace Database\Seeders;

use App\Models\FreePeriodActivityType;
use Illuminate\Database\Seeder;

class TeacherKPISeeder extends Seeder
{
    public function run(): void
    {
        // Only seed activity types, not sample activities
        $this->call(FreePeriodActivityTypeSeeder::class);
        
        $this->command->info('✅ Teacher KPI seeder completed - activity types are ready');
        $this->command->info('💡 Teachers can now log their free period activities via the API');
    }
}
