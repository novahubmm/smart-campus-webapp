<?php

namespace Database\Seeders;

use App\Models\KeyContact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KeyContactSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $settingId = '00000000-0000-0000-0000-000000000001';

        $contacts = [
            ['name' => 'Dr. Olivia Bennett', 'role' => 'Principal', 'email' => 'principal@smartcampus.test', 'phone' => '+95 9 000 000 010', 'is_primary' => true],
            ['name' => 'James Carter', 'role' => 'Vice Principal', 'email' => 'viceprincipal@smartcampus.test', 'phone' => '+95 9 000 000 011'],
            ['name' => 'Sarah Lee', 'role' => 'Finance Manager', 'email' => 'finance@smartcampus.test', 'phone' => '+95 9 000 000 012'],
        ];

        foreach ($contacts as $contact) {
            KeyContact::firstOrCreate(
                ['setting_id' => $settingId, 'email' => $contact['email']],
                $contact + ['id' => (string) Str::uuid(), 'setting_id' => $settingId]
            );
        }
    }
}
