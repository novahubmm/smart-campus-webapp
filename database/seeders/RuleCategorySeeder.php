<?php

namespace Database\Seeders;

use App\Models\RuleCategory;
use Illuminate\Database\Seeder;

class RuleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'title' => 'Academic Rules',
                'description' => 'Rules related to academic performance and conduct',
                'icon' => '📚',
                'icon_color' => '#0891B2',
                'icon_bg_color' => '#CFFAFE',
            ],
            [
                'title' => 'Conduct & Discipline',
                'description' => 'Rules related to student behavior and discipline',
                'icon' => '⚖️',
                'icon_color' => '#DC2626',
                'icon_bg_color' => '#FEE2E2',
            ],
            [
                'title' => 'Safety & Security',
                'description' => 'Rules related to campus safety and security',
                'icon' => '🛡️',
                'icon_color' => '#F59E0B',
                'icon_bg_color' => '#FEF3C7',
            ],
            [
                'title' => 'Facility Usage',
                'description' => 'Rules related to using school facilities',
                'icon' => '🏫',
                'icon_color' => '#16A34A',
                'icon_bg_color' => '#DCFCE7',
            ],
        ];

        foreach ($categories as $category) {
            RuleCategory::updateOrCreate(
                ['title' => $category['title']],
                $category
            );
        }

        $this->command->info('Rule categories seeded successfully!');
    }
}
