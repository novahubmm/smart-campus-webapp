<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubjectType;

class SubjectTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Core'],
            ['name' => 'Elective'],
        ];

        foreach ($types as $type) {
            SubjectType::create($type);
        }

        $this->command->info('Subject Types created successfully!');
    }
}
