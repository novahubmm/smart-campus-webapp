<?php

namespace Database\Seeders\Demo;

use App\Models\Department;
use Illuminate\Support\Str;

class DemoDepartmentSeeder extends DemoBaseSeeder
{
    public function run(): array
    {
        $this->command->info('Creating Departments (3)...');

        $departments = [
            ['code' => 'FIN', 'name' => 'Finance Department', 'is_active' => true],
            ['code' => 'MGT', 'name' => 'Management Department', 'is_active' => true],
            ['code' => 'TCH', 'name' => 'Teaching Department', 'is_active' => true],
        ];

        $created = [];
        foreach ($departments as $dept) {
            $existing = Department::where('code', $dept['code'])->first();
            $created[$dept['code']] = $existing ?: Department::create($dept + ['id' => (string) Str::uuid()]);
        }

        return $created;
    }
}
