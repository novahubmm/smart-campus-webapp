<?php

namespace Database\Seeders;

use App\Models\PaymentPromotion;
use Illuminate\Database\Seeder;

class PaymentPromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding payment promotions...');

        $promotions = [
            [
                'months' => 1,
                'discount_percent' => 0,
                'is_active' => true,
            ],
            [
                'months' => 2,
                'discount_percent' => 0,
                'is_active' => true,
            ],
            [
                'months' => 3,
                'discount_percent' => 5,
                'is_active' => true,
            ],
            [
                'months' => 6,
                'discount_percent' => 10,
                'is_active' => true,
            ],
            [
                'months' => 9,
                'discount_percent' => 15,
                'is_active' => true,
            ],
            [
                'months' => 12,
                'discount_percent' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($promotions as $promotion) {
            $promo = PaymentPromotion::updateOrCreate(
                ['months' => $promotion['months']],
                $promotion
            );
            $this->command->info("✓ Created/Updated: {$promo->months} months - {$promo->discount_percent}% discount");
        }

        $this->command->newLine();
        $this->command->info('✓ Payment promotions seeded successfully!');
        $this->command->info('Total: ' . count($promotions) . ' promotions');
        $this->command->newLine();
        $this->command->info('Discount structure:');
        $this->command->line('  • 3 months  = 5% discount');
        $this->command->line('  • 6 months  = 10% discount');
        $this->command->line('  • 9 months  = 15% discount');
        $this->command->line('  • 12 months = 20% discount');
    }
}
