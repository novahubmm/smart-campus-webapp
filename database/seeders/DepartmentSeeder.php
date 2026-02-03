<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $defaults = [
            ['code' => 'PRIMARY', 'name' => 'Primary Teachers'],
            ['code' => 'LANG', 'name' => 'Language Teachers'],
            ['code' => 'ICT', 'name' => 'ICT Staff'],
        ];

        foreach ($defaults as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                $dept + ['is_active' => true, 'id' => (string) \Illuminate\Support\Str::uuid()]
            );
        }
    }
}
