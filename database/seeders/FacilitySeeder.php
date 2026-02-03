<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            'Projector',
            'Whiteboard',
            'Smart Board',
            'Computer',
            'Air Conditioner',
            'Fan',
            'Speakers',
            'Microphone',
            'Desk',
            'Chair',
            'Bookshelf',
            'TV Screen',
            'WiFi Access Point',
            'CCTV Camera',
            'Fire Extinguisher',
        ];

        foreach ($facilities as $facility) {
            Facility::create([
                'name' => $facility,
            ]);
        }
    }
}
