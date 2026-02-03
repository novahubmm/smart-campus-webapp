<?php

namespace Database\Seeders;

use App\Models\DailyReportRecipient;
use Illuminate\Database\Seeder;

class DailyReportRecipientSeeder extends Seeder
{
    public function run(): void
    {
        $recipients = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'School Administrator',
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($recipients as $recipient) {
            DailyReportRecipient::updateOrCreate(
                ['slug' => $recipient['slug']],
                $recipient
            );
        }
    }
}
