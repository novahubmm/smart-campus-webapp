<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding payment methods...');

        $methods = [
            [
                'name' => 'KBZ Bank',
                'name_mm' => 'KBZ ဘဏ်',
                'type' => 'bank',
                'account_number' => '1234567890123456',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'logo_url' => "/images/payment-methods/kbz.png",
                'is_active' => true,
                'instructions' => 'Transfer to this account and upload the receipt.',
                'instructions_mm' => 'ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ။',
                'sort_order' => 1,
            ],
            [
                'name' => 'AYA Bank',
                'name_mm' => 'AYA ဘဏ်',
                'type' => 'bank',
                'account_number' => '0987654321098765',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'logo_url' => "/images/payment-methods/aya.png",
                'is_active' => true,
                'instructions' => 'Transfer to this account and upload the receipt.',
                'instructions_mm' => 'ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ။',
                'sort_order' => 2,
            ],
            [
                'name' => 'CB Bank',
                'name_mm' => 'CB ဘဏ်',
                'type' => 'bank',
                'account_number' => '5555666677778888',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'logo_url' => "/images/payment-methods/cb.png",
                'is_active' => true,
                'instructions' => 'Transfer to this account and upload the receipt.',
                'instructions_mm' => 'ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ။',
                'sort_order' => 3,
            ],
            [
                'name' => 'KBZ Pay',
                'name_mm' => 'KBZ Pay',
                'type' => 'mobile_wallet',
                'account_number' => '09123456789',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'logo_url' => "/images/payment-methods/kbzpay.png",
                'is_active' => true,
                'instructions' => 'Send payment to this number and upload the screenshot.',
                'instructions_mm' => 'ဒီနံပါတ်ကို ငွေပို့ပြီး screenshot upload လုပ်ပါ။',
                'sort_order' => 4,
            ],
            [
                'name' => 'Wave Money',
                'name_mm' => 'Wave Money',
                'type' => 'mobile_wallet',
                'account_number' => '09987654321',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'logo_url' => "/images/payment-methods/wavemoney.png",
                'is_active' => true,
                'instructions' => 'Send payment to this number and upload the screenshot.',
                'instructions_mm' => 'ဒီနံပါတ်ကို ငွေပို့ပြီး screenshot upload လုပ်ပါ။',
                'sort_order' => 5,
            ],
            [
                'name' => 'AYA Pay',
                'name_mm' => 'AYA Pay',
                'type' => 'mobile_wallet',
                'account_number' => '09111222333',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'logo_url' => "/images/payment-methods/ayapay.png",
                'is_active' => true,
                'instructions' => 'Send payment to this number and upload the screenshot.',
                'instructions_mm' => 'ဒီနံပါတ်ကို ငွေပို့ပြီး screenshot upload လုပ်ပါ။',
                'sort_order' => 6,
            ],
            [
                'name' => 'CB Pay',
                'name_mm' => 'CB Pay',
                'type' => 'mobile_wallet',
                'account_number' => '09444555666',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'logo_url' => "/images/payment-methods/cbpay.png",
                'is_active' => true,
                'instructions' => 'Send payment to this number and upload the screenshot.',
                'instructions_mm' => 'ဒီနံပါတ်ကို ငွေပို့ပြီး screenshot upload လုပ်ပါ။',
                'sort_order' => 7,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name']],
                $method
            );
            $this->command->info("✓ Created/Updated: {$method['name']}");
        }

        $this->command->newLine();
        $this->command->info('✓ Payment methods seeded successfully!');
        $this->command->info('Total: ' . count($methods) . ' payment methods');
    }
}
